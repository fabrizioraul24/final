<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ReportService
{
    /**
     * Build a standardized PDF download response with Pil Andina styling.
     */
    public static function download(string $view, array $data, string $filename, string $orientation = 'portrait')
    {
        $data['generatedAt'] ??= now();
        $data['reportCode'] ??= self::makeReportCode($filename);

        return Pdf::loadView($view, $data)
            ->setPaper('a4', $orientation === 'landscape' ? 'landscape' : 'portrait')
            ->download($filename);
    }

    private static function makeReportCode(string $filename): string
    {
        $base = Str::slug(pathinfo($filename, PATHINFO_FILENAME));
        $words = collect(explode('-', $base))
            ->reject(fn (string $word) => in_array($word, ['reporte', 'reportes', 'pdf', 'de', 'del', 'la', 'el'], true))
            ->values();
        $prefix = $words->count() === 1
            ? Str::upper(Str::substr($words->first() ?: 'rep', 0, 3))
            : Str::upper($words->map(fn (string $word) => Str::substr($word, 0, 1))->take(3)->implode(''));
        $prefix = $prefix ?: 'RPT';
        $cacheKey = 'report_code_sequence:'.now()->format('Ymd').':'.$prefix;

        Cache::add($cacheKey, 0, now()->addDay());
        $sequence = Cache::increment($cacheKey);

        return $prefix.'-'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }
}
