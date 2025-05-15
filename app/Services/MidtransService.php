<?php


namespace App\Services;

use App\Models\Order;
use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = false;
    }

    public function createTransaction($order, $items)
    {
        $resp = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode(config('services.midtrans.server_key') . ':')
        ]);
        
        // Ensure total_amount is a valid number
        if (!is_numeric($order->total_amount)) {
            return response()->json([
                'message' => 'Invalid total amount'
            ], 400);
        }

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total_amount,
            ],
            'customer_details' => [
                'first_name' => $order->recipient_name,
                'email' => auth()->user()->email,
                'phone' => $order->phone,
                'shipping_address' => [
                    'first_name' => $order->recipient_name,
                    'phone' => $order->phone,
                    'address' => $order->address_detail,
                    'city' => $order->city,
                    'postal_code' => '',
                    'country_code' => 'IDN',
                ]
            ],
            'item_details' => array_merge(
                $items->isEmpty() ? [] : $items->map(function($item) {
                    return [
                        'id' => $item->product_id,
                        'price' => (int) $item->price,
                        'quantity' => $item->quantity,
                        'name' => substr($item->product_name, 0, 50)
                    ];
                })->toArray(),
                [
                    [
                        'id' => 'SHIPPING',
                        'price' => (int) $order->shipping_cost,
                        'quantity' => 1,
                        'name' => 'Ongkir'
                    ],
                    [
                        'id' => 'PRICE_ADJUSTMENT',
                        'price' => (int) $order->price_adjustment,
                        'quantity' => 1,
                        'name' => 'Tambahan biaya custom'
                    ]
                ]
            )
        ];

        $response = $resp->post(config('services.midtrans.snap_url'), $params);

        if ($response->status() == 200 || $response->status() == 201) {
            return $response->json()['redirect_url'];
        } else {
            \Log::error('Midtrans API Error', [
                'response_body' => $response->body(),
                'status_code' => $response->status()
            ]);
            return response()->json([
                'message' => $response->body()
            ], 500);
        }
    }

    public function getStatus($order)
    {
        try {
            $status = Transaction::status($order->order_number);
            return [
                'success' => true,
                'message' => 'Success get transaction status',
                'data' => $status
            ];
        } catch(\Exception $e) {
            \Log::error('Midtrans Transaction Status Error', [
                'order_id' => $order->order_number,
                'message' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }
}
