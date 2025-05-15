<div class="max-w-[480px] mx-auto bg-white min-h-screen relative shadow-lg">
    <!-- Header -->
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white z-50">
        <div class="flex items-center h-16 px-4 border-b border-gray-200">
            <button onclick="history.back()" class="p-2 hover:bg-gray-100 rounded-full">
                <i class="bi bi-arrow-left text-xl text-gray-600"></i>
            </button>
            <h1 class="ml-3 text-lg font-semibold text-gray-700">Profil Saya</h1>
        </div>
    </div>

    <!-- Profile Content -->
    <div class="pt-16">
        <!-- Profile Header -->
        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-6 rounded-b-3xl shadow-md">
            <div class="flex flex-col items-center gap-4 text-center">
                <div class="w-24 h-24 rounded-full bg-white/20 flex items-center justify-center">
                    <i class="bi bi-person text-5xl text-white"></i>
                </div>
                <div class="text-white">
                    <h2 class="text-xl font-semibold">{{$name}}</h2>
                    <p class="text-white/80 text-sm">{{$email}}</p>
                </div>
            </div>
        </div>

        <!-- Edit Profile -->
        <div class="p-4">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Akun</h3>
            <a href="{{ route('profile.edit') }}" class="w-full flex items-center justify-center gap-2 p-4 bg-white rounded-xl shadow-md hover:bg-gray-100 transition">
                <i class="bi bi-pencil-square text-gray-600"></i>
                <span class="text-gray-700 font-medium">Ubah Profil</span>
            </a>
        </div>

        <!-- Profile Menu -->
        <div class="p-4 space-y-4">
            <!-- Contact via WhatsApp -->
            <a href="https://wa.me/{{$whatsapp}}" target="_blank" class="flex items-center justify-between p-4 bg-gray-50 rounded-xl shadow-md hover:bg-gray-100 transition">
                <div class="flex items-center gap-3">
                    <i class="bi bi-whatsapp text-green-500 text-xl"></i>
                    <span class="text-gray-700 font-medium text-sm">Hubungi Bintang Catering via WhatsApp</span>
                </div>
                <i class="bi bi-chevron-right text-gray-400"></i>
            </a>

            <!-- Logout Button -->
            <button wire:click="logout" class="w-full mt-6 p-4 text-red-600 flex items-center justify-center gap-2 bg-red-50 rounded-xl shadow-md hover:bg-red-100 transition">
                <i class="bi bi-box-arrow-right"></i>
                <span class="font-medium">Keluar</span>
            </button>
        </div>
    </div>
</div>
