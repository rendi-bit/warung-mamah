<x-app-layout>
    <x-slot name="header">
        <div class="max-w-7xl mx-auto">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                Profile Settings
            </h2>
            <p class="text-sm text-gray-500 mt-1">Kelola informasi akun, keamanan, dan preferensi akses.</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-white shadow sm:rounded-lg p-6 border border-emerald-50">
                    <p class="text-xs uppercase tracking-wide text-emerald-700 font-semibold">Profil Aktif</p>
                    <h3 class="text-xl font-bold text-gray-900 mt-2">{{ auth()->user()->name }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ auth()->user()->email }}</p>
                    <div class="mt-4 text-sm text-gray-600">Terakhir update: {{ now()->translatedFormat('d F Y') }}</div>
                </div>
                <div class="bg-white shadow sm:rounded-lg p-6 border border-emerald-50">
                    <p class="text-xs uppercase tracking-wide text-emerald-700 font-semibold">Keamanan</p>
                    <h3 class="text-xl font-bold text-gray-900 mt-2">Password</h3>
                    <p class="text-sm text-gray-500 mt-1">Pastikan kombinasi password kuat dan unik.</p>
                </div>
                <div class="bg-white shadow sm:rounded-lg p-6 border border-emerald-50">
                    <p class="text-xs uppercase tracking-wide text-emerald-700 font-semibold">Data Akun</p>
                    <h3 class="text-xl font-bold text-gray-900 mt-2">Kontrol Penuh</h3>
                    <p class="text-sm text-gray-500 mt-1">Kamu bisa mengubah detail akun kapan saja.</p>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg border border-emerald-50">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg border border-emerald-50">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg border border-red-50">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
