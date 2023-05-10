<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppUser extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function subjects() {
        return $this->belongsToMany(Subject::class);
    }

    public function professional_statuses() {
        return $this->belongsToMany(ProfessionalStatus::class);
    }

    public function addSubject(Subject $subject) {
        return $this->subjects()->attach($subject->id);
    }

    public function removeSubject(Subject $subject) {
        return $this->subjects()->detach($subject->id);
    }

    public function removeAllSubjects() {
        return $this->subjects()->detach();
    }

    public function addProfessionalStatus(ProfessionalStatus $status) {
        return $this->professional_statuses()->attach($status->id);
    }

    public function removeProfessionalStatus(ProfessionalStatus $status) {
        return $this->professional_statuses()->detach($status->id);
    }

    public function removeAllProfessionalStatuses() {
        return $this->professional_statuses()->detach();
    }


}
