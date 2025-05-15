<?php

namespace App\Models;

use App\Services\MidtransService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'subtotal',    
        'total_amount',
        'status',
        'payment_status',
        'recipient_name',
        'phone',
        'shipping_cost',
        'shipping_address',
        'notes',
        'delivery_date',
        'delivery_time',
        'payment_gateway_transaction_id',
        'payment_gateway_data',
        'payment_proof',
        'is_custom_catering',
        'price_adjustment',
        'provinsi_id', 'kabupaten_id', 'kecamatan_id',
        'provinsi_name', 'kabupaten_name', 'kecamatan_name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            
            $model->total_amount = $model->subtotal + $model->shipping_cost + $model->price_adjustment;

            if($model->payment_gateway_transaction_id == null && Store::first()->is_use_payment_gateway == 1) {
                $midtrans = app(MidtransService::class);
                $paymentUrl = $midtrans->createTransaction($model, $model->items);

                $model->payment_gateway_transaction_id = $paymentUrl;
            }
        });
    }

    public function scopeSearch($query, $value)
    {
        $query->where("order_number", "like", "%{$value}%");
    }

    public function customCatering(): HasMany
    {
        return $this->hasMany(CustomCatering::class);
    }
    
}
