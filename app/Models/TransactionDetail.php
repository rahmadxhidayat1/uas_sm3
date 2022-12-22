<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'transaction_id', 'product_id', 'quantity', 'amount', 'created_at'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public $incrementing = false;
}
