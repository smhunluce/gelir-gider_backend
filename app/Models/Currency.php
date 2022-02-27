<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency',
        'rate',
        'date',
    ];

    protected $primaryKey = 'currency';

    public $incrementing = FALSE;

    public $timestamps = FALSE;
}
