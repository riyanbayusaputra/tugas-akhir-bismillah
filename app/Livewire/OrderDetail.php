<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PaymentMethod;
use App\Models\Order;
use Carbon\Carbon;
use App\Services\OrderStatusService;
use App\Services\MidtransService;
use App\Models\Store;

class OrderDetail extends Component
{
    public $order;
    public $paymentDeadline;
    public $paymentMethods;
    public $store;

    public function mount($orderNumber)
    {
        $this->order = Order::where('order_number', $orderNumber)
                        ->firstOrFail();
        $this->paymentDeadline = Carbon::parse($this->order->created_at)->addHours(12);
        $this->paymentMethods = PaymentMethod::all();
        $this->store = Store::first();
    }

    public function getStatusInfo()
    {
        return OrderStatusService::getStatusInfo(
            $this->order->status,
            $this->paymentDeadline,
            $this->order->completed_at,
            $this->order->payment_gateway_transaction_id
        );
    }

    public function render()
    {
        $statusInfo = $this->getStatusInfo();
      
        $this->checkPaymentStatus();
       
        return view('livewire.order-detail', [
                'statusInfo' => $statusInfo,
                'order' => $this->order
            ])->layout('components.layouts.app', ['hideBottomNav' => true]);
    }

    public function checkPaymentStatus()
    {
        if ($this->order && $this->order->payment_gateway_transaction_id) {
            try {
                $midtrans = app(MidtransService::class);
                $status = $midtrans->getStatus($this->order);

                $latestStatus = $status['data']->transaction_status;

                // Cek apakah status berubah
                if ($latestStatus) {
                    // Update database jika ada perubahan status
                    $this->order->update([
                        'payment_gateway_data' => json_encode($status['data'])
                    ]);
                    switch ($latestStatus) {
                        case 'settlement':
                            $this->order->update([
                                'payment_status' => 'paid',
                            ]);
                            break;
                        case 'pending':
                            $this->order->update([
                                'payment_status' => 'unpaid',
                                'status' => 'pending'
                            ]);
                            break;
                        case 'deny':
                        case 'cancel':
                        case 'expire':
                            $this->order->update([
                                'payment_status' => 'unpaid',
                                'status' => 'cancelled'
                            ]);
                            break;
                    }
                }
            } catch (\Exception $e) {
                report($e);
            }
        }
    }

}
