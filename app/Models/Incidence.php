<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incidence extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'store',
        'client',
        'owner',
        'responsable',
        'impact',
        'status',
        'comments',
        'closed',
        'created_at'
    ];
}