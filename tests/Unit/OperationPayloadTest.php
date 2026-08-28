<?php

use ArielMejiaDev\LaravelAdobePdf\Operations\CombinePdf;
use ArielMejiaDev\LaravelAdobePdf\Operations\CompressPdf;
use ArielMejiaDev\LaravelAdobePdf\Operations\CreatePdf;
use ArielMejiaDev\LaravelAdobePdf\Operations\DocumentGeneration;
use ArielMejiaDev\LaravelAdobePdf\Operations\ExtractPdf;
use ArielMejiaDev\LaravelAdobePdf\Operations\HtmlToPdf;
use ArielMejiaDev\LaravelAdobePdf\Operations\Watermark;

it('builds the create payload', function () {
    $payload = (new CreatePdf('doc.docx'))->language('en-US')->buildPayload(['urn:1']);

    expect($payload)->toBe([
        'assetID' => 'urn:1',
        'documentLanguage' => 'en-US',
    ]);
});

it('builds the extract payload with elements, renditions and table format', function () {
    $payload = (new ExtractPdf('in.pdf'))
        ->text()
        ->tables()
        ->renditions('tables', 'figures')
        ->tableFormat('csv')
        ->withCharBounds()
        ->buildPayload(['urn:1']);

    expect($payload)->toBe([
        'assetID' => 'urn:1',
        'elementsToExtract' => ['text', 'tables'],
        'renditionsToExtract' => ['tables', 'figures'],
        'tableOutputFormat' => 'csv',
        'getCharBounds' => true,
    ]);
});

it('defaults extract to text and outputs a zip', function () {
    $operation = new ExtractPdf('in.pdf');

    expect($operation->buildPayload(['urn:1']))->toBe(['assetID' => 'urn:1', 'elementsToExtract' => ['text']])
        ->and($operation->outputExtension())->toBe('zip');
});

it('builds the compress payload with an upper-cased level', function () {
    $payload = (new CompressPdf('in.pdf'))->level('high')->buildPayload(['urn:1']);

    expect($payload)->toBe(['assetID' => 'urn:1', 'compressionLevel' => 'HIGH']);
});

it('builds the combine payload as an assets list with page ranges', function () {
    $payload = (new CombinePdf)
        ->add('a.pdf', [[1, 3]])
        ->add('b.pdf')
        ->buildPayload(['urn:a', 'urn:b']);

    expect($payload)->toBe([
        'assets' => [
            ['assetID' => 'urn:a', 'pageRanges' => [['start' => 1, 'end' => 3]]],
            ['assetID' => 'urn:b'],
        ],
    ]);
});

it('builds the document generation payload', function () {
    $operation = (new DocumentGeneration('template.docx', ['name' => 'Ada']))->asWord();

    expect($operation->buildPayload(['urn:1']))->toBe([
        'assetID' => 'urn:1',
        'outputFormat' => 'docx',
        'jsonDataForMerge' => ['name' => 'Ada'],
    ])->and($operation->outputExtension())->toBe('docx');
});

it('builds the html to pdf payload from a url with stringified json', function () {
    $payload = (new HtmlToPdf('https://example.com'))
        ->data(['title' => 'Hi'])
        ->pageSize(8.5, 11.0)
        ->buildPayload([]);

    expect($payload)->toBe([
        'inputUrl' => 'https://example.com',
        'json' => '{"title":"Hi"}',
        'pageLayout' => ['pageWidth' => 8.5, 'pageHeight' => 11.0],
    ]);
});

it('prefers an uploaded asset over the url for html to pdf', function () {
    $payload = (new HtmlToPdf('page.zip'))->buildPayload(['urn:zip']);

    expect($payload['assetID'])->toBe('urn:zip')
        ->and($payload)->not->toHaveKey('inputUrl');
});

it('builds the watermark payload keeping document then watermark order', function () {
    $payload = (new Watermark('doc.pdf', 'mark.pdf'))
        ->opacity(50)
        ->onForeground()
        ->pages([[2, 5]])
        ->buildPayload(['urn:doc', 'urn:mark']);

    expect($payload)->toBe([
        'inputDocumentAssetID' => 'urn:doc',
        'watermarkDocumentAssetID' => 'urn:mark',
        'appearance' => ['opacity' => 50, 'appearOnForeground' => true],
        'pageRanges' => [['start' => 2, 'end' => 5]],
    ]);
});

it('builds the watermark payload from the fluent document/watermark methods', function () {
    $payload = (new Watermark)
        ->document('doc.pdf')
        ->watermark('mark.pdf')
        ->buildPayload(['urn:doc', 'urn:mark']);

    expect($payload)->toBe([
        'inputDocumentAssetID' => 'urn:doc',
        'watermarkDocumentAssetID' => 'urn:mark',
    ]);
});
