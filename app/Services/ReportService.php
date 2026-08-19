<?php

namespace App\Services;

use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

class ReportService
{
    /**
     * Build a standardized PDF download response with Pil Andina styling.
     */
    public static function download(string $view, array $data, string $filename): PdfBuilder
    {
        return Pdf::view($view, $data)
            ->format('a4')
            ->margins(16, 20, 16, 20)
            ->name($filename)
            ->download();
    }
}
