<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'txnId',
        'thirdPartyId',
        'transactionType',
        'merId',
        'merName',
        'address',
        'amount',
        'commission',
        'totalAmount',
        'currency',
        'reason',
        'refundId',
        'msisdn',
        'accountNumber',
        'clientReference',
        'paymentVia',
        'refId',
        'successRedirectUrl',
        'failureRedirectUrl',
        'cancelRedirectUrl',
        'commissionAmountInPercent',
        'providerCommissionAmountInPercent',
        'commissionFromCustomer',
        'vatAmountInPercent',
        'lotteryTax',
        'message',
        'updateType',
        'Status',
        'StatusReason',
        'ReceiverWalletID',
    ];

    protected $casts = [
        'commissionAmountInPercent'         => 'float',
        'providerCommissionAmountInPercent' => 'float',
        'commissionFromCustomer'            => 'boolean',
        'vatAmountInPercent'                => 'float',
        'amount'                            => 'float',
        'commission'                        => 'float',
        'totalAmount'                       => 'float',
    ];

      public $timestamps = true;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}
