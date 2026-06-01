<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorSession extends Model
{
    protected $fillable = ['catalog_id', 'name', 'phone', 'fingerprint', 'ip'];

    public function catalog()
    {
        return $this->belongsTo(Catalog::class);
    }

    public function actions()
    {
        return $this->hasMany(VisitorAction::class);
    }
}
