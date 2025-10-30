<?php

namespace App\Exports;

use App\Models\ScrapingResult; // Sesuaikan dengan nama model Anda
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ScrapingExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Ambil semua data scraping dari database
        // Sesuaikan query dengan kebutuhan Anda
        return ScrapingResult::orderBy('created_at', 'desc')->get();
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'No',
            'Kutipan',
            'Berita URL',
            'Tanggal Publish',
            'Speaker',
            'Sumber',
            'Status',
            'Tanggal Scraping'
        ];
    }

    /**
     * Map data untuk setiap row
     */
    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->kutipan ?? $row->content ?? '', // Sesuaikan dengan nama kolom di database
            $row->berita_url ?? $row->url ?? '',
            $row->tanggal_publish ?? $row->published_at ?? '',
            $row->speaker ?? '-',
            $row->sumber ?? $row->source ?? '',
            $row->status ?? 'scraped',
            $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : ''
        ];
    }

    /**
     * Style untuk Excel
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style untuk header
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'] // Warna biru
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ],
        ];
    }

    /**
     * Set lebar kolom
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 80,  // Kutipan
            'C' => 30,  // Berita URL
            'D' => 20,  // Tanggal Publish
            'E' => 40,  // Speaker
            'F' => 20,  // Sumber
            'G' => 15,  // Status
            'H' => 20,  // Tanggal Scraping
        ];
    }
}