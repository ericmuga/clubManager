<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsChanges;

class Setting extends Model
{
    use HasFactory,LogsChanges;

       protected $fillable = [
        'table_name', 'field_name', 'log_changes',
    ];

    protected $casts = [
        'loggable_fields' => 'array', // Cast JSON to array
    ];
}
