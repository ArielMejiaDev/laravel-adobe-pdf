# How It Works

Understanding the lifecycle helps you reason about queues, retries and failures.

## The lifecycle

Every operation follows the same path, which mirrors the Adobe PDF Services API:

```
 build (fluent)      dispatch            job runs on the queue
┌───────────────┐   ┌──────────┐   ┌──────────────────────────────────────────┐
│ create()      │   │ create   │   │ 1. upload inputs      → asset IDs         │
│  ->from(...)  │──▶│ process  │──▶│ 2. submit operation   → status location   │
│  ->dispatch() │   │ (pending)│   │ 3. poll (release)     → done / failed     │
└───────────────┘   └──────────┘   │ 4. download result    → store on disk     │
                                    └──────────────────────────────────────────┘
```

1. **Build** — a fluent builder (e.g. `CreatePdf`) collects your inputs and options.
2. **Dispatch** — a row is written to `adobe_pdf_processes` (status `pending`) and a
   `ProcessAdobePdfOperation` job is queued. Input files are *staged* onto the storage
   disk so any worker can read them.
3. **Run** — the job walks the steps below, reading its state back from the process
   record so each step is idempotent.

## Non-blocking polling

When Adobe reports the job is still `in progress`, the package does **not** block the
worker with `sleep()`. Instead the job **releases itself back onto the queue**
(`$this->release($backoff)`) and increments the poll counter on the process. The worker
immediately moves on to other jobs; the operation resumes on the next attempt.

This is what makes the package safe to run at scale — a thousand pending operations
don't tie up a thousand workers in sleep loops.

## Synchronous execution

`dispatchSync()` runs the exact same job inline. Because there's no queue to release
back onto, it polls in a **bounded in-process loop** (sleeping `polling.backoff`
seconds between polls) until the operation is `done`, `failed`, or it hits
`polling.max_attempts`.

```php
$process = LaravelAdobePdf::compress('report.pdf')->dispatchSync();
// $process is already Completed or Failed here
```

::: warning
Synchronous execution blocks the current process while it polls. Use it for small
documents, CLI commands and tests — prefer `dispatch()` for web requests.
:::

## What `dispatch()` returns

Both `dispatch()` and `dispatchSync()` return an
[`AdobePdfProcess`](/guide/processes) model:

- After `dispatch()` it is `pending` (the work hasn't run yet).
- After `dispatchSync()` it is the final `Completed` / `Failed` state.

## Failure semantics

| Situation | Async (`dispatch`) | Sync (`dispatchSync`) |
| --------- | ------------------ | --------------------- |
| Adobe returns `failed`, or polling times out | Process marked **failed**, job stops (no retry) | Process marked **failed**, returned |
| Rate limited (`429`) | Job **released** and retried later | Process marked **failed**, returned |
| Auth / network / other API error | Job **throws** → queue retries (bounded by `retryUntil()`); `failed()` records it once exhausted | Process marked **failed**, returned |

The important guarantee: **`dispatchSync()` never throws for an Adobe-side failure** —
inspect `$process->hasFailed()` and `$process->error` instead. See
[Error Handling](/guide/error-handling) for the details.
