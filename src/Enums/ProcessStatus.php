<?php

namespace ArielMejiaDev\LaravelAdobePdf\Enums;

enum ProcessStatus: string
{
    case Pending = 'pending';
    case Uploading = 'uploading';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function isFinished(): bool
    {
        return in_array($this, [self::Completed, self::Failed], true);
    }

    public function isSuccessful(): bool
    {
        return $this === self::Completed;
    }
}
