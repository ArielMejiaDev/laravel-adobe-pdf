import { defineConfig } from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: 'Laravel Adobe PDF',
  description:
    'A fluent, async-first Laravel wrapper around the Adobe PDF Services API — combine, compress, create, extract, generate, HTML-to-PDF and watermark, all through queued jobs.',

  // Project site served at https://arielmejiadev.github.io/laravel-adobe-pdf/
  base: '/laravel-adobe-pdf/',

  lastUpdated: true,
  cleanUrls: true,

  head: [
    ['meta', { name: 'theme-color', content: '#ec1c24' }],
    ['meta', { property: 'og:type', content: 'website' }],
    ['meta', { property: 'og:title', content: 'Laravel Adobe PDF' }],
    [
      'meta',
      {
        property: 'og:description',
        content: 'Fluent, async-first Laravel wrapper around the Adobe PDF Services API.',
      },
    ],
  ],

  themeConfig: {
    // https://vitepress.dev/reference/default-theme-config
    nav: [
      { text: 'Guide', link: '/guide/introduction' },
      { text: 'Operations', link: '/operations/overview' },
      {
        text: 'Links',
        items: [
          { text: 'Packagist', link: 'https://packagist.org/packages/arielmejiadev/laravel-adobe-pdf' },
          { text: 'Adobe PDF Services API', link: 'https://developer.adobe.com/document-services/docs/apis/' },
        ],
      },
    ],

    sidebar: {
      '/guide/': [
        {
          text: 'Introduction',
          items: [
            { text: 'What is it?', link: '/guide/introduction' },
            { text: 'Getting Started', link: '/guide/getting-started' },
            { text: 'Configuration', link: '/guide/configuration' },
            { text: 'How It Works', link: '/guide/how-it-works' },
          ],
        },
        {
          text: 'Going Further',
          items: [
            { text: 'Queues & Chaining', link: '/guide/queues-and-chaining' },
            { text: 'Tracking Processes', link: '/guide/processes' },
            { text: 'Error Handling', link: '/guide/error-handling' },
            { text: 'Testing', link: '/guide/testing' },
          ],
        },
      ],
      '/operations/': [
        {
          text: 'Operations',
          items: [
            { text: 'Overview', link: '/operations/overview' },
            { text: 'Create PDF', link: '/operations/create-pdf' },
            { text: 'Extract PDF', link: '/operations/extract-pdf' },
            { text: 'Compress PDF', link: '/operations/compress-pdf' },
            { text: 'Combine PDF', link: '/operations/combine-pdf' },
            { text: 'Document Generation', link: '/operations/document-generation' },
            { text: 'HTML to PDF', link: '/operations/html-to-pdf' },
            { text: 'Watermark', link: '/operations/watermark' },
          ],
        },
      ],
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/arielmejiadev/laravel-adobe-pdf' },
    ],

    editLink: {
      pattern: 'https://github.com/arielmejiadev/laravel-adobe-pdf/edit/main/docs/:path',
      text: 'Edit this page on GitHub',
    },

    search: {
      provider: 'local',
    },

    footer: {
      message: 'Released under the MIT License.',
      copyright: 'Copyright © 2026 ArielMejiaDev',
    },
  },
})
