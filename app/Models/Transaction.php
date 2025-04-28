<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = ['noref', 'type', 'date', 'notes'];

    public function details()
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id');
    }
}

class TransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = ['transaction_id', 'coa_from', 'coa_to', 'debit', 'credit'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function coaFrom()
    {
        return $this->belongsTo(Coas::class, 'coa_from');
    }

    public function coaTo()
    {
        return $this->belongsTo(Coas::class, 'coa_to');
    }
}

