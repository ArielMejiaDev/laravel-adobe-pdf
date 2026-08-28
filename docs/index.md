---
layout: home

hero:
  name: Laravel Adobe PDF
  text: Adobe PDF Services, the Laravel way
  tagline: A fluent, async-first wrapper around the Adobe PDF Services API — combine, compress, create, extract, generate, HTML-to-PDF and watermark, all through queued jobs.
  actions:
    - theme: brand
      text: Get Started
      link: /guide/getting-started
    - theme: alt
      text: Operations
      link: /operations/overview
    - theme: alt
      text: View on GitHub
      link: https://github.com/arielmejiadev/laravel-adobe-pdf

features:
  - icon: 🧩
    title: Fluent & elegant
    details: An expressive builder for every operation — LaravelAdobePdf::extract('invoice.pdf')->tables()->text()->dispatch().
  - icon: ⚙️
    title: Async first, sync when you want
    details: Every operation is a queued job with non-blocking polling. Need it inline? dispatchSync() runs the same job and returns the result.
  - icon: 🔗
    title: Composable pipelines
    details: Each operation exposes a job via toJob(), so you can chain extract → manipulate → generate → watermark with Bus::chain().
  - icon: 📊
    title: Tracked out of the box
    details: Every operation is recorded in the adobe_pdf_processes table with its status, Adobe error payload and output path — ready for a dashboard.
  - icon: 🚨
    title: Typed errors
    details: Failures surface as RateLimitException, AuthenticationException, OperationFailedException and TimeoutException.
  - icon: 🧪
    title: Fully testable
    details: Built on Laravel's HTTP client, so the whole Adobe flow mocks cleanly with Http::fake().
---
