<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cylinder extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'size',
        'location',
        'allocated_date',
        'user_id',
        'qr_code_path',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'allocated_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
