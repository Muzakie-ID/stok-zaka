<div class="pb-24">
    <!-- Header -->
    <div class="bg-white p-4 sticky top-14 z-30 shadow-sm border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-800">Riwayat Service</h1>
        <p class="text-xs text-gray-500">Daftar perbaikan unit HP</p>
    </div>

    <div class="p-4 space-y-4">
        <!-- Summary Card -->
        <div class="card bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg">
            <div class="card-body p-5">
                <h2 class="text-sm font-medium opacity-90">Total Biaya Service</h2>
                <p class="text-3xl font-bold">Rp {{ number_format($totalBiayaService, 0, ',', '.') }}</p>
                <p class="text-xs opacity-80 mt-1">Akumulasi biaya perbaikan stok</p>
            </div>
        </div>

        <!-- List Service -->
        <div class="space-y-3">
            @foreach($services as $service)
            <div class="card bg-white shadow-sm border border-gray-100">
                <div class="card-body p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-gray-800">{{ $service->hp->merk_model ?? 'HP Terhapus' }}</h3>
                            <p class="text-xs text-gray-500">{{ $service->tanggal_service }}</p>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-red-600">- Rp {{ number_format($service->biaya, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="mt-2 pt-2 border-t border-gray-100">
                        <p class="text-sm text-gray-600 italic">"{{ $service->deskripsi }}"</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $services->links() }}
        </div>
    </div>
</div>
