<?php

// app/Models/ActivityLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'admin_id',
        'action',
        'module',
        'description'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id')->withDefault([
            'name' => 'System'
        ]);
    }
}