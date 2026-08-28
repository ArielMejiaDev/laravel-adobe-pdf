# Extract PDF

Extract text, tables and figures from a PDF as **structured JSON**, with optional
renditions of tables and figures.

> Adobe endpoint: `extractpdf` · [reference](https://developer.adobe.com/document-services/docs/apis/#operation/pdfoperations.extractpdf)

## Basic usage

```php
use ArielMejiaDev\LaravelAdobePdf\Facades\LaravelAdobePdf;

LaravelAdobePdf::extract('invoice.pdf')
    ->text()
    ->tables()
    ->dispatch();
```

## Methods

| Method | Description |
| ------ | ----------- |
| `from(string\|Source $input)` | Set the source PDF. |
| `text()` | Extract text elements. |
| `tables()` | Extract table elements. |
| `renditions(string ...$types)` | Also render `tables` and/or `figures` as files. |
| `tableFormat(string $format)` | Rendered table format: `csv` or `xlsx`. |
| `withCharBounds(bool $enabled = true)` | Include character bounding boxes. |

If you don't call `text()` or `tables()`, the operation defaults to extracting `text`.

```php
LaravelAdobePdf::extract('report.pdf')
    ->text()
    ->tables()
    ->renditions('tables', 'figures')
    ->tableFormat('csv')
    ->withCharBounds()
    ->dispatch();
```

## Result

A **ZIP** archive containing `structuredData.json` plus any requested renditions,
stored as `output.zip` on the process.

```php
$zipBytes = $process->output();
```

Inside the archive:

- `structuredData.json` — an ordered list of `elements` (headings, paragraphs, tables,
  figures) with their text and, for tables/figures, `filePaths` pointing at rendition
  files in the same zip.
- `tables/` — table renditions (`.csv` / `.xlsx` and/or `.png`), when you requested them.
- `figures/` — image renditions (`.png`), when you requested them.

## Working with the extracted content

The snippets below assume you ran extraction with text, tables and figure renditions:

```php
$process = LaravelAdobePdf::extract('invoice.pdf')
    ->text()
    ->tables()
    ->renditions('tables', 'figures')
    ->tableFormat('csv')
    ->dispatchSync();
```

### Parsing the archive into blocks

This helper opens the zip, reads `structuredData.json`, and flattens it into a simple
list of blocks — headings, paragraphs, tables (parsed from CSV) and images (as base64) —
that you can render however you like.

```php
use ZipArchive;

function extractedBlocks(string $zipBytes): array
{
    $tmp = tempnam(sys_get_temp_dir(), 'adobe-extract').'.zip';
    file_put_contents($tmp, $zipBytes);

    $zip = new ZipArchive;
    $zip->open($tmp);

    $structured = json_decode($zip->getFromName('structuredData.json'), true);

    $blocks = [];

    foreach ($structured['elements'] ?? [] as $element) {
        $path = $element['Path'] ?? '';

        // Text: headings (//Document/H1, /H2, …) and paragraphs (//Document/P)
        if (isset($element['Text'])) {
            if (preg_match('#/H(\d)#', $path, $m)) {
                $blocks[] = ['type' => 'heading', 'level' => (int) $m[1], 'text' => $element['Text']];
            } else {
                $blocks[] = ['type' => 'text', 'text' => $element['Text']];
            }

            continue;
        }

        // Tables: read the CSV rendition referenced by the element
        if (str_contains($path, '/Table') && isset($element['filePaths'])) {
            foreach ($element['filePaths'] as $file) {
                if (str_ends_with($file, '.csv')) {
                    $rows = array_map('str_getcsv', explode("\n", trim($zip->getFromName($file))));
                    $blocks[] = ['type' => 'table', 'rows' => $rows];
                }
            }

            continue;
        }

        // Figures / images: read the PNG rendition
        if (str_contains($path, '/Figure') && isset($element['filePaths'])) {
            foreach ($element['filePaths'] as $file) {
                if (str_ends_with($file, '.png')) {
                    $blocks[] = ['type' => 'image', 'data' => base64_encode($zip->getFromName($file))];
                }
            }
        }
    }

    $zip->close();
    @unlink($tmp);

    return $blocks;
}
```

```php
$blocks = extractedBlocks($process->output());
```

### Render it in a Blade view

Pass the blocks to a view from your controller:

```php
public function show(string $uuid)
{
    $process = LaravelAdobePdf::process($uuid);

    return view('extraction.show', [
        'blocks' => extractedBlocks($process->output()),
    ]);
}
```

`resources/views/extraction/show.blade.php`:

```blade
<article>
    @foreach ($blocks as $block)
        @switch($block['type'])
            @case('heading')
                @php($level = min($block['level'], 6))
                <h{{ $level }}>{{ $block['text'] }}</h{{ $level }}>
                @break

            @case('text')
                <p>{{ $block['text'] }}</p>
                @break

            @case('table')
                <table class="table">
                    @foreach ($block['rows'] as $row)
                        <tr>
                            @foreach ($row as $cell)
                                <td>{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </table>
                @break

            @case('image')
                <img src="data:image/png;base64,{{ $block['data'] }}" alt="Extracted figure">
                @break
        @endswitch
    @endforeach
</article>
```

### Export to a plain-text file

Keep the headings and paragraphs, and flatten tables to tab-separated rows:

```php
use Illuminate\Support\Facades\Storage;

$lines = [];

foreach (extractedBlocks($process->output()) as $block) {
    $lines[] = match ($block['type']) {
        'heading', 'text' => $block['text'],
        'table' => collect($block['rows'])
            ->map(fn ($row) => implode("\t", $row))
            ->implode("\n"),
        'image' => '[image]',
    };
}

Storage::disk('local')->put('invoice.txt', implode("\n\n", $lines));
```

### Export to a Word document

Build a `.docx` with [PhpWord](https://github.com/PHPOffice/PHPWord)
(`composer require phpoffice/phpword`), mapping each block to a Word element:

```php
use PhpOffice\PhpWord\PhpWord;

$word = new PhpWord;
$section = $word->addSection();

foreach (extractedBlocks($process->output()) as $block) {
    switch ($block['type']) {
        case 'heading':
            $section->addTitle($block['text'], min($block['level'], 6));
            break;

        case 'text':
            $section->addText($block['text']);
            break;

        case 'table':
            $table = $section->addTable();
            foreach ($block['rows'] as $row) {
                $table->addRow();
                foreach ($row as $cell) {
                    $table->addCell(2000)->addText($cell);
                }
            }
            break;

        case 'image':
            $file = tempnam(sys_get_temp_dir(), 'fig').'.png';
            file_put_contents($file, base64_decode($block['data']));
            $section->addImage($file, ['width' => 400]);
            break;
    }
}

$word->save(storage_path('app/invoice.docx'));
```

::: tip Round-trip back to PDF
Because Word documents are a valid input to [Create PDF](/operations/create-pdf) and
`.docx` templates drive [Document Generation](/operations/document-generation), you can
feed this generated file straight back into the pipeline if you need a polished PDF from
the extracted content.
:::

## Request payload

```json
{
  "assetID": "urn:aaid:AS:UE1:...",
  "elementsToExtract": ["text", "tables"],
  "renditionsToExtract": ["tables", "figures"],
  "tableOutputFormat": "csv",
  "getCharBounds": true
}
```
