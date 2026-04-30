<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Operasional
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid gap-6 md:grid-cols-3">

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Login Sebagai</p>

                    <p class="text-2xl font-bold text-gray-900">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="mt-2 text-sm text-gray-500">
                        {{ auth()->user()->email }}
                    </p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Akses Cepat</p>

                    <a 
                        href="{{ route('products.index') }}" 
                        class="inline-flex mt-3 text-sm font-semibold text-green-700 hover:text-green-800"
                    >
                        Lihat Katalog Produk
                    </a>

                    <a 
                        href="{{ route('orders.index') }}" 
                        class="inline-flex mt-2 text-sm font-semibold text-green-700 hover:text-green-800"
                    >
                        Lacak Pesanan
                    </a>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Status Sistem</p>

                    <p class="text-2xl font-bold text-gray-900">
                        Online
                    </p>

                    <p class="mt-2 text-sm text-gray-500">
                        {{ now()->translatedFormat('d F Y, H:i') }} WIB
                    </p>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>