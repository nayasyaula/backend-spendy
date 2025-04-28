<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = ['transaction_id', 'coa_name', 'note', 'debit', 'credit', 'balance'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
