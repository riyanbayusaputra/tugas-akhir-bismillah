<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Cart;
use App\Models\Store;
use App\Models\Order;
use App\Notifications\NewOrderNotification;
use Illuminate\Support\Facades\Notification;
use App\Services\MidtransService;
use GuzzleHttp\Client;

class Checkout extends Component
{
    public $carts = [];
    public $total = 0;
    public $shippingCost = 0;
    public $store;
    public $price_adjustment = 0;
    public $isCustomCatering = false;
    public $customCatering = [
        'menu_description' => '',
    ];
    protected $midtrans;
    public $shippingData = [
        'recipient_name' => '',
        'phone' => '',
        'shipping_address' => '',
        'noted' => '',
        'delivery_date' => '',
        'delivery_time' => '',
    
    ];

    // Data wilayah
    public $provinsis = [];
    public $kabupatens = [];
    public $kecamatans = [];
    
    public $selected_provinsi = '';
    public $selected_kabupaten = '';
    public $selected_kecamatan = '';

    //yang diijinkan pesan catering
    public $allowedKabupatenIds = ['33.28', '33.27', '33.29'];

    protected $rules = [
        'shippingData.recipient_name' => 'required|min:3',
        'shippingData.phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
        'shippingData.shipping_address' => 'required|min:10',
        // 'shippingData.noted' => 'nullable|min:10',
        'shippingData.delivery_date' => 'required',
        'shippingData.delivery_time' => 'required',

    ];

    

    public function boot(MidtransService $midtrans)
    {
        $this->midtrans = $midtrans;
    }

    public function mount()
    {
        // if (!auth()->user()?->hasVerifiedEmail()) {
        //     return redirect()->route('verification.notice');
        // }
        $this->loadCarts();
        if ($this->carts->isEmpty()) {
            return redirect()->route('home');
        }
        $this->store = Store::first();

        if (auth()->check()) {
            $user = auth()->user();
            $this->shippingData['recipient_name'] = $user->name;
        }

        // Fetch provinsi data when page loads
        $this->fetchProvinsiData();
    }

    public function updatedSelectedProvinsi($provinsiId)
    {
        $this->selected_provinsi = $provinsiId;
        $this->selected_kabupaten = null;
        $this->selected_kecamatan = null;

        $this->kabupatens = [];
        $this->kecamatans = [];
        
        if (!empty($provinsiId)) {
            $this->fetchKabupatenData($provinsiId);
        }
    }
    
    
    public function updatedSelectedKabupaten($kabupatenId)
    {
        $this->selected_kabupaten = $kabupatenId;
        $this->selected_kecamatan = null;
  
        $this->kecamatans = [];
        
        if (!empty($kabupatenId)) {
            $this->fetchKecamatanData($kabupatenId);
        }
    }
    
    public function updatedSelectedKecamatan($kecamatanId)
    {
        $this->selected_kecamatan = $kecamatanId;
   
    }

    public function fetchProvinsiData()
    {
        try {
            $client = new Client();
            $response = $client->get('https://api.binderbyte.com/wilayah/provinsi', [
                'query' => [
                    'api_key' => 'a83a97cb58d93379b17e61de25fd839ce33445f6db05572672bf99344e697c97'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $this->provinsis = $data['value'] ?? $data['data'] ?? [];

            // Filter Jawa Tengah province by its ID (assuming ID for Jawa Tengah is 32)
        $this->provinsis = array_filter($this->provinsis, function($provinsi) {
            return $provinsi['id'] == 33; // Jawa Tengah ID
        });
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'message' => 'Gagal mengambil data provinsi: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function fetchKabupatenData($provinsiId)
    {
        try {
            $client = new Client();
            $response = $client->get('https://api.binderbyte.com/wilayah/kabupaten', [
            'query' => [
                'api_key' => 'a83a97cb58d93379b17e61de25fd839ce33445f6db05572672bf99344e697c97',
                'id_provinsi' => $provinsiId
            ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $this->kabupatens = $data['value'] ?? $data['data'] ?? [];
         
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'message' => 'Gagal mengambil data kabupaten: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function fetchKecamatanData($kabupatenId)
    {
        try {
            $client = new Client();
            $response = $client->get('https://api.binderbyte.com/wilayah/kecamatan', [
                'query' => [
                    'api_key' => 'a83a97cb58d93379b17e61de25fd839ce33445f6db05572672bf99344e697c97',
                    'id_kabupaten' => $kabupatenId
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $this->kecamatans = $data['value'] ?? $data['data'] ?? [];
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'message' => 'Gagal mengambil data kecamatan: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function loadCarts()
    {
        $this->carts = Cart::where('user_id', auth()->id())
            ->with('product')
            ->get();

        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total = 0;

        foreach ($this->carts as $cart) {
            $this->total += $cart->product->price * $cart->quantity;
        }
    }

    public function render()
    {
        if ($this->carts->isEmpty()) {
            return redirect()->route('home');
        }
        return view('livewire.checkout')
            ->layout('components.layouts.app', ['hideBottomNav' => true]);
    }

    public function createOrder()
    {
        if (!$this->carts->isEmpty()) {
            try {
                $this->validate();
                // Validasi kabupaten yang dipilih
            if (!in_array($this->selected_kabupaten, $this->allowedKabupatenIds)) {
                $this->dispatch('showAlert', [
                    'message' => 'Maaf, layanan catering kami hanya tersedia untuk Tegal, Pemalang, Slawi, dan Brebes',
                    'type' => 'error'
                ]);
                return;
            }

                
                
                // Dapatkan nama wilayah berdasarkan ID yang dipilih
                $provinsiName = '';
                $kabupatenName = '';
                $kecamatanName = '';
                
                foreach ($this->provinsis as $provinsi) {
                    if ($provinsi['id'] == $this->selected_provinsi) {
                        $provinsiName = $provinsi['name'];
                        break;
                    }
                }
                
                foreach ($this->kabupatens as $kabupaten) {
                    if ($kabupaten['id'] == $this->selected_kabupaten) {
                        $kabupatenName = $kabupaten['name'];
                        break;
                    }
                }
                
                foreach ($this->kecamatans as $kecamatan) {
                    if ($kecamatan['id'] == $this->selected_kecamatan) {
                        $kecamatanName = $kecamatan['name'];
                        break;
                    }
                }
                
                $order = Order::create([
                    'user_id' => auth()->id(),
                    'order_number' => 'INV-' . strtoupper(uniqid()),
                    'subtotal' => $this->total,
                    'total_amount' => $this->total,
                    'status' => 'checking',
                    'payment_status' => 'unpaid',
                    'recipient_name' => $this->shippingData['recipient_name'],
                    'phone' => $this->shippingData['phone'],
                    'shipping_address' => $this->shippingData['shipping_address'],
                    'noted' => $this->shippingData['noted'],
                    'delivery_date' => $this->shippingData['delivery_date'],
                    'delivery_time' => $this->shippingData['delivery_time'],
                    'is_custom_catering' => $this->isCustomCatering,
                    // Simpan data wilayah
                    'provinsi_id' => $this->selected_provinsi,
                    'kabupaten_id' => $this->selected_kabupaten,
                    'kecamatan_id' => $this->selected_kecamatan,
                    'provinsi_name' => $provinsiName,
                    'kabupaten_name' => $kabupatenName,
                    'kecamatan_name' => $kecamatanName,
                ]);
                
               


                foreach ($this->carts as $cart) {
                    $order->items()->create([
                        'product_id' => $cart->product_id,
                        'product_name' => $cart->product->name,
                        'quantity' => $cart->quantity,
                        'price' => $cart->product->price
                    ]);
                }
                if ($this->isCustomCatering) {
                    $order->customCatering()->create([
                        'menu_description' => $this->customCatering['menu_description'],
                    ]);
                }

                Cart::where('user_id', auth()->id())->delete();

                try {
                    Notification::route('mail', $this->store->email_notification)
                        ->notify(new NewOrderNotification($order));
                } catch (\Exception $e) {
                    // Handle notification exception
                }

                return redirect()->route('order-detail', ['orderNumber' => $order->order_number]);

            } catch (\Exception $e) {
                $this->dispatch('showAlert', [
                    'message' => $e->getMessage(),
                    'type' => 'error'
                ]);
            }
        } else {
            $this->dispatch('showAlert', [
                'message' => 'Keranjang belanja kosong',
                'type' => 'error'
            ]);
        }
    }
}