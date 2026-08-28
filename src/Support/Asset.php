<?php

namespace ArielMejiaDev\LaravelAdobePdf\Support;

/**
 * A file registered with Adobe PDF Services.
 *
 * When first created it carries a presigned "uploadUri" that the file bytes are
 * PUT to. Afterwards only the "assetID" is needed to reference it in operations.
 */
final class Asset
{
    public function __construct(
        public readonly string $assetID,
        public readonly ?string $uploadUri = null,
        public readonly string $mediaType = MediaType::PDF,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromResponse(array $data, string $mediaType = MediaType::PDF): self
    {
        return new self(
            assetID: $data['assetID'],
            uploadUri: $data['uploadUri'] ?? null,
            mediaType: $mediaType,
        );
    }
}
