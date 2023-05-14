<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function municipality() {
        return $this->belongsTo(Municipality::class);
    }

    public function institution_type() {
        return $this->belongsTo(InstitutionType::class);
    }

    public function setMunicipality(Municipality $municipality) {
        return $this->municipality()->associate($municipality);
    }

    public function removeMunicipality() {
        return $this->municipality()->dissociate();
    }

    public function setInstitutionType(InstitutionType $institutionType) {
        return $this->institution_type()->associate($institutionType);
    }

    public function removeInstitutionType() {
        $this->institution_type()->dissociate();
    }

    public function app_users() {
        return $this->hasMany(AppUser::class);
    }
}
