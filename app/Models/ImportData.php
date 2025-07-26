<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportData extends Model
{
    /** @use HasFactory<\Database\Factories\ImportDataFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
    ];
}
