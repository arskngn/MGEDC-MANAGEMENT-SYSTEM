<?php

namespace App\Services;

use App\Models\GeneralSetting;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    /**
     * Generate a PDF from a Blade view and return it as a download response.
     */
    public function download(string $view, array $data, string $filename, string $orientation = 'portrait', string $format = 'A4'): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->buildPdf($view, $data, $orientation, $format);

        return $pdf->download($filename);
    }

    /**
     * Generate a PDF from a Blade view and return it as an inline/stream response (for browser viewing).
     */
    public function stream(string $view, array $data, string $filename, string $orientation = 'portrait', string $format = 'A4'): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->buildPdf($view, $data, $orientation, $format);

        return $pdf->stream($filename);
    }

    /**
     * Build a DomPDF instance from a Blade view.
     */
    protected function buildPdf(string $view, array $data, string $orientation = 'portrait', string $format = 'A4'): \Barryvdh\DomPDF\PDF
    {
        $pdf = Pdf::loadView($view, $data)
            ->setPaper($format, $orientation)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'serif')
            ->setOption('chroot', public_path());

        return $pdf;
    }

    /**
     * Get the logo as a base64 data URI for embedding in PDF views.
     */
    public function logoDataUri(?GeneralSetting $generalSetting = null): ?string
    {
        $generalSetting = $generalSetting ?? GeneralSetting::first();

        if (! $generalSetting || ! $generalSetting->logo_light) {
            return null;
        }

        $path = public_path($generalSetting->logo_light);

        if (! is_readable($path)) {
            return null;
        }

        $data = @file_get_contents($path);

        if ($data === false) {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml',
            default => 'image/jpeg',
        };

        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }
}

