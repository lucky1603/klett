<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{

    use HasFactory;

    protected $guarded = [];

    public function schools() {
        return $this->hasMany(School::class);
    }
}
