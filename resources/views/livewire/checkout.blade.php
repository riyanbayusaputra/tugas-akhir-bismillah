<div class="max-w-[480px] mx-auto bg-white min-h-screen relative shadow-lg pb-[140px]">
    <!-- Header -->
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white z-50">
        <div class="flex items-center h-16 px-4 border-b border-gray-100">
            <button onclick="history.back()" class="p-2 hover:bg-gray-50 rounded-full">
                <i class="bi bi-arrow-left text-xl"></i>
            </button>
            <h1 class="ml-2 text-lg font-medium">Checkout</h1>
        </div>
    </div>

    <!-- Main Content -->
    <div class="pt-20 pb-12 px-4 space-y-8">
        <!-- Section 1: Order Summary -->
        <div>
            <div class="flex items-center gap-2 mb-4">
                <i class="bi bi-cart-check text-lg text-primary"></i>
                <h2 class="text-lg font-medium">Ringkasan Pesanan</h2>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-4">
                <div class="space-y-4">
                    @foreach($carts as $cart)
                        <div class="flex gap-3">
                            <img src="{{$cart->product->first_image_url ?? asset('image/no-pictures.png')}}" 
                                alt="{{$cart->product->name}}" 
                                class="w-20 h-20 object-cover rounded-lg">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium line-clamp-2">{{$cart->product->name}}</h3>
                                <div class="text-sm text-gray-500 mt-1">{{$cart->quantity}} x Rp {{number_format($cart->product->price)}}</div>
                                <div class="text-primary font-medium">Rp {{number_format($cart->product->price * $cart->quantity)}}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Section 2: Recipient Information -->
        <div>
            <div class="flex items-center gap-2 mb-4">
                <i class="bi bi-person text-lg text-primary"></i>
                <h2 class="text-lg font-medium">Data Penerima</h2>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-4 space-y-4">
                <!-- Name -->
                <div>
                    <label class="text-sm text-gray-600 mb-1.5 block">Nama Lengkap</label>
                    <input type="text" 
                        wire:model="shippingData.recipient_name"
                        class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-primary focus:border-primary"
                        placeholder="Masukkan nama lengkap penerima">
                </div>

                <!-- Phone -->
                <div>
                    <label class="text-sm text-gray-600 mb-1.5 block">Nomor Telepon</label>
                    <input wire:model="shippingData.phone"   
                        type="tel" 
                        class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-primary focus:border-primary"
                        placeholder="Contoh: 08123456789">
                </div>
            </div>
        </div>

        

        <div class="mb-4">
            <label for="provinsi" class="block text-sm font-medium text-gray-700">Provinsi</label>
            <select id="provinsi" wire:model.live="selected_provinsi" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                <option value="">Pilih Provinsi</option>
                @foreach($provinsis as $provinsi)
                    <option value="{{ $provinsi['id'] }}">{{ $provinsi['name'] }}</option>
                @endforeach
            </select>
            @error('selected_provinsi') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        
        <div class="mb-4">
            <label for="kabupaten" class="block text-sm font-medium text-gray-700">Kabupaten/Kota</label>
            <select id="kabupaten" wire:model.live="selected_kabupaten" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" @if(empty($kabupatens)) disabled @endif>
                <option value="">Pilih Kabupaten/Kota</option>
                @foreach($kabupatens as $kabupaten)
                    <option value="{{ $kabupaten['id'] }}">{{ $kabupaten['name'] }}</option>
                @endforeach
            </select>
            @error('selected_kabupaten') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        
        <div class="mb-4">
            <label for="kecamatan" class="block text-sm font-medium text-gray-700">Kecamatan</label>
            <select id="kecamatan" wire:model.live="selected_kecamatan" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" @if(empty($kecamatans)) disabled @endif>
                <option value="">Pilih Kecamatan</option>
                @foreach($kecamatans as $kecamatan)
                    <option value="{{ $kecamatan['id'] }}">{{ $kecamatan['name'] }}</option>
                @endforeach
            </select>
            @error('selected_kecamatan') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        
        

        <!-- Section 3: Shipping Address -->
        <div>
            <div class="bg-white rounded-xl border border-gray-100 p-4 space-y-4">
                <!-- Detailed Address -->
                <div>
                    <label class="text-sm text-gray-600 mb-1.5 block">Detail Alamat</label>
                    <textarea 
                    wire:model.live="shippingData.shipping_address"
                    class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-primary focus:border-primary"
                    rows="3"
                    placeholder="Nama jalan, nomor rumah (patokan), RT/RW, Desa/Kelurahan"></textarea>
                </div>
                <div>
                    @if($shippingData['shipping_address'] && $shippingData['recipient_name'] && $shippingData['phone'])
                        <div class="mt-3 p-3 bg-gray-50 rounded-lg text-sm">
                            <div class="font-medium">Detail Pengiriman:</div>
                            <div class="text-gray-600">Nama : {{$shippingData['recipient_name']}}</div>
                            <div class="text-gray-600">Nomor : {{$shippingData['phone']}}</div>
                            <div class="text-gray-600">Alamat : {{$shippingData['shipping_address']}}</div>
                            <div class="text-gray-600">Biaya Ongkir luar : Ongkir di tentukan setelah anda melakukan pemesanan</div>
                        </div>
                    @endif
                </div>
            </div>
            
        </div>
        
        

        <!-- Section 5: Additional Notes -->
        <div>
            <div class="flex items-center gap-2 mb-4">
                <i class="bi bi-pencil text-lg text-primary"></i>
                <h2 class="text-lg font-medium">Catatan Tambahan</h2>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-4">
                <textarea 
                    wire:model.live="shippingData.noted"
                    class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-primary focus:border-primary"
                    rows="2"
                    placeholder="Catatan untuk kurir (opsional)"></textarea>
            </div>
        </div>
    </div>
    <div>
        <div class="flex items-center gap-2 mb-4">
            <i class="bi bi-clock text-lg text-primary flex-shrink-0 ml-4"></i>
            <h2 class="text-lg font-medium">Jadwal Pemakain</h2>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Tanggal Pengiriman -->
                <div>
                    <label class="text-sm text-gray-600 mb-1.5 block">Tanggal</label>
                    <input type="date" wire:model.defer="shippingData.delivery_date"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary" />
                    @error('shippingData.delivery_date')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>
            
                <!-- Waktu Pengiriman -->
                <div>
                    <label class="text-sm text-gray-600 mb-1.5 block">Waktu</label>
                    <input 
                        type="time" 
                        wire:model.defer="shippingData.delivery_time"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary cursor-pointer"
                        required
                    />
                    @error('shippingData.delivery_time')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4">
                    <div class="flex items-center">
                        <input wire:model.live="isCustomCatering" type="checkbox" id="customCatering" class="mr-2 h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"">
                        <label for="customCatering" class="font-medium">Custom Pesanan</label>
                    </div>
                </div>
                
         
                @if ($isCustomCatering)
                <div class="mt-4">
                    <label for="menu_description">Deskripsi Menu Custom</label>
                    <textarea id="menu_description" wire:model.defer="customCatering.menu_description"   class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-primary focus:border-primary"
                    rows="3"
                    placeholder="Jelaskan menu custom yang Anda inginkan"></textarea>
            
                    @error('customCatering.menu_description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            
                   
                </div>
            @endif
            

                
            </div>
        </div>
    </div>
    

    

    <!-- Fixed Bottom Section -->
    <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white border-t border-gray-100 p-4 z-50">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-sm text-gray-600">Total Pembayaran:</p>
                <p class="text-lg font-semibold text-primary">Rp {{number_format($total + $shippingCost +$price_adjustment)}}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500">{{count($carts)}} Produk</p>
            </div>
        </div>

        <button 
            wire:click="createOrder"
            class="w-full h-12 flex items-center justify-center gap-2 rounded-full bg-primary text-white font-medium hover:bg-primary/90 transition-colors">
            <i class="bi bi-bag-check"></i>
            Buat Pesanan
        </button>
    </div>
</div>
{{-- 
@push('scripts')
    <script type="text/javascript"
        src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('services.midtrans.client_key') }}">
    </script>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('payment-success', (data) => {
                const snapToken = data[0].payment_gateway_transaction_id;
                const orderId = data[0].order_id;

                if (snapToken) {
                    try {
                        window.snap.pay(snapToken, {
                            onSuccess: function(result) {
                                window.location.href = `/order-detail/${orderId}`;
                            },
                            onPending: function(result) {
                                window.location.href = `/order-detail/${orderId}`;
                            },
                            onError: function(result) {
                                alert('Pembayaran gagal! Silakan coba lagi.');
                            },
                            onClose: function() {
                                alert('Anda menutup halaman pembayaran sebelum menyelesaikan transaksi');
                                window.location.href = `/`;
                            }
                        });
                    } catch (error) {
                        alert('Terjadi kesalahan saat membuka popup pembayaran');
                    }
                }
            });
        });
    </script>
@endpush --}}