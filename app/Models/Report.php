<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'reports';

    protected $fillable = [
        'user_id',
        'report_name',
        'report_type',   // daily | monthly | yearly
        'source',        // news | youtube
        'start_date',
        'end_date',
        'format',        // pdf | csv
        'file_path',
        'file_name',
        'mime',
        'file_size',
        'status',        // processing | completed | failed
        'error_message',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    // Relasi ke users (users PK = id_user)
    public function user()
    {
        // foreignKey di reports = user_id, ownerKey di users = id_user
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id_user');
    }
}
