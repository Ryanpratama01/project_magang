<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class YoutubeScrapingExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
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
        $query = DB::table('youtube_comments1')
            ->orderBy('scraped_at', 'desc');

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
            'Video ID',
            'Judul Video',
            'URL Video',
            'Nama Channel',
            'Komentar Asli',
            'Komentar Bersih',
            'Nama Author',
            'Jumlah Like',
            'Jumlah Balasan',
            'Tanggal Publish',
            'Tanggal Scraping',
            'Status'
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
            $row->video_id ?? '',
            $row->video_title ?? '',
            $row->video_url ?? '',
            $row->channel_name ?? '',
            $row->comment_text ?? '',
            $row->cleaned_text ?? '',
            $row->author_name ?? '',
            $row->likes_count ?? 0,
            $row->replies_count ?? 0,
            $row->published_at ?? '',
            $row->scraped_at ?? '',
            $row->processing_status ?? 'scraped'
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
                    'startColor' => ['rgb' => 'FF0000'] // Warna merah YouTube
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
            'B' => 20,  // Video ID
            'C' => 50,  // Judul Video
            'D' => 40,  // URL Video
            'E' => 30,  // Nama Channel
            'F' => 80,  // Komentar Asli
            'G' => 80,  // Komentar Bersih
            'H' => 25,  // Nama Author
            'I' => 12,  // Jumlah Like
            'J' => 12,  // Jumlah Balasan
            'K' => 20,  // Tanggal Publish
            'L' => 20,  // Tanggal Scraping
            'M' => 15,  // Status
        ];
    }
}