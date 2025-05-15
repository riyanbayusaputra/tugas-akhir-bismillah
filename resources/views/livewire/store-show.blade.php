<!-- Main Container -->
<div class="max-w-[480px] mx-auto bg-white min-h-screen relative shadow-lg pb-[70px]">
    <!-- Banner -->
    <div class="h-[180px] relative overflow-hidden bg-gradient-to-br from-primary to-secondary rounded-b-3xl">
        @if($store->bannerUrl)
            <img src="{{ $store->bannerUrl }}" alt="Banner" class="w-full h-full object-cover">
        @endif
        <div class="absolute inset-0 opacity-50 pattern-dots"></div>
    </div>

    <!-- Profile Section -->
    <div class="px-5 relative -mt-20">
        <div class="flex justify-center">
            <div class="w-[120px] h-[120px] bg-gradient-to-br from-primary to-secondary rounded-full flex items-center justify-center shadow-lg transform hover:scale-105 transition-transform duration-300">
                <img src="{{ $store->imageUrl ?? asset('image/store.png') }}" alt="Store" 
                     class="w-[100px] h-[100px] rounded-full border-4 border-white">
            </div>
        </div>
        <h4 class="mt-4 mb-1 text-gray-800 font-extrabold text-xl text-center hover:text-primary transition-colors">{{ $store->name }}</h4>
        <p class="text-gray-600 text-sm text-center italic hover:text-gray-800 transition-colors">{{ $store->description }}</p>
    </div>

    <!-- Navigation Tabs -->
    <div class="mt-6 px-4 overflow-x-auto hide-scrollbar">
        <div class="flex gap-3 pb-3 whitespace-nowrap">
            <button wire:click="setCategory('all')" 
                    class="px-6 py-2 flex items-center rounded-full transition-colors border {{ $selectedCategory === 'all' ? 'bg-primary text-white border-primary' : 'text-gray-700 border-gray-300 hover:border-primary hover:text-primary' }}">
                Semua
            </button>
            
            @foreach ($categories as $category)
                <button wire:click="setCategory('{{ $category->id }}')"
                        class="px-6 py-2 flex items-center rounded-full transition-colors border {{ $selectedCategory == $category->id ? 'bg-primary text-white border-primary' : 'text-gray-700 border-gray-300 hover:border-primary hover:text-primary' }}">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Product Section -->
    <div class="p-4">
        @if($products->isEmpty())
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center py-12 px-4">
                <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mb-4">
                    <i class="bi bi-bag-x text-3xl text-primary"></i>
                </div>
                <h3 class="text-base font-medium text-gray-900 mb-2">Belum Ada meenu</h3>
                <p class="text-gray-500 text-center text-sm">
                    @if($selectedCategory !== 'all')
                        Belum ada menu dalam kategori ini
                    @else
                        belum menambahkan produk apapun
                    @endif
                </p>
            </div>
        @else
            <div class="grid grid-cols-2 gap-3">
                @foreach($products as $item)
                <div class="bg-white rounded-xl overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
                    <a href="{{ route('product.detail', ['slug' => $item->slug]) }}" wire:navigate>
                        <div class="relative">
                            <img src="{{ $item->first_image_url ?? asset('image/no-pictures.png') }}" 
                                 alt="{{$item->name}}" 
                                 class="w-full h-[160px] object-cover">
                        </div>
                        <div class="p-3">
                            <h6 class="text-sm font-bold text-gray-800 line-clamp-2">{{$item->name}}</h6>
                            <div class="mt-2 flex items-center gap-1">
                                <span class="text-xs text-gray-500">Rp</span>
                                <span class="text-primary font-bold">{{ number_format($item->price, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
