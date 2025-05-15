<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $store->name ?? 'Aplikasi Pemesanan Catering Bintang rasa' }}</title>

    <!-- Favicon & App Icons -->
    <link rel="icon" type="image/png" href="{{ $store->imageUrl ?? asset('image/store.png') }}">
    <link rel="apple-touch-icon" href="{{ $store->imageUrl ?? asset('image/store.png') }}">
    <meta name="msapplication-TileImage" content="{{ $store->imageUrl ?? asset('image/store.png') }}">
    <meta name="theme-color" content= "{{$store->primary_color ?? '#ff6666'}}">

    {{-- font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "{{$store->primary_color ?? '#ff6666'}}",
                        secondary: "{{$store->secondary_color ?? '#818CF8'}}",
                        accent: '#C7D2FE',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50" style="font-family: 'poppins', sans-serif;
  font-weight: 500;
  font-style: normal;">

    {{ $slot }}
    @if(!isset($hideBottomNav))
        @livewire('components.bottom-navigation')
    @endif

    @livewire('components.alert')

    
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    @livewireScripts
    @stack('scripts')
</body>
</html>