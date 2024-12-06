<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Partylist extends Model
{
    use HasFactory;

    protected $table = 'partylists';
    protected $primaryKey = 'partylist_id';
    public $timestamps = false;

    protected $fillable = ['name'];

    public function candidates()
    {
        return $this->hasMany(Candidate::class, 'partylist_id');
    }
} 