<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorAction extends Model
{
    protected $fillable = ['visitor_session_id', 'product_id', 'action'];

    public function visitorSession()
    {
        return $this->belongsTo(VisitorSession::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
