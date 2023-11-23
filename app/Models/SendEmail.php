<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SendEmail extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function email_type() {
        return $this->belongsTo(EmailType::class);
    }
}
