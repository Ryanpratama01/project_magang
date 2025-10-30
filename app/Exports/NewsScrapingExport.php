<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NewsScrapingExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $ids;

    /**
     * Constructor untuk terima IDs yang dipilih (opsional)
     */
    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = DB::table('news_quotes')
            ->orderBy('created_at', 'desc');

        // Jika ada IDs yang dipilih, filter berdasarkan IDs
        if ($this->ids && is_array($this->ids) && count($this->ids) > 0) {
            $query->whereIn('id', $this->ids);
        }

        return $query->get();
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'No',
            'Kutipan Asli',
            'Kutipan Bersih',
            'Speaker',
            'Berita URL',
            'Sumber Berita',
            'Konteks',
            'Tanggal Publish',
            'Tanggal Scraping',
            'Status',
            'Dibuat',
            'Diupdate'
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
            $row->original_text ?? '',
            $row->cleaned_text ?? '',
            $row->speaker ?? '-',
            $row->news_url ?? '',
            $row->news_source ?? '',
            $row->context ?? '',
            $row->published_at ?? '',
            $row->scraped_at ?? '',
            $row->processing_status ?? 'scraped',
            $row->created_at ?? '',
            $row->updated_at ?? ''
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
            'B' => 80,  // Kutipan Asli
            'C' => 80,  // Kutipan Bersih
            'D' => 30,  // Speaker
            'E' => 40,  // Berita URL
            'F' => 20,  // Sumber Berita
            'G' => 40,  // Konteks
            'H' => 20,  // Tanggal Publish
            'I' => 20,  // Tanggal Scraping
            'J' => 15,  // Status
            'K' => 20,  // Dibuat
            'L' => 20,  // Diupdate
        ];
    }
}