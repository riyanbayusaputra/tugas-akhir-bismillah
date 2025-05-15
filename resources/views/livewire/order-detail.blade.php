<div class="max-w-[480px] mx-auto bg-white min-h-screen relative shadow-lg pb-24">
        <!-- Header -->
        <div class="fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white z-50">
            <div class="flex items-center h-16 px-4 border-b border-gray-100">
                <button onclick="window.location.href='{{ route('orders') }}'" class="p-2 hover:bg-gray-50 rounded-full">
                    <i class="bi bi-arrow-left text-xl"></i>
                </button>
                <h1 class="ml-2 text-lg font-medium">Detail Pesanan</h1>
            </div>
        </div>

        <!-- Main Content -->
        <div class="pt-20 p-4">
            <!-- Order Status -->
            <div wire:poll.delay.5000ms="checkPaymentStatus" class="bg-{{$statusInfo['color']}}-50 p-4 rounded-xl mb-6">
                <div class="flex items-center gap-3">
                    <i class="bi {{$statusInfo['icon']}} text-2xl text-{{$statusInfo['color']}}-500"></i>
                    <div>
                        <h2 class="font-medium text-{{$statusInfo['color']}}-600">{{$statusInfo['title']}}</h2>
                        <p class="text-sm text-{{$statusInfo['color']}}-600">{{$statusInfo['message']}}</p>
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="border border-gray-200 rounded-xl overflow-hidden mb-6">
                <div class="p-4 bg-gray-50 border-b border-gray-200">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="font-medium">Detail Pesanan</h3>
                        <span class="text-sm text-gray-500">{{$order->order_number}}</span>
                    </div>
                    <div class="text-sm text-gray-500"> {{$order->created_at->format('d M Y H:i')}}</div>
                </div>

                <div class="p-4">
                    @foreach($order->items as $item)
                    <div class="flex gap-3 pb-4 border-b border-gray-100">
                        <img src="{{$item->product->first_image_url ?? asset('image/no-pictures.png')}}" 
                             alt="Product" class="w-20 h-20 object-cover rounded-lg">
                        <div>
                            <h4 class="font-medium">{{$item->product_name}}</h4>
                            <div class="mt-1">
                                <span class="text-sm">{{$item->quantity}} x </span>
                                <span class="font-medium">Rp {{number_format($item->price, 0, ',', '.')}}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <div class="pt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span>Rp {{number_format($order->subtotal, 0, ',', '.')}}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Ongkir</span>
                            <span>Rp {{number_format($order->shipping_cost, 0, ',', '.')}}</span>
                        </div>

                        <!-- Cek apakah ada price_adjustment pada order -->
                            @if ($order->is_custom_catering && $order->price_adjustment > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Harga Tambahan (Custom Catering)</span>
                                <span>Rp {{ number_format($order->price_adjustment, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="pt-2 border-t border-gray-200">
                            <div class="flex justify-between font-medium">
                                <span>Total</span>
                                <span class="text-primary">Rp {{number_format($order->total_amount, 0, ',', '.')}}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Details -->
            <div class="border border-gray-200 rounded-xl overflow-hidden mb-6">
                <div class="p-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-medium">Informasi Pengiriman</h3>
                </div>
                <div class="p-4 space-y-3">
                    <div class="flex gap-2">
                        <span class="text-gray-600 min-w-[140px]">Nama Penerima</span>
                        <span>: {{$order->recipient_name}}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-gray-600 min-w-[140px]">No. Telepon</span>
                        <span>: {{$order->phone}}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-gray-600 min-w-[140px]">Alamat</span>
                        <span>: {{$order->shipping_address}}</span>
                    </div>
                  
                    @if($order->order_number !== null)
                    <div class="border-t border-gray-100 pt-4 mt-4">
                        <div class="flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-gray-600">No. Order:</span>
                                    <span class="font-medium ml-2">{{$order->order_number}}</span>
                                </div>
                                <button 
                                    onclick="copyToClipboard('{{$order->order_number}}')"
                                    class="text-primary hover:text-primary/80">
                                    <i id="copyButton" class="bi bi-clipboard text-xl"></i>
                                </button>
                            </div>
                            @if ($order->status != 'cancelled')
                            <a 
                                href="https://wa.me/{{$store->whatsapp}}?text=Halo%20kak,%20Saya%20ingin%20tanya%20tentang%20pesanan%20saya%20dengan%0a%0a*no.%20pesanan%20:%20{{$order->order_number}}*"
                                target="_blank"
                                class="block w-full bg-green-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-green-700 transition-colors text-center">
                                <i class="bi bi-whatsapp me-2"></i>
                                Hubungi Penjual
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @if($order->status === 'pending' && ($order->payment_gateway_transaction_id == null))
                <!-- Payment Instructions -->
                <div class="space-y-4">
                    <h3 class="font-medium">Petunjuk Pembayaran</h3>

                    @foreach($paymentMethods as $item)
                    <!-- BCA -->
                    <div class="border rounded-xl overflow-hidden">
                        <div class="flex items-center gap-3 p-4 bg-gray-50 border-b">
                            <img src="{{ Storage::url($item->image) }}" 
                                alt="BCA" class="h-6">
                            <span class="font-medium">{{$item->name}}</span>
                        </div>
                        <div class="p-4">
                            <div class="flex justify-between items-center">
                                <div class="space-y-1">
                                    <div class="text-sm text-gray-500">Nomor Rekening:</div>
                                    <div class="font-mono font-medium text-lg">{{$item->account_number}}</div>
                                    <div class="text-sm text-gray-500">a.n. {{$item->account_name}}</div>
                                </div>
                                <button class="text-primary hover:text-primary/80" onclick="navigator.clipboard.writeText('1234567890')">
                                    <i  class="bi bi-clipboard text-xl"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <!-- Important Notes -->
                <div class="mt-6 p-4 bg-blue-50 rounded-xl">
                    <div class="flex items-start gap-3">
                        <i class="bi bi-info-circle-fill text-blue-500 mt-0.5"></i>
                        <div class="text-sm text-blue-700">
                            <p class="font-medium mb-1">Penting:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>Transfer sesuai dengan nominal yang tertera</li>
                                <li>Simpan bukti pembayaran</li>
                                <li>Upload bukti pembayaran setelah transfer</li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            @if($order->payment_proof)
            <div class="p-4 border border-gray-200 rounded-xl mb-6 mt-6">
                <h3 class="font-medium mb-4">Bukti Pembayaran</h3>
                <div class="space-y-3">
                    <img 
                        src="{{ Storage::url($order->payment_proof) }}"
                        alt="Bukti Pembayaran"
                        class="w-full rounded-lg border border-gray-100"
                    />
                </div>
            </div>
        @endif
        </div>

       


        

        @if($order->status === 'pending' && ($order->payment_gateway_transaction_id == null) && ($order->payment_proof == null))
            <!-- Bottom Button -->
            <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white border-t border-gray-100 p-4 z-50">
                <a href="{{route('payment-confirmation', ['orderNumber' => $order->order_number])}}" class="block w-full bg-primary text-white py-3 rounded-xl font-medium hover:bg-primary/90 transition-colors text-center">
                    Konfirmasi Pembayaran
                </a>
            </div>
        @elseif($order->status === 'pending' && ($order->payment_gateway_transaction_id !== null) && ($order->payment_proof == null))
        
            <!-- Bottom Button -->
            <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white border-t border-gray-100 p-4 z-50">
                <a href="{{$order->payment_gateway_transaction_id    }}" class="block w-full bg-primary text-white py-3 rounded-xl font-medium hover:bg-primary/90 transition-colors text-center">
                    Lakukan Pembayaran
                </a>
            </div>
        @endif
        
        
    </div>
    
    
@push('scripts')
<script>
    const copyButton = document.getElementById('copyButton');
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            document.querySelector('button[onclick="copyToClipboard(\'' + text + '\')"]').classList.add('animate-bounce');
            copyButton.classList.remove('bi-clipboard');
            copyButton.classList.add('bi-clipboard-check');
            setTimeout(() => {
                document.querySelector('button[onclick="copyToClipboard(\'' + text + '\')"]').classList.remove('animate-bounce');
                copyButton.classList.remove('bi-clipboard-check');
                copyButton.classList.add('bi-clipboard');
            }, 2500);
        });
    }
</script>
@endpush
