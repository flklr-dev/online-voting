<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Authenticatable
{
    use HasFactory;

    protected $table = 'students';
    protected $primaryKey = 'student_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'student_id',
        'fullname',
        'school_email',
        'faculty',
        'program',
        'status',
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}

