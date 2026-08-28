# Tracking Processes

Every operation is recorded as an `AdobePdfProcess` model (table:
`adobe_pdf_processes`). This is what powers status checks, result retrieval and any
dashboard you might build on top.

## Looking one up

`dispatch()` / `dispatchSync()` return the process directly, and you can always fetch
one later by its public UUID:

```php
use ArielMejiaDev\LaravelAdobePdf\Facades\LaravelAdobePdf;

$process = LaravelAdobePdf::process($uuid);
```

## Status

The `status` attribute is a `ProcessStatus` enum:

```php
use ArielMejiaDev\LaravelAdobePdf\Enums\ProcessStatus;

$process->status;            // ProcessStatus::Completed
$process->status->value;     // 'completed'

$process->isSuccessful();    // status === Completed
$process->hasFailed();       // status === Failed
$process->isFinished();      // Completed or Failed
```

| Status | Meaning |
| ------ | ------- |
| `Pending` | Queued, not started |
| `Uploading` | Uploading source assets to Adobe |
| `Processing` | Submitted; polling Adobe for completion |
| `Completed` | Done; result stored |
| `Failed` | Failed; see `error` |

## Reading the result

```php
$bytes = $process->output();      // the resulting file's contents (or null)
$url   = $process->outputUrl();   // a URL to the stored file (disk-dependent)

$process->output_disk;            // e.g. 'local'
$process->output_path;            // e.g. 'adobe-pdf/{uuid}/output.pdf'
```

## Inspecting failures

```php
if ($process->hasFailed()) {
    $process->error;
    // [
    //   'code'    => 'INVALID_ASSET_ID',
    //   'message' => 'The provided asset ID is invalid.',
    //   'status'  => 400,
    //   'context' => [ ...raw Adobe payload... ],
    // ]
}
```

## Querying (for a dashboard)

`LaravelAdobePdf::processes()` returns an Eloquent query builder, with a few handy
scopes:

```php
LaravelAdobePdf::processes()->running()->get();      // pending / uploading / processing
LaravelAdobePdf::processes()->successful()->get();   // completed
LaravelAdobePdf::processes()->failed()->latest()->get();

// Or query the model directly
use ArielMejiaDev\LaravelAdobePdf\Models\AdobePdfProcess;

AdobePdfProcess::failed()->count();
```

## Useful columns

| Column | Description |
| ------ | ----------- |
| `uuid` | Public identifier (used by `process()` and route-model binding) |
| `operation` | Operation key (`create`, `extract`, …) |
| `status` | `ProcessStatus` enum |
| `options` | Operation options (elements, compression level, merge data, …) |
| `asset_ids` | Adobe asset IDs of the uploaded inputs |
| `poll_attempts` | How many times the status has been polled |
| `error` | Structured error payload when failed |
| `started_at` / `completed_at` / `failed_at` | Lifecycle timestamps |
