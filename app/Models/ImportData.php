<?php

namespace App\Models;

use Database\Factories\ImportDataFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportData extends Model
{
    /** @use HasFactory<ImportDataFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
    ];
}
