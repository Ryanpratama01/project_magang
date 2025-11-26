<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;
use App\Models\Report;
use Illuminate\Support\Str;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Collection; // pastikan di top

class ReportController extends Controller
{
    protected array $newsCfg = [
        'articles_table' => 'news_articles',
        'article_id'     => 'id',
        'article_date'   => 'published_at',
        'article_clean'  => 'summary_clean',
        'sents_table'    => 'news_article_sentiments',
        'sent_fk'        => 'article_fk',
        'sent_label'     => 'label',
        'sent_time'      => 'analyzed_at',
    ];

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $base = Report::query()
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('report_name', 'like', "%{$q}%")
                        ->orWhere('file_name', 'like', "%{$q}%")
                        ->orWhere('source', 'like', "%{$q}%");
                });
            });

        $total = (clone $base)->count();

        $reports = $base->latest()
            ->paginate(10)
            ->withQueryString();

        $reports->getCollection()->transform(function ($r) {
            $r->size_human = $this->humanFileSize((int)($r->file_size ?? 0));
            return $r;
        });

        $q = $request->query('q');
        if ($q) {
            $reports->where(function ($qq) use ($q) {
                $qq->where('report_name', 'like', "%$q%")
                    ->orWhere('source', 'like', "%$q%")
                    ->orWhere('file_name', 'like', "%$q%");
            });
        }

        // Jika request AJAX, kirim partial tabel saja
        if ($request->ajax()) {
            // kembalikan HTML table + hitung total supaya bisa diupdate
            return response()->json([
                'html'  => view('reports._table', ['reports' => $reports])->render(),
                'total' => method_exists($reports, 'total') ? $reports->total()
                    : (is_countable($reports) ? count($reports) : 0),
            ]);
        }

        return view('report', [
            'reports' => $reports,
            'q'       => $q,
            'total'   => $total,
        ]);
    }

    // HALAMAN UI: grafik bulanan (web page, bukan JSON)
    // ganti signature agar menerima Request
    public function chartsPage(Request $request)
    {
        $qNow   = trim((string) $request->query('q', ''));
        $yNow   = (int) now()->year;
        $years  = range($yNow - 5, $yNow + 1);

        $base = \App\Models\Report::query()
            ->when($qNow !== '', function ($qq) use ($qNow) {
                $qq->where(function ($w) use ($qNow) {
                    $w->where('report_name', 'like', "%{$qNow}%")
                        ->orWhere('file_name', 'like', "%{$qNow}%")
                        ->orWhere('source', 'like', "%{$qNow}%");
                });
            });

        $total = (clone $base)->count();

        $reports = $base->latest()
            ->paginate(10)
            ->withQueryString();

        $reports->getCollection()->transform(function ($r) {
            $r->size_human = $this->humanFileSize((int)($r->file_size ?? 0));
            return $r;
        });

        $q = $request->query('q');

        if ($q) {
            $reports->where(function ($qq) use ($q) {
                $qq->where('report_name', 'like', "%$q%")
                    ->orWhere('source', 'like', "%$q%")
                    ->orWhere('file_name', 'like', "%$q%");
            });
        }

        // Jika request AJAX, kirim partial tabel saja
        if ($request->ajax()) {
            // kembalikan HTML table + hitung total supaya bisa diupdate
            return response()->json([
                'html'  => view('reports._table', ['reports' => $reports])->render(),
                'total' => method_exists($reports, 'total') ? $reports->total()
                    : (is_countable($reports) ? count($reports) : 0),
            ]);
        }

        return view('reports.charts', [
            'defaultYear' => $yNow,
            'years'       => $years,
            'reports'     => $reports,
            'q'           => $qNow,
            'total'       => $total,
        ]);
    }

    private function uniqueReportName(string $name, int $maxLen = 150, bool &$renamed = false): string
    {
        $renamed = false;

        $base = trim($name);
        if ($base === '') $base = 'Laporan';

        // pangkas agar ruang suffix cukup
        $base   = mb_substr($base, 0, $maxLen);
        $final  = $base;
        $i      = 2;

        while (Report::where('report_name', $final)->exists()) {
            $suffix = " ($i)";
            $final  = mb_substr($base, 0, $maxLen - mb_strlen($suffix)) . $suffix;
            $i++;
            $renamed = true;
        }
        return $final;
    }

    /**
     * Debug helper: Log data structure sebelum generate PDF
     */
    private function debugDataStructure(array $summary, $details, string $source): void
    {
        // ubah iterable -> array dengan aman
        if (is_iterable($details)) {
            $arr = is_array($details) ? $details : iterator_to_array($details);
        } else {
            $arr = [];
        }

        Log::info('=== PDF Generation Debug ===', [
            'source'        => $source,
            'summary_keys'  => array_keys($summary),
            'summary_data'  => $summary,
            'details_type'  => is_object($details) ? get_class($details) : gettype($details),
            'details_count' => is_countable($arr) ? count($arr) : 0,
            'first_item'    => $arr[0] ?? null,
        ]);

        if (!empty($arr)) {
            Log::info('First item structure:', [
                'keys'   => array_keys((array)$arr[0]),
                'sample' => $arr[0],
            ]);
        }
    }

    /**
     * Validate data sebelum generate PDF
     */
    private function validatePdfData(array $summary, $details, string $source): bool
    {
        // Cek summary
        $summaryKey = $source === 'news' ? 'news' : 'youtube';
        if (!isset($summary[$summaryKey])) {
            Log::error('Summary data missing', [
                'expected_key' => $summaryKey,
                'available_keys' => array_keys($summary)
            ]);
            return false;
        }

        // Cek details
        if (!is_iterable($details)) {
            Log::error('Details is not iterable', ['type' => gettype($details)]);
            return false;
        }

        $detailsArray = is_array($details) ? $details : iterator_to_array($details);
        if (empty($detailsArray)) {
            Log::warning('Details array is empty');
        }

        return true;
    }

    /* =============== Generate (PDF) =============== */
    public function generate(Request $request)
    {
        @ini_set('max_execution_time', 180);
        @ini_set('memory_limit', '512M');

        try {
            $validated = $request->validate([
                'report_name' => 'required|string|max:150',
                'report_type' => 'required|string|in:monthly,yearly',
                'start_date'  => 'required|date',
                'end_date'    => 'required|date|after_or_equal:start_date',
                'source'      => 'required|in:news,youtube',
            ]);

            // Konversi ke Carbon (lokal)
            $startLocal = Carbon::parse($validated['start_date'])->startOfDay();
            $endLocal   = Carbon::parse($validated['end_date'])->endOfDay();

            // Validasi tambahan: maksimal 12 bulan rentang
            $diffMonths = $startLocal->diffInMonths($endLocal);
            if ($diffMonths > 12) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rentang bulan maksimal 12 bulan. Anda memilih ' . ($diffMonths + 1) . ' bulan.'
                ], 422);
            }

            $source = $validated['source'];
            $rtype  = $validated['report_type'];

            // --- Nama laporan: otomatis unik + kirim peringatan bila diubah ---
            $rnameInput = trim($validated['report_name']);
            $renamed = false;
            $rname = $this->uniqueReportName($rnameInput, 150, $renamed);

            Storage::disk('public')->makeDirectory('reports');

            // Ambil data
            if ($source === 'news') {
                Log::info('Fetching news data', [
                    'start' => $startLocal->toDateString(),
                    'end'   => $endLocal->toDateString()
                ]);

                $rawResults = DB::table('news_articles as a')
                    ->leftJoin('news_article_sentiments as s', 'a.id', '=', 's.article_fk')
                    ->whereBetween('s.analyzed_at', [$startLocal, $endLocal])
                    ->groupBy('a.id', 'a.title', 'a.url', 'a.published_at', 'a.created_at')
                    ->selectRaw("
                        a.id,
                        a.title,
                        a.url,
                        a.published_at as date,
                        a.created_at,
                        COUNT(s.id) as total_sentiments,
                        SUM(CASE WHEN LOWER(s.label) = 'positive' THEN 1 ELSE 0 END) as pos,
                        SUM(CASE WHEN LOWER(s.label) = 'neutral' THEN 1 ELSE 0 END) as neu,
                        SUM(CASE WHEN LOWER(s.label) = 'negative' THEN 1 ELSE 0 END) as neg
                    ")
                    ->havingRaw('total_sentiments > 0')
                    ->orderBy('a.published_at', 'desc')
                    ->limit(300)
                    ->get();

                Log::info('News query executed', [
                    'total_found' => $rawResults->count(),
                    'sample_ids'  => $rawResults->take(3)->pluck('id')->toArray()
                ]);

                $details = $rawResults->map(function ($row) {
                    $pos   = (int) $row->pos;
                    $neu   = (int) $row->neu;
                    $neg   = (int) $row->neg;
                    $total = $pos + $neu + $neg;

                    if ($pos > $neu && $pos > $neg) $label = 'positive';
                    elseif ($neg > $neu && $neg > $pos) $label = 'negative';
                    else                                 $label = 'neutral';

                    return [
                        'id'           => $row->id,
                        'date'         => $row->date,
                        'published_at' => $row->date,
                        'created_at'   => $row->created_at ?? $row->date,
                        'title'        => $row->title ?? 'Tanpa Judul',
                        'url'          => $row->url ?? null,
                        'pos'          => $pos,
                        'pos_count'    => $pos,
                        'neu'          => $neu,
                        'neu_count'    => $neu,
                        'neg'          => $neg,
                        'neg_count'    => $neg,
                        'total'        => $total,
                        'label'        => $label,
                        'snippet'      => null,
                    ];
                });

                $details = $details instanceof \Illuminate\Support\Collection ? $details : collect($details);

                $summary['news'] = [
                    'positive' => (int) $details->sum('pos'),
                    'neutral'  => (int) $details->sum('neu'),
                    'negative' => (int) $details->sum('neg'),
                    'total'    => (int) $details->sum('total'),
                ];

                $totalRecords = $details->count();

                Log::info('News data processed successfully', [
                    'total_records'  => $totalRecords,
                    'summary'        => $summary['news'],
                    'first_3_titles' => $details->take(3)->pluck('title')->toArray()
                ]);
            } else {
                // YOUTUBE
                $ytDetails = $this->getYoutubeVideoSummary($startLocal, $endLocal, 300); // boleh array
                $details   = collect($ytDetails); // pastikan jadi Collection supaya aman di ->count()
                $summary['youtube'] = $this->aggregateYoutube($startLocal, $endLocal);
                $totalRecords = (int) ($summary['youtube']['total'] ?? 0);
            }

            if ($totalRecords === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data untuk periode yang dipilih.'
                ], 404);
            }

            // Nama file
            $periodSlug = $startLocal->format('Y-m') . '_to_' . $endLocal->format('Y-m');
            $base       = Str::slug($rname, '_');
            $filename   = "{$base}_{$rtype}_{$periodSlug}.pdf";
            for ($i = 1; Storage::disk('public')->exists("reports/$filename"); $i++) {
                $filename = "{$base}_{$rtype}_{$periodSlug}_{$i}.pdf";
            }

            // Simpan meta awal
            $reportRow = Report::create([
                'user_id'     => auth()->check() ? (auth()->user()->id_user ?? null) : null,
                'report_name' => $rname,
                'report_type' => $rtype,
                'source'      => $source,
                'start_date'  => $startLocal->toDateString(),
                'end_date'    => $endLocal->toDateString(),
                'format'      => 'pdf',
                'status'      => 'processing',
            ]);

            Log::info('Starting PDF generation', [
                'source'      => $source,
                'start_date'  => $startLocal->toDateString(),
                'end_date'    => $endLocal->toDateString(),
                'report_name' => $rname,
            ]);

            // Validasi ringan sebelum render
            if (!$this->validatePdfData(isset($summary) ? $summary : [], $details, $source)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak valid untuk generate PDF. Cek log untuk detail.'
                ], 500);
            }

            $this->debugDataStructure($summary ?? [], $details, $source);

            Log::info('=== PRE-PDF DEBUG ===', [
                'details_count' => $details->count(),
                'details_type'  => get_class($details),
                'summary'       => $summary ?? [],
                'first_item_keys' => array_keys((array) $details->first()),
            ]);

            $chartUrls = [];
            $chartImg  = [];

            // Render PDF (tabel)
            $this->makePdf(
                $filename,
                $summary ?? [],
                $startLocal,
                $endLocal,
                $rtype,
                $source,
                $details,
                $chartUrls,
                $chartImg,
                $rname,
                'table'
            );

            Log::info('Table PDF generation completed', [
                'filename'      => $filename,
                'source'        => $source,
                'total_records' => $totalRecords
            ]);

            $path = "reports/$filename";
            $size = Storage::disk('public')->exists($path) ? Storage::disk('public')->size($path) : 0;

            $reportRow->update([
                'file_path' => $path,
                'file_name' => $filename,
                'mime'      => 'application/pdf',
                'file_size' => $size,
                'status'    => 'completed',
            ]);

            $reportPayload = [
                'id'          => $reportRow->id,
                'report_name' => $reportRow->report_name,
                'report_type' => $reportRow->report_type,
                'source'      => $reportRow->source,
                'start_date'  => $reportRow->start_date,
                'end_date'    => $reportRow->end_date,
                'format'      => 'pdf',
                'file_name'   => $filename,
                'file_size'   => (int) $size,
                'size_human'  => $this->humanFileSize((int) $size),
                'created_at'  => optional($reportRow->created_at)
                    ? $reportRow->created_at->timezone(config('app.timezone'))->format('Y-m-d H:i:s')
                    : null,
            ];

            return response()->json([
                'success'       => true,
                'message'       => 'Report generated successfully',
                'filename'      => $filename,
                'preview_url'   => route('reports.preview',  ['filename' => $filename]),
                'download_url'  => route('reports.download', ['filename' => $filename]),
                'generated_at'  => now()->toDateTimeString(),
                'file_size'     => $this->humanFileSize($size ?? 0),
                'total_records' => $totalRecords,
                'summary'       => $summary ?? [],
                'report'        => $reportPayload,

                // ⚠️ peringatan bila nama bentrok & diubah
                'renamed'       => $renamed,
                'warning'       => $renamed ? ('Nama laporan "' . $rnameInput . '" sudah dipakai. Diubah menjadi "' . $rname . '".') : null,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            Log::error('reports.generate:error', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PDF grafik (endpoint: reports.generate.charts)
     */
    public function generateCharts(Request $request)
    {
        @ini_set('max_execution_time', 180);
        @ini_set('memory_limit', '512M');

        $validated = $request->validate([
            'report_name' => 'required|string|max:150',
            'source'      => 'required|in:news,youtube',
            'report_type' => 'nullable|in:monthly,yearly',
            'month'       => 'nullable|integer|min:1|max:12',
            'year'        => 'required|integer|min:2000|max:2100',
        ]);

        // --- Nama laporan: otomatis unik + warning ---
        $rnameInput = trim($validated['report_name']);
        $renamed = false;
        $rname   = $this->uniqueReportName($rnameInput, 150, $renamed);

        $source = $validated['source'];
        $rtype  = $validated['report_type'] ?? 'monthly';
        $year   = (int) $validated['year'];
        $month  = (int) ($validated['month'] ?? 0);

        // Tentukan periode
        if ($rtype === 'monthly') {
            if (!$month) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bulan wajib diisi untuk laporan bulanan.'
                ], 422);
            }
            $start = Carbon::create($year, $month, 1, 0, 0, 0)->startOfMonth();
            $end   = $start->copy()->endOfMonth();
        } else {
            $start = Carbon::create($year, 1, 1, 0, 0, 0)->startOfYear();
            $end   = $start->copy()->endOfYear();
        }

        // Summary
        if ($source === 'news') {
            $summary = ['news' => $this->aggregateNewsByMajority($start, $end)];
            $total   = (int) ($summary['news']['total'] ?? 0);
        } else {
            $summary = ['youtube' => $this->aggregateYoutube($start, $end)];
            $total   = (int) ($summary['youtube']['total'] ?? 0);
        }

        if ($total <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data untuk periode tersebut.'
            ], 404);
        }

        // Bangun chart
        if ($rtype === 'monthly') {
            $daily    = $this->getDailyTrendForMonth($start, $end, $source);
            $cfg      = $this->buildMonthlyCharts($summary, $daily, $source);
            $chartImg = [
                'bar' => $this->chartToBase64($cfg['bar']),
                'pie' => $this->chartToBase64($cfg['pie']),
            ];
            $chartUrls = $cfg;
        } else {
            $urls = $this->generateChartUrls($summary, $start, $end, $source);
            $chartImg = [
                'bar' => $this->chartToBase64($urls['bar']),
                'pie' => $this->chartToBase64($urls['pie']),
            ];
            $chartUrls = [
                'bar'     => $urls['bar'],
                'pie'     => $urls['pie'],
                'monthly' => $urls['monthly'],
            ];
        }

        // Nama file
        $periodSlug = $rtype === 'monthly'
            ? $start->format('Y_m')
            : $start->format('Y') . '_year';
        $base     = Str::slug($rname, '_');
        $filename = "{$base}_charts_{$periodSlug}.pdf";
        for ($i = 1; Storage::disk('public')->exists("reports/$filename"); $i++) {
            $filename = "{$base}_charts_{$periodSlug}_{$i}.pdf";
        }

        try {
            Log::info('=== STARTING CHART PDF GENERATION ===', [
                'filename' => $filename,
                'source'   => $source,
                'pdf_type' => 'chart'
            ]);

            // Query detail items untuk ditampilkan di halaman 2 PDF
            if ($source === 'news') {
                $detailItems = $this->getNewsDetailsByMajority($start, $end, 50);
            } else {
                $detailItems = $this->getYoutubeVideoSummary($start, $end, 50);
            }

            // Debug log (opsional, bisa dihapus nanti)
            Log::info('=== CHART PDF DATA DEBUG ===', [
                'source' => $source,
                'items_count' => count($detailItems),
                'first_item_keys' => !empty($detailItems) ? array_keys((array)$detailItems[0]) : [],
                'has_snippet' => !empty($detailItems[0]['snippet'] ?? null),
                'has_comment' => !empty($detailItems[0]['comment_text'] ?? null),
            ]);

            $this->makePdf(
                $filename,
                $summary,
                $start,
                $end,
                $rtype,
                $source,
                collect($detailItems),  // ← UBAH: kirim data sebenarnya
                $chartUrls,
                $chartImg,
                $rname,
                'chart'
            );


            Log::info('=== CHART PDF GENERATION COMPLETED ===');

            $path = "reports/$filename";
            $size = Storage::disk('public')->exists($path)
                ? Storage::disk('public')->size($path)
                : 0;

            // Simpan meta
            $reportRow = Report::create([
                'user_id'     => auth()->check() ? (auth()->user()->id_user ?? null) : null,
                'report_name' => $rname,
                'report_type' => $rtype,
                'source'      => $source,
                'start_date'  => $start->toDateString(),
                'end_date'    => $end->toDateString(),
                'format'      => 'pdf',
                'file_path'   => $path,
                'file_name'   => $filename,
                'mime'        => 'application/pdf',
                'file_size'   => $size,
                'status'      => 'completed',
            ]);

            return response()->json([
                'success'      => true,
                'message'      => 'Laporan grafik berhasil dibuat',
                'filename'     => $filename,
                'preview_url'  => route('reports.preview',  ['filename' => $filename]),
                'download_url' => route('reports.download', ['filename' => $filename]),
                'file_size'    => $this->humanFileSize((int) $size),
                'report_type'  => 'chart',

                // ⚠️ peringatan bila nama dibenturkan
                'renamed'      => $renamed,
                'warning'      => $renamed ? ('Nama laporan "' . $rnameInput . '" sudah dipakai. Diubah menjadi "' . $rname . '".') : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Chart PDF generation failed', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /* =============== CRUD Actions =============== */

    // Preview inline (PDF tampil di tab)
    public function preview($filename)
    {
        $path = 'reports/' . basename($filename);
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }
        $fullPath = Storage::disk('public')->path($path);
        return response()->file($fullPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($filename) . '"',
        ]);
    }

    // Download file
    public function download($filename)
    {
        $path = 'reports/' . basename($filename);
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }
        $fullPath = Storage::disk('public')->path($path);
        return response()->download($fullPath, basename($filename));
    }

    public function destroy(Request $request, Report $report)
    {
        if ($report->file_path && Storage::disk('public')->exists($report->file_path)) {
            Storage::disk('public')->delete($report->file_path);
        }
        $report->delete();

        // Jika dipanggil via AJAX, balas JSON
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Laporan berhasil dihapus.']);
        }

        // Default (submit form biasa): redirect back + flash message
        return redirect()->back()->with('status', 'Laporan berhasil dihapus.');
    }

    public function chartsDetails(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'source'     => 'required|in:news,youtube',
            'page'       => 'sometimes|integer|min:1',
            'per_page'   => 'sometimes|integer|min:1|max:100',
        ]);

        $start = \Carbon\Carbon::parse($validated['start_date'])->startOfDay();
        $end   = \Carbon\Carbon::parse($validated['end_date'])->endOfDay();
        $src   = $validated['source'];
        $page     = (int)($validated['page'] ?? 1);
        $perPage  = (int)($validated['per_page'] ?? 10);

        // Ambil "raw" list cukup besar lalu slice (cepat untuk mulai; nanti bisa dioptimasi OFFSET/LIMIT)
        if ($src === 'news') {
            $rows = $this->getNewsDetailsByMajority($start, $end, 1000); // sudah mengembalikan: date,label,title,url,snippet,pos,neu,neg
        } else {
            $rows = $this->getYoutubeVideoSummary($start, $end, 1000);   // mengembalikan: date,title,url,pos,neu,neg,total
            // samakan key agar kolom konsisten
            $rows = array_map(function ($r) {
                $r['label']   = $r['label'] ?? null; // YouTube tidak pakai label mayoritas per video, biarkan null
                $r['snippet'] = $r['snippet'] ?? null;
                return $r;
            }, $rows);
        }

        $total = count($rows);
        $offset = max(0, ($page - 1) * $perPage);
        $items = array_slice($rows, $offset, $perPage);

        return response()->json([
            'success'   => true,
            'source'    => $src,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'items'     => array_values($items),
        ]);
    }

    /* =============== Count untuk Template Cepat =============== */
    public function count(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'sometimes|string|in:monthly,yearly',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'source'      => 'required|in:news,youtube',
        ]);

        $startLocal = Carbon::parse($validated['start_date'])->startOfDay();
        $endLocal   = Carbon::parse($validated['end_date'])->endOfDay();

        if ($validated['source'] === 'news') {
            $count = $this->countNewsArticles($startLocal, $endLocal);
        } else {
            $count = $this->countYoutubeVideos($startLocal, $endLocal);
        }

        return response()->json([
            'success'     => true,
            'items_count' => (int) $count,
            'start_date'  => $startLocal->toDateString(),
            'end_date'    => $endLocal->toDateString(),
        ]);
    }

    public function charts(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'source'     => 'required|in:news,youtube',
        ]);

        $start  = Carbon::parse($validated['start_date'])->startOfDay();
        $end    = Carbon::parse($validated['end_date'])->endOfDay();
        $source = $validated['source'];

        // batas aman: maksimal 12 bulan
        if ($start->diffInMonths($end) > 12) {
            return response()->json([
                'success' => false,
                'message' => 'Rentang maksimal 12 bulan untuk grafik.',
            ], 422);
        }

        try {
            if ($source === 'news') {
                // === NEWS ===
                $trend = $this->buildNewsMonthlyTrend($start, $end);

                $sumPos = array_sum($trend['positive']);
                $sumNeu = array_sum($trend['neutral']);
                $sumNeg = array_sum($trend['negative']);
                $summary = [
                    'news' => [
                        'positive' => $sumPos,
                        'neutral'  => $sumNeu,
                        'negative' => $sumNeg,
                        'total'    => $sumPos + $sumNeu + $sumNeg,
                    ],
                ];
            } else {
                // === YOUTUBE ===
                // gunakan tren bulanan yang sudah kamu buat agar tidak selalu 0
                $trend = $this->buildYoutubeMonthlyTrend($start, $end);

                // summary total pakai aggregateYoutube agar konsisten dengan laporan
                $ag = $this->aggregateYoutube($start, $end) ?? [];
                $summary = [
                    'youtube' => [
                        'positive' => (int)($ag['positive'] ?? 0),
                        'neutral'  => (int)($ag['neutral']  ?? 0),
                        'negative' => (int)($ag['negative'] ?? 0),
                        'total'    => (int)($ag['total']    ?? 0),
                    ]
                ];
            }

            return response()->json([
                'success' => true,
                'trend'   => $trend,
                'summary' => $summary,
            ]);
        } catch (\Throwable $e) {
            Log::error('charts.data:error', [
                'msg'   => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'src'   => $source, // tambahan: bantu identifikasi cabang yang error
                'start' => $start->toDateString(),
                'end'   => $end->toDateString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data grafik.',
            ], 500);
        }
    }


    /* =============== Generate Chart URLs =============== */

    /**
     * Generate chart URLs untuk laporan (Bar Chart + Pie Chart per bulan)
     */
    /**
     * Generate chart URLs untuk laporan (Bar Chart + Pie Chart per bulan)
     * - Menggunakan QuickChart PNG + plugin datalabels agar persentase tampil
     * - devicePixelRatio=2 untuk hasil tajam di PDF
     */
    private function generateChartUrls(array $summary, Carbon $start, Carbon $end, string $source): array
    {
        $src = $source === 'news' ? 'news' : 'youtube';

        // Data summary keseluruhan
        $data  = $summary[$src] ?? ['positive' => 0, 'neutral' => 0, 'negative' => 0];
        $pos   = (int) ($data['positive'] ?? 0);
        $neu   = (int) ($data['neutral']  ?? 0);
        $neg   = (int) ($data['negative'] ?? 0);
        $total = $pos + $neu + $neg;

        // Persentase
        $posPct = $total > 0 ? round(($pos / $total) * 100, 1) : 0;
        $neuPct = $total > 0 ? round(($neu / $total) * 100, 1) : 0;
        $negPct = $total > 0 ? round(($neg / $total) * 100, 1) : 0;

        // Data per bulan (pakai helper yang sama seperti dashboard)
        $monthlyData = $this->getMonthlyTrendForReport($start, $end, $source);

        // 1) BAR CHART — Trend Bulanan
        $barConfig = [
            'type' => 'bar',
            'data' => [
                'labels'   => $monthlyData['labels'],
                'datasets' => [
                    [
                        'label' => 'Positif',
                        'data'  => $monthlyData['positive'],
                        'backgroundColor' => '#10b981',
                    ],
                    [
                        'label' => 'Netral',
                        'data'  => $monthlyData['neutral'],
                        'backgroundColor' => '#f59e0b',
                    ],
                    [
                        'label' => 'Negatif',
                        'data'  => $monthlyData['negative'],
                        'backgroundColor' => '#ef4444',
                    ],
                ],
            ],
            'options' => [
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text'    => 'Trend Sentimen per Bulan (' . ($src === 'news' ? 'Artikel' : 'YouTube') . ')',
                        'font'    => ['size' => 16],
                    ],
                    'legend' => ['position' => 'top'],
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'ticks' => ['precision' => 0],
                    ],
                ],
            ],
        ];

        // 2) PIE CHART — Distribusi Keseluruhan (dengan datalabels persentase)
        $pieConfig = [
            'type' => 'doughnut',
            'data' => [
                'labels'   => ['Positif', 'Netral', 'Negatif'],
                'datasets' => [[
                    'data'            => [$pos, $neu, $neg],
                    'backgroundColor' => ['#10b981', '#f59e0b', '#ef4444'],
                ]],
            ],
            'options' => [
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text'    => 'Distribusi Sentimen Keseluruhan',
                        'font'    => ['size' => 16],
                    ],
                    'legend' => ['position' => 'bottom'],
                    'datalabels' => [
                        'color' => '#fff',
                        'font'  => ['weight' => 'bold', 'size' => 14],
                        'formatter' => "(value, ctx) => {
                        const total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                        if (!total) return '';
                        const pct = Math.round((value/total)*100);
                        return pct + '%';
                    }",
                    ],
                ],
            ],
        ];

        // Helper pembuat URL QuickChart
        $buildChartUrl = function (array $cfg, int $w, int $h) {
            $params = http_build_query([
                'bkg'              => 'white',
                'c'                => json_encode($cfg),
                'w'                => $w,
                'h'                => $h,
                'format'           => 'png',
                'devicePixelRatio' => 2,
                'plugins'          => 'datalabels', // penting untuk menampilkan persentase
            ]);

            return 'https://quickchart.io/chart?' . $params;
        };

        $barUrl = $buildChartUrl($barConfig, 700, 350);
        $pieUrl = $buildChartUrl($pieConfig, 500, 350);

        return [
            'bar'     => $barUrl,
            'pie'     => $pieUrl,
            'monthly' => $monthlyData,
            'summary' => [
                'positive' => $pos,
                'neutral'  => $neu,
                'negative' => $neg,
                'total'    => $total,
                'pos_pct'  => $posPct,
                'neu_pct'  => $neuPct,
                'neg_pct'  => $negPct,
            ],
        ];
    }

    private function buildMonthlyCharts(array $summary, array $daily, string $source): array
    {
        $src = $source === 'news' ? 'news' : 'youtube';
        $sum = $summary[$src] ?? ['positive' => 0, 'neutral' => 0, 'negative' => 0];
        $pos = (int)($sum['positive'] ?? 0);
        $neu = (int)($sum['neutral'] ?? 0);
        $neg = (int)($sum['negative'] ?? 0);

        $barConfig = [
            'type' => 'bar',
            'data' => [
                'labels'   => $daily['labels'],
                'datasets' => [
                    ['label' => 'Positif', 'data' => $daily['positive'], 'backgroundColor' => '#10b981'],
                    ['label' => 'Netral', 'data' => $daily['neutral'], 'backgroundColor' => '#f59e0b'],
                    ['label' => 'Negatif', 'data' => $daily['negative'], 'backgroundColor' => '#ef4444'],
                ],
            ],
            'options' => [
                'plugins' => [
                    'title'  => ['display' => true, 'text' => 'Trend Harian (bulan terpilih)', 'font' => ['size' => 16]],
                    'legend' => ['position' => 'top'],
                ],
                'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
            ],
        ];

        $pieConfig = [
            'type' => 'doughnut',
            'data' => [
                'labels'   => ['Positif', 'Netral', 'Negatif'],
                'datasets' => [[
                    'data'            => [$pos, $neu, $neg],
                    'backgroundColor' => ['#10b981', '#f59e0b', '#ef4444'],
                ]],
            ],
            'options' => [
                'plugins' => [
                    'title'  => ['display' => true, 'text' => 'Distribusi Sentimen (1 Bulan)', 'font' => ['size' => 16]],
                    'legend' => ['position' => 'bottom'],
                ],
            ],
        ];

        $build = function (array $cfg, int $w, int $h) {
            $params = http_build_query([
                'bkg' => 'white',
                'c'   => json_encode($cfg),
                'w'   => $w,
                'h'   => $h,
                'format' => 'png',
                'devicePixelRatio' => 2,
            ]);
            return 'https://quickchart.io/chart?' . $params;
        };

        return [
            'bar' => $build($barConfig, 700, 350),
            'pie' => $build($pieConfig, 500, 320),
        ];
    }

    /**
     * Ambil data trend bulanan untuk periode tertentu
     */
    private function getMonthlyTrendForReport(Carbon $start, Carbon $end, string $source): array
    {
        // Generate daftar bulan dalam rentang
        $months = [];
        $current = $start->copy()->startOfMonth();

        while ($current->lte($end)) {
            $months[] = $current->format('Y-m');
            $current->addMonth();
        }

        if ($source === 'news') {
            $c = $this->newsCfg;

            // Hitung sentimen mayoritas per artikel per bulan
            $rows = DB::table($c['sents_table'] . ' as s')
                ->join($c['articles_table'] . ' as a', "a.{$c['article_id']}", '=', "s.{$c['sent_fk']}")
                ->whereBetween("a.{$c['article_date']}", [$start, $end])
                ->whereNotNull("s.{$c['sent_label']}")
                ->selectRaw("
                    DATE_FORMAT(a.{$c['article_date']}, '%Y-%m') as ym,
                    a.{$c['article_id']} as aid,
                    SUM(CASE WHEN LOWER(s.{$c['sent_label']}) IN ('positive','positif') THEN 1 ELSE 0 END) as pos,
                    SUM(CASE WHEN LOWER(s.{$c['sent_label']}) IN ('neutral','netral') THEN 1 ELSE 0 END) as neu,
                    SUM(CASE WHEN LOWER(s.{$c['sent_label']}) IN ('negative','negatif') THEN 1 ELSE 0 END) as neg
                ")
                ->groupBy('ym', 'aid')
                ->get();

            // Hitung mayoritas per artikel
            $map = [];
            foreach ($rows as $r) {
                $label = ($r->pos > $r->neu && $r->pos > $r->neg) ? 'positive'
                    : (($r->neg > $r->pos && $r->neg > $r->neu) ? 'negative' : 'neutral');

                if (!isset($map[$r->ym][$label])) $map[$r->ym][$label] = 0;
                $map[$r->ym][$label]++;
            }
        } else {
            // YouTube: label terbaru per komentar
            $sentTable = 'comment_sentiments';
            $rows = DB::table("$sentTable as cs")
                ->join(
                    DB::raw("(SELECT comment_fk, MAX(analyzed_at) max_at FROM $sentTable GROUP BY comment_fk) latest"),
                    fn ($j) => $j->on('cs.comment_fk', '=', 'latest.comment_fk')
                        ->on('cs.analyzed_at', '=', 'latest.max_at')
                )
                ->join('youtube_comments as yc', 'yc.id', '=', 'cs.comment_fk')
                ->whereBetween('yc.published_at', [$start, $end])
                ->selectRaw("
                    DATE_FORMAT(yc.published_at, '%Y-%m') as ym,
                    LOWER(cs.label) as label,
                    COUNT(*) as n
                ")
                ->groupBy('ym', 'label')
                ->get();

            $map = [];
            foreach ($rows as $r) {
                $lbl = in_array($r->label, ['positive', 'positif']) ? 'positive'
                    : (in_array($r->label, ['negative', 'negatif']) ? 'negative' : 'neutral');
                $map[$r->ym][$lbl] = (int) $r->n;
            }
        }

        // Format labels untuk chart
        $labels = array_map(function ($ym) {
            [$y, $m] = explode('-', $ym);
            $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return $monthNames[(int)$m - 1] . ' ' . $y;
        }, $months);

        return [
            'labels' => $labels,
            'positive' => array_map(fn ($m) => $map[$m]['positive'] ?? 0, $months),
            'neutral' => array_map(fn ($m) => $map[$m]['neutral'] ?? 0, $months),
            'negative' => array_map(fn ($m) => $map[$m]['negative'] ?? 0, $months),
            'months_raw' => $months,
        ];
    }

    private function getDailyTrendForMonth(Carbon $start, Carbon $end, string $source): array
    {
        // susun daftar tanggal dalam bulan
        $days = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $days[] = $cursor->format('Y-m-d');
            $cursor->addDay();
        }

        if ($source === 'news') {
            $c = $this->newsCfg;
            $rows = DB::table($c['sents_table'] . ' as s')
                ->join($c['articles_table'] . ' as a', "a.{$c['article_id']}", '=', "s.{$c['sent_fk']}")
                ->whereBetween("a.{$c['article_date']}", [$start, $end])
                ->whereNotNull("s.{$c['sent_label']}")
                ->selectRaw("
                DATE(a.{$c['article_date']}) as d,
                SUM(CASE WHEN LOWER(s.{$c['sent_label']}) IN ('positive','positif') THEN 1 ELSE 0 END) as pos,
                SUM(CASE WHEN LOWER(s.{$c['sent_label']}) IN ('neutral','netral') THEN 1 ELSE 0 END) as neu,
                SUM(CASE WHEN LOWER(s.{$c['sent_label']}) IN ('negative','negatif') THEN 1 ELSE 0 END) as neg
            ")
                ->groupBy('d')
                ->get();
            $map = [];
            foreach ($rows as $r) {
                $map[$r->d] = ['pos' => (int)$r->pos, 'neu' => (int)$r->neu, 'neg' => (int)$r->neg];
            }
        } else {
            // YouTube: label terbaru per komentar per hari
            $sentTable = Schema::hasTable('comment_sentiments') ? 'comment_sentiments' : 'youtube_comment_sentiments';
            $labelCol  = $this->firstExistingColumn($sentTable, ['label', 'sentiment_label', 'sentiment']);
            $fkCol     = $this->firstExistingColumn($sentTable, ['comment_fk', 'comment_id']);
            $timeCol   = $this->firstExistingColumn($sentTable, ['analyzed_at', 'created_at']);
            $latest = $this->latestLabeledSubquery($sentTable, $fkCol, $timeCol);
            $dateExpr = $this->youtubeLocalDateSql();

            $rows = DB::table('youtube_comments as yc')
                ->joinSub($latest, 'lt', fn ($j) => $j->on('yc.id', '=', 'lt.ck'))
                ->join("$sentTable as cs", function ($j) use ($fkCol, $timeCol, $labelCol) {
                    $j->on("cs.$fkCol", '=', 'lt.ck')->on("cs.$timeCol", '=', 'lt.max_at')->whereNotNull("cs.$labelCol");
                })
                ->whereBetween(DB::raw($dateExpr), [$start->toDateString(), $end->toDateString()])
                ->selectRaw("
                $dateExpr as d,
                SUM(CASE WHEN LOWER(cs.$labelCol) IN ('positive','positif') THEN 1 ELSE 0 END) as pos,
                SUM(CASE WHEN LOWER(cs.$labelCol) IN ('neutral','netral') THEN 1 ELSE 0 END) as neu,
                SUM(CASE WHEN LOWER(cs.$labelCol) IN ('negative','negatif') THEN 1 ELSE 0 END) as neg
            ")
                ->groupBy('d')
                ->get();
            $map = [];
            foreach ($rows as $r) {
                $map[$r->d] = ['pos' => (int)$r->pos, 'neu' => (int)$r->neu, 'neg' => (int)$r->neg];
            }
        }

        $labels = array_map(fn ($d) => Carbon::parse($d)->format('d M'), $days);
        return [
            'labels'   => $labels,
            'positive' => array_map(fn ($d) => $map[$d]['pos'] ?? 0, $days),
            'neutral'  => array_map(fn ($d) => $map[$d]['neu'] ?? 0, $days),
            'negative' => array_map(fn ($d) => $map[$d]['neg'] ?? 0, $days),
            'days_raw' => $days,
        ];
    }

    /* =============== Helpers NEWS =============== */

    private function getNewsDetailsByMajority(Carbon $start, Carbon $end, int $limit = 300): array
    {
        if (!Schema::hasTable('news_articles') || !Schema::hasTable('news_article_sentiments')) {
            Log::warning('Tables not found for news details');
            return [];
        }

        try {
            // TAMBAHKAN summary_clean dan summary_raw di SELECT dan GROUP BY
            $rawResults = DB::table('news_articles as a')
                ->leftJoin('news_article_sentiments as s', 'a.id', '=', 's.article_fk')
                ->whereBetween('s.analyzed_at', [$start, $end])
                ->groupBy('a.id', 'a.title', 'a.url', 'a.published_at', 'a.created_at', 'a.summary_clean', 'a.summary_raw') // ← TAMBAHAN
                ->orderBy('a.published_at', 'desc')
                ->selectRaw("
                    a.id,
                    a.title,
                    a.url,
                    a.published_at as date,
                    a.summary_clean,
                    a.summary_raw,
                    COUNT(s.id) as sentimen_count,
                    SUM(CASE WHEN LOWER(s.label) = 'positive' THEN 1 ELSE 0 END) as pos,
                    SUM(CASE WHEN LOWER(s.label) = 'neutral' THEN 1 ELSE 0 END) as neu,
                    SUM(CASE WHEN LOWER(s.label) = 'negative' THEN 1 ELSE 0 END) as neg
                ")
                ->havingRaw('sentimen_count > 0')
                ->limit($limit)
                ->get();

            Log::info('getNewsDetailsByMajority executed', [
                'count' => $rawResults->count(),
                'period' => $start->format('Y-m-d') . ' to ' . $end->format('Y-m-d'),
                'with_snippet' => $rawResults->filter(fn ($r) => !empty($r->summary_clean))->count()
            ]);

            // Transform ke format standard
            $out = [];
            foreach ($rawResults as $row) {
                $pos = (int)$row->pos;
                $neu = (int)$row->neu;
                $neg = (int)$row->neg;
                $total = $pos + $neu + $neg;

                // Tentukan label mayoritas
                if ($pos > $neu && $pos > $neg) {
                    $label = 'positive';
                } elseif ($neg > $neu && $neg > $pos) {
                    $label = 'negative';
                } else {
                    $label = 'neutral';
                }

                // AMBIL SNIPPET: prioritas summary_clean > summary_raw
                $snippet = null;
                if (!empty($row->summary_clean)) {
                    $snippet = $row->summary_clean;
                } elseif (!empty($row->summary_raw)) {
                    $snippet = $row->summary_raw;
                }

                $out[] = [
                    'id' => $row->id,
                    'date' => (string)$row->date,
                    'title' => $row->title ?: 'Tanpa Judul',
                    'url' => $row->url ?: null,
                    'pos' => $pos,
                    'neu' => $neu,
                    'neg' => $neg,
                    'total' => $total,
                    'label' => $label,
                    'snippet' => $snippet, // ← PENTING!
                ];
            }

            return $out;
        } catch (\Exception $e) {
            Log::error('Error in getNewsDetailsByMajority', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    private function countNewsArticles(Carbon $start, Carbon $end): int
    {
        $c = $this->newsCfg;
        if (!Schema::hasTable($c['articles_table']) || !Schema::hasTable($c['sents_table'])) return 0;

        $labelCol = $c['sent_label'];
        $fkCol    = $c['sent_fk'];
        $dateCol  = $c['article_date'];

        return (int) DB::table($c['sents_table'] . ' as s')
            ->join($c['articles_table'] . ' as a', "a.{$c['article_id']}", '=', "s.$fkCol")
            ->whereBetween("s.analyzed_at", [$start, $end])
            ->whereNotNull("s.$labelCol")
            ->distinct()
            ->count("a.{$c['article_id']}");
    }

    private function buildNewsMonthlyTrend(Carbon $start, Carbon $end): array
    {
        // Ambil agregat per-bulan
        $rows = DB::table('news_articles as a')
            ->leftJoin('news_article_sentiments as s', 'a.id', '=', 's.article_fk')
            ->whereBetween('s.analyzed_at', [$start, $end])
            ->selectRaw("
            DATE_FORMAT(a.published_at, '%Y-%m') as month_key,
            SUM(CASE WHEN LOWER(s.label)='positive' THEN 1 ELSE 0 END) as pos,
            SUM(CASE WHEN LOWER(s.label)='neutral'  THEN 1 ELSE 0 END) as neu,
            SUM(CASE WHEN LOWER(s.label)='negative' THEN 1 ELSE 0 END) as neg
        ")
            ->groupBy('month_key')
            ->get();

        // Susun map ['Y-m' => ['pos'=>..,'neu'=>..,'neg'=>..]]
        $map = [];
        foreach ($rows as $r) {
            $map[$r->month_key] = [
                'pos' => (int) $r->pos,
                'neu' => (int) $r->neu,
                'neg' => (int) $r->neg,
            ];
        }

        // Pakai helper untuk isi bucket lengkap 0..0
        return $this->fillTrendFromMap($start, $end, $map);
    }

    private function emptyMonthlyTrend(Carbon $start, Carbon $end): array
    {
        $labels    = [];
        $labelsKey = [];
        $pos = $neu = $neg = [];

        $cursor = $start->copy()->startOfMonth();
        $limit  = $end->copy()->endOfMonth();

        while ($cursor->lte($limit)) {
            $labels[]    = $cursor->translatedFormat('M Y'); // "Jun 2025" (pakai locale)
            $labelsKey[] = $cursor->format('Y-m');           // "2025-06"
            $pos[] = 0;
            $neu[] = 0;
            $neg[] = 0;
            $cursor->addMonth();
        }

        return [
            'labels'      => $labels,
            'labels_key'  => $labelsKey, // hanya untuk internal mapping; nanti di-unset
            'positive'    => $pos,
            'neutral'     => $neu,
            'negative'    => $neg,
        ];
    }

    /**
     * (Opsional) Kalau kamu punya map agregat monthly, gunakan ini untuk mengisi tren.
     * $map contoh: ['2025-01'=>['pos'=>3,'neu'=>2,'neg'=>1], ...]
     */
    private function fillTrendFromMap(Carbon $start, Carbon $end, array $map): array
    {
        $trend = $this->emptyMonthlyTrend($start, $end);
        $idx = array_flip($trend['labels_key']);
        foreach ($map as $ym => $v) {
            if (!isset($idx[$ym])) continue;
            $i = $idx[$ym];
            $trend['positive'][$i] = (int) ($v['pos'] ?? 0);
            $trend['neutral'][$i]  = (int) ($v['neu'] ?? 0);
            $trend['negative'][$i] = (int) ($v['neg'] ?? 0);
        }
        unset($trend['labels_key']);
        return $trend;
    }

    /**
     * Agregasi sentimen berita berbasis mayoritas per artikel.
     * Setiap artikel dihitung 1 suara ke label yang jumlahnya paling besar.
     * Return: ['positive' => int, 'neutral' => int, 'negative' => int, 'total' => int]
     */
    private function aggregateNewsByMajority(Carbon $start, Carbon $end): array
    {
        // Hitung jumlah label per ARTIKEL di periode
        $rows = DB::table('news_articles as a')
            ->leftJoin('news_article_sentiments as s', 'a.id', '=', 's.article_fk')
            ->whereBetween('s.analyzed_at', [$start, $end])
            ->groupBy('a.id')
            ->selectRaw("
            a.id,
            SUM(CASE WHEN LOWER(s.label)='positive' THEN 1 ELSE 0 END) AS pos,
            SUM(CASE WHEN LOWER(s.label)='neutral'  THEN 1 ELSE 0 END) AS neu,
            SUM(CASE WHEN LOWER(s.label)='negative' THEN 1 ELSE 0 END) AS neg
        ")
            ->get();

        $posMaj = 0;
        $neuMaj = 0;
        $negMaj = 0;

        foreach ($rows as $r) {
            $p = (int)$r->pos;
            $n = (int)$r->neu;
            $g = (int)$r->neg;

            // Lewati artikel tanpa sentiment sama sekali
            if (($p + $n + $g) === 0) {
                continue;
            }

            // Mayoritas: jika seri, kita pilih NETRAL kecuali ada yang lebih besar
            if ($p >= $n && $p >= $g && $p > 0) {
                $posMaj++;
            } elseif ($g >= $n && $g >= $p && $g > 0) {
                $negMaj++;
            } else {
                $neuMaj++;
            }
        }

        return [
            'positive' => $posMaj,
            'neutral'  => $neuMaj,
            'negative' => $negMaj,
            // total artikel yang punya minimal 1 sentiment (bukan total baris sentiment)
            'total'    => $posMaj + $neuMaj + $negMaj,
        ];
    }

    /* =============== Helpers YOUTUBE =============== */

    private function youtubeLocalDateSql(): string
    {
        return "DATE(CASE WHEN yc.published_at IS NOT NULL
                     THEN CONVERT_TZ(yc.published_at, '+00:00', '+07:00')
                     ELSE yc.created_at END)";
    }

    private function latestLabeledSubquery(string $table, string $fkCol, string $timeCol): \Illuminate\Database\Query\Builder
    {
        return DB::table($table)
            ->select($fkCol . ' as ck', DB::raw("MAX($timeCol) as max_at"))
            ->groupBy($fkCol);
    }

    private function countYoutubeVideos(Carbon $startLocal, Carbon $endLocal): int
    {
        if (!Schema::hasTable('youtube_videos') || !Schema::hasTable('youtube_comments')) return 0;

        $sentTable = Schema::hasTable('comment_sentiments') ? 'comment_sentiments'
            : (Schema::hasTable('youtube_comment_sentiments') ? 'youtube_comment_sentiments' : null);
        if (!$sentTable) return 0;

        $labelCol = $this->firstExistingColumn($sentTable, ['label', 'sentiment_label', 'sentiment']);
        $fkCol    = $this->firstExistingColumn($sentTable, ['comment_fk', 'comment_id']);
        $timeCol  = $this->firstExistingColumn($sentTable, ['analyzed_at', 'created_at']);
        if (!$labelCol || !$fkCol || !$timeCol) return 0;

        $latest = $this->latestLabeledSubquery($sentTable, $fkCol, $timeCol);
        $localDateExpr = $this->youtubeLocalDateSql();

        return (int) DB::table('youtube_comments as yc')
            ->joinSub($latest, 'lt', fn ($j) => $j->on('yc.id', '=', 'lt.ck'))
            ->join("$sentTable as cs", function ($j) use ($fkCol, $timeCol, $labelCol) {
                $j->on("cs.$fkCol", '=', 'lt.ck')
                    ->on("cs.$timeCol", '=', 'lt.max_at')
                    ->whereNotNull("cs.$labelCol");
            })
            ->join('youtube_videos as v', 'v.id', '=', 'yc.video_fk')
            ->whereBetween(DB::raw($localDateExpr), [$startLocal->toDateString(), $endLocal->toDateString()])
            ->distinct()
            ->count('v.id');
    }

    private function getYoutubeVideoSummary(Carbon $startLocal, Carbon $endLocal, int $limit = 300): array
    {
        if (!Schema::hasTable('youtube_videos') || !Schema::hasTable('youtube_comments')) return [];

        $sentTable = Schema::hasTable('comment_sentiments') ? 'comment_sentiments'
            : (Schema::hasTable('youtube_comment_sentiments') ? 'youtube_comment_sentiments' : null);
        if (!$sentTable) return [];

        $labelCol = $this->firstExistingColumn($sentTable, ['label', 'sentiment_label', 'sentiment']);
        $fkCol    = $this->firstExistingColumn($sentTable, ['comment_fk', 'comment_id']);
        $timeCol  = $this->firstExistingColumn($sentTable, ['analyzed_at', 'created_at']);
        if (!$labelCol || !$fkCol || !$timeCol) return [];

        $latest = $this->latestLabeledSubquery($sentTable, $fkCol, $timeCol);
        $localDateExpr = $this->youtubeLocalDateSql();

        $rows = DB::table('youtube_comments as yc')
            ->joinSub($latest, 'lt', fn ($j) => $j->on('yc.id', '=', 'lt.ck'))
            ->join("$sentTable as cs", function ($j) use ($fkCol, $timeCol, $labelCol) {
                $j->on("cs.$fkCol", '=', 'lt.ck')
                    ->on("cs.$timeCol", '=', 'lt.max_at')
                    ->whereNotNull("cs.$labelCol");
            })
            ->join('youtube_videos as v', 'v.id', '=', 'yc.video_fk')
            ->whereBetween(DB::raw($localDateExpr), [$startLocal->toDateString(), $endLocal->toDateString()])
            ->groupBy('v.id', 'v.published_at', 'v.title', 'v.url', 'v.video_id')
            ->orderBy('v.published_at', 'desc')
            ->selectRaw("
                v.id as video_id_pk,
                v.published_at as date,
                v.title,
                v.url,
                v.video_id,
                SUM(CASE WHEN LOWER(cs.$labelCol) IN ('positive','positif') THEN 1 ELSE 0 END) as pos,
                SUM(CASE WHEN LOWER(cs.$labelCol) IN ('neutral','netral')  THEN 1 ELSE 0 END) as neu,
                SUM(CASE WHEN LOWER(cs.$labelCol) IN ('negative','negatif') THEN 1 ELSE 0 END) as neg,
                COUNT(*) as total_comments
            ")
            ->limit($limit)
            ->get()
            ->map(function ($r) use ($fkCol) {
                $url = $r->url ?: (!empty($r->video_id) ? 'https://www.youtube.com/watch?v=' . $r->video_id : null);

                $pos = (int) $r->pos;
                $neu = (int) $r->neu;
                $neg = (int) $r->neg;

                // Tentukan mayoritas
                $label = ($pos > $neu && $pos > $neg) ? 'positive'
                    : (($neg > $pos && $neg > $neu) ? 'negative' : 'neutral');

                // AMBIL 1 SAMPLE KOMENTAR DARI VIDEO INI
                $commentText = null;
                try {
                    // Gunakan comment_text_clean jika ada, fallback ke comment_text
                    $sampleComment = DB::table('youtube_comments')
                        ->where('video_fk', $r->video_id_pk)
                        ->where(function ($q) {
                            $q->whereNotNull('comment_text_clean')
                                ->orWhereNotNull('comment_text');
                        })
                        ->orderBy('published_at', 'desc')
                        ->select(
                            DB::raw('COALESCE(comment_text_clean, comment_text) as text')
                        )
                        ->first();

                    if ($sampleComment && !empty($sampleComment->text)) {
                        $commentText = $sampleComment->text;
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to fetch comment for video', [
                        'video_id' => $r->video_id_pk,
                        'error' => $e->getMessage()
                    ]);
                }

                return [
                    'date'   => (string) $r->date,
                    'title'  => (string) ($r->title ?? '(tanpa judul)'),
                    'url'    => $url,
                    'video_id' => $r->video_id,
                    'pos'    => $pos,
                    'neu'    => $neu,
                    'neg'    => $neg,
                    'total'  => (int) $r->total_comments,
                    'label'  => $label,
                    'snippet' => null,
                    'comment_text' => $commentText, // ← PENTING!
                ];
            })
            ->toArray();

        Log::info('getYoutubeVideoSummary executed', [
            'count' => count($rows),
            'with_comment' => collect($rows)->filter(fn ($r) => !empty($r['comment_text']))->count()
        ]);

        return $rows;
    }



    private function aggregateYoutube(Carbon $startLocal, Carbon $endLocal): array
    {
        if (!Schema::hasTable('youtube_comments')) {
            return ['source' => 'youtube', 'positive' => 0, 'neutral' => 0, 'negative' => 0, 'total' => 0];
        }

        $table = Schema::hasTable('comment_sentiments') ? 'comment_sentiments'
            : (Schema::hasTable('youtube_comment_sentiments') ? 'youtube_comment_sentiments' : null);
        if (!$table) {
            return ['source' => 'youtube', 'positive' => 0, 'neutral' => 0, 'negative' => 0, 'total' => 0];
        }

        $labelCol = $this->firstExistingColumn($table, ['label', 'sentiment_label', 'sentiment']);
        $fkCol    = $this->firstExistingColumn($table, ['comment_fk', 'comment_id']);
        $timeCol  = $this->firstExistingColumn($table, ['analyzed_at', 'created_at']);
        if (!$labelCol || !$fkCol || !$timeCol) {
            return ['source' => 'youtube', 'positive' => 0, 'neutral' => 0, 'negative' => 0, 'total' => 0];
        }

        $latest = $this->latestLabeledSubquery($table, $fkCol, $timeCol);
        $localDateExpr = $this->youtubeLocalDateSql();

        $rows = DB::table("$table as cs")
            ->joinSub($latest, 'lt', function ($j) use ($fkCol, $timeCol) {
                $j->on("cs.$fkCol", '=', 'lt.ck')->on("cs.$timeCol", '=', 'lt.max_at');
            })
            ->join('youtube_comments as yc', "yc.id", '=', "cs.$fkCol")
            ->whereBetween(DB::raw($localDateExpr), [$startLocal->toDateString(), $endLocal->toDateString()])
            ->whereNotNull("cs.$labelCol")
            ->select(DB::raw("LOWER(cs.$labelCol) as label"), DB::raw('COUNT(*) as n'))
            ->groupBy('label')
            ->pluck('n', 'label');

        $pos = (int)($rows['positive'] ?? $rows['positif'] ?? 0);
        $neu = (int)($rows['neutral']  ?? $rows['netral']  ?? 0);
        $neg = (int)($rows['negative'] ?? $rows['negatif'] ?? 0);

        return ['source' => 'youtube', 'positive' => $pos, 'neutral' => $neu, 'negative' => $neg, 'total' => $pos + $neu + $neg];
    }

    private function buildYoutubeMonthlyTrend(Carbon $start, Carbon $end): array
    {
        // siapkan kerangka kosong 0..0 dengan label bulan
        $trend = $this->emptyMonthlyTrend($start, $end);
        $idx   = array_flip($trend['labels_key']); // map "Y-m" -> index

        // Pastikan tabel tersedia
        if (!Schema::hasTable('youtube_comments')) {
            unset($trend['labels_key']);
            return $trend;
        }

        // Tentukan tabel & kolom sentiment yang tersedia (fleksibel)
        $table = Schema::hasTable('comment_sentiments') ? 'comment_sentiments'
            : (Schema::hasTable('youtube_comment_sentiments') ? 'youtube_comment_sentiments' : null);

        if (!$table) {
            unset($trend['labels_key']);
            return $trend;
        }

        $labelCol = $this->firstExistingColumn($table, ['label', 'sentiment_label', 'sentiment']);
        $fkCol    = $this->firstExistingColumn($table, ['comment_fk', 'comment_id']);
        $timeCol  = $this->firstExistingColumn($table, ['analyzed_at', 'created_at']);
        if (!$labelCol || !$fkCol || !$timeCol) {
            unset($trend['labels_key']);
            return $trend;
        }

        // Ambil label TERBARU per komentar
        $latest = $this->latestLabeledSubquery($table, $fkCol, $timeCol);

        // Ekspresi tanggal lokal yang sama dengan aggregateYoutube()
        $localDateExpr = $this->youtubeLocalDateSql(); // menghasilkan DATE kolom lokal

        // Agregat per bulan
        $rows = DB::table("$table as cs")
            ->joinSub($latest, 'lt', function ($j) use ($fkCol, $timeCol) {
                $j->on("cs.$fkCol", '=', 'lt.ck')->on("cs.$timeCol", '=', 'lt.max_at');
            })
            ->join('youtube_comments as yc', "yc.id", '=', "cs.$fkCol")
            ->whereBetween(DB::raw($localDateExpr), [
                $start->toDateString(),
                $end->toDateString()
            ])
            ->whereNotNull("cs.$labelCol")
            ->selectRaw("
            DATE_FORMAT($localDateExpr, '%Y-%m') as ym,
            SUM(CASE WHEN LOWER(cs.$labelCol) IN ('positive','positif') THEN 1 ELSE 0 END) as pos,
            SUM(CASE WHEN LOWER(cs.$labelCol) IN ('neutral','netral')  THEN 1 ELSE 0 END) as neu,
            SUM(CASE WHEN LOWER(cs.$labelCol) IN ('negative','negatif') THEN 1 ELSE 0 END) as neg
        ")
            ->groupBy('ym')
            ->get();

        foreach ($rows as $r) {
            if (!isset($idx[$r->ym])) continue;
            $i = $idx[$r->ym];
            $trend['positive'][$i] = (int) $r->pos;
            $trend['neutral'][$i]  = (int) $r->neu;
            $trend['negative'][$i] = (int) $r->neg;
        }

        unset($trend['labels_key']);
        return $trend;
    }

    /* =============== Utils =============== */

    private function firstExistingColumn(string $table, array $candidates): ?string
    {
        foreach ($candidates as $c) if ($c && Schema::hasColumn($table, $c)) return $c;
        return null;
    }

    private function makePdf(
        string $filename,
        array $summary,
        \Carbon\Carbon $start,
        \Carbon\Carbon $end,
        string $rtype,
        string $source,
        iterable $details,
        array $chartUrls,
        array $chartImg,
        ?string $reportName = null,
        string $pdfType = 'table' // NEW: parameter untuk membedakan tipe PDF
    ): void {
        // Konversi Collection ke array jika perlu
        $detailsArray = $details instanceof \Illuminate\Support\Collection
            ? $details->toArray()
            : (is_array($details) ? $details : iterator_to_array($details));

        // Batasi hanya 300 item untuk performa PDF
        $detailsArray = array_slice($detailsArray, 0, 300);

        // Pastikan data summary tersedia
        $summaryData = $source === 'news'
            ? ($summary['news'] ?? ['positive' => 0, 'neutral' => 0, 'negative' => 0, 'total' => 0])
            : ($summary['youtube'] ?? ['positive' => 0, 'neutral' => 0, 'negative' => 0, 'total' => 0]);

        // Data untuk view
        $viewData = [
            'report_name'  => $reportName ?: 'Laporan Sentimen',
            'source'       => $source,
            'start'        => $start,
            'end'          => $end,
            'rtype'        => $rtype,
            'generated_at' => now(),

            // Summary data
            'summary'      => $summary,
            'summaryData'  => $summaryData,

            // Detail items (unified naming)
            'items'        => $detailsArray,
            'details'      => $detailsArray,

            // Chart data
            'chartUrls'    => $chartUrls,
            'chartImg'     => $chartImg,

            // Statistics
            'total_items'  => count($detailsArray),
            'pos_count'    => $summaryData['positive'] ?? 0,
            'neu_count'    => $summaryData['neutral'] ?? 0,
            'neg_count'    => $summaryData['negative'] ?? 0,
            'total_count'  => $summaryData['total'] ?? 0,
        ];

        // PERBAIKAN: Tentukan view berdasarkan tipe PDF
        if ($pdfType === 'chart') {
            if (View::exists('partials._pdf_charts')) {
                $viewName = 'partials._pdf_charts';
            } elseif (View::exists('reports._pdf_charts')) {
                $viewName = 'reports._pdf_charts';
            } elseif (View::exists('partials._pdf_news')) {
                $viewName = 'partials._pdf_news'; // fallback terakhir (tabel)
            } else {
                $viewName = 'reports._pdf_simple';
            }

            Log::info('Using chart PDF view', ['view' => $viewName]);
        } else {
            // Untuk laporan tabel
            $viewName = View::exists('partials._news_table')
                ? 'partials._news_table'
                : 'reports._table_simple';

            Log::info('Using table PDF view', ['view' => $viewName]);
        }

        try {
            $html = view($viewName, $viewData)->render();

            // Setup Dompdf
            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');
            $options->setChroot(public_path());

            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Simpan PDF
            \Illuminate\Support\Facades\Storage::disk('public')
                ->put('reports/' . $filename, $dompdf->output());

            Log::info('PDF generated successfully', [
                'filename' => $filename,
                'items_count' => count($detailsArray),
                'source' => $source,
                'pdf_type' => $pdfType,
                'view_used' => $viewName
            ]);
        } catch (\Exception $e) {
            Log::error('PDF generation failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'pdf_type' => $pdfType,
                'view_name' => $viewName ?? 'unknown'
            ]);
            throw $e;
        }
    }

    private function makePdfCharts(
        string $filename,
        array $summary,
        Carbon $start,
        Carbon $end,
        string $source,
        array $daily,
        array $chartUrls,
        array $chartImg
    ): void {
        $viewName = View::exists('partials._pdf_charts')
            ? 'partials._pdf_charts'
            : (View::exists('reports._pdf_charts') ? 'reports._pdf_charts' : 'reports._pdf_simple');

        $html = view($viewName, [
            'summary'   => $summary,
            'start'     => $start,
            'end'       => $end,
            'source'    => $source,
            'daily'     => $daily,
            'chartUrls' => $chartUrls,
            'chartImg'  => $chartImg,
            'generated_at' => now(),
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->setChroot(public_path());

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        Storage::disk('public')->put('reports/' . $filename, $dompdf->output());
    }

    private function humanFileSize(int $bytes, int $decimals = 1): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = (int) floor(log($bytes, 1024));
        return round($bytes / (1024 ** $base), $decimals) . ' ' . $units[$base];
    }

    private function chartToBase64(string $url): ?string
    {
        $context = stream_context_create([
            'http' => ['timeout' => 15],
            'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);

        $bin = @file_get_contents($url, false, $context);
        if (!$bin) {
            return null;
        }
        return 'data:image/png;base64,' . base64_encode($bin);
    }
}
