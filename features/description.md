Description of the package

I want to replicate the Adobe PDF Services Open API to handle the requests calls and responses using the Laravel Elegant Syntax and providing all the Laravel features like Jobs, Queues, etc.

## Notes

Use a fluent and elegant syntax like: https://laravel.com/framework/docs/13.x/strings#strings

## Features

All the features from the Adobe PDF Services Open API docs site should be adapt, but some of the most important ones are:

- Combine PDF https://developer.adobe.com/document-services/docs/apis/#tag/Combine-PDF

- Compress PDF https://developer.adobe.com/document-services/docs/apis/#tag/Compress-PDF

- Create PDF https://developer.adobe.com/document-services/docs/apis/#tag/Create-PDF

- Document Generation https://developer.adobe.com/document-services/docs/apis/#tag/Document-Generation

- Extract PDF Content: https://developer.adobe.com/document-services/docs/apis/#operation/pdfoperations.extractpdf

- Create PDF document from non PDF document https://developer.adobe.com/document-services/docs/apis/#operation/pdfoperations.createpdf

- Add Watermark in PDF Document https://developer.adobe.com/document-services/docs/apis/#tag/PDF-Watermark

- HTML to PDF https://developer.adobe.com/document-services/docs/apis/#tag/Html-to-PDF

## Take in mind

All this features would require to make a request to a third party service using env variables and it's own config file, also would require to use behind the scenes in the abstraction the Http Laravel Client and use Laravel Jobs.

The idea is to use the Jobs indepently or use them in a bus of jobs so a file content could be, eg:

- extracted
- manipulated the content to add elements
- generate a new pdf from that content
- add a water mark
- all in the same process

Take in mind that a process could failed in any step by different reasons like, internet issues, reach a limit for API calls, memory issues, take in mind custom exceptions to comunicate the issues correctly like in this example: https://developer.adobe.com/document-services/docs/apis/#operation/pdfoperations.extractpdf!c=429&path=error/message&t=response

As Laravel Jobs could be stored in a database table would be nice to have optionally a dashboard with the processes that are running and the ones that are successful as PDF generation could show a good or bad response with validation messages or different http statuses, all this issues description should be show
