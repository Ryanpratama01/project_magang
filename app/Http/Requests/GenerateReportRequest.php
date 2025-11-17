<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // izinkan (kalau perlu auth, atur di sini)
    }

    public function rules(): array
    {
        return [
            'report_name' => ['required','string','max:150'],
            'report_type' => ['required','in:daily,monthly,yearly'],
            'source'      => ['required','in:news,youtube'],
            'start_date'  => ['required','date'],
            'end_date'    => ['required','date','after_or_equal:start_date'],
            'format'      => ['required','in:pdf,csv'],
        ];
    }

    public function messages(): array
    {
        return [
            'report_name.required'      => 'Nama file wajib diisi.',
            'report_type.in'            => 'Tipe laporan tidak valid.',
            'source.in'                 => 'Sumber data tidak valid.',
            'start_date.required'       => 'Tanggal mulai wajib diisi.',
            'end_date.after_or_equal'   => 'Tanggal selesai harus >= tanggal mulai.',
            'format.in'                 => 'Format output tidak valid.',
        ];
    }
}
