<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $primaryKey = 'admin_id';

    protected $fillable = [
        'username',
        'password',
    ];

    // Don't include admin_id in fillable
    protected $hidden = ['password']; // Ensure password is hidden by default
}
