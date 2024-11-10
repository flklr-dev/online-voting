<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    // Specify the table name if it's not plural
    protected $table = 'admins';

    // Define the primary key field
    protected $primaryKey = 'admin_id';

    // Specify which attributes can be mass-assigned
    protected $fillable = ['username', 'password'];

    // Hide password from array/json responses
    protected $hidden = ['password'];

    // Disable auto-incrementing if your `admin_id` is not auto-incremented
    public $incrementing = true;

    // Ensure the model uses timestamps
    public $timestamps = true;
}
