<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Hp;
use App\Models\DetailPenjualan;
use App\Models\Service;
use Carbon\Carbon;

class Home extends Component
{
    public function render()
    {
        // 1. Stats Utama
        $profitToday = DetailPenjualan::whereDate('created_at', Carbon::today())->sum('laba_rugi');
        $profitYesterday = DetailPenjualan::whereDate('created_at', Carbon::yesterday())->sum('laba_rugi');
        
        // Hitung persentase kenaikan/penurunan
        $profitTrend = 0;
        if ($profitYesterday > 0) {
            $profitTrend = (($profitToday - $profitYesterday) / $profitYesterday) * 100;
        } elseif ($profitToday > 0) {
            $profitTrend = 100; // Jika kemarin 0 dan hari ini ada, anggap 100% naik
        }

        $stokReadyCount = Hp::where('status', 'READY')->count();
        $totalModalStok = Hp::where('status', 'READY')->whereNotNull('total_modal')->sum('total_modal');
        $stokModalBelumDiisi = Hp::where('status', 'READY')->where(function ($q) {
            $q->whereNull('total_modal')->orWhereNull('harga_beli_awal');
        })->count();
        $terjualHariIni = DetailPenjualan::whereDate('created_at', Carbon::today())->count();

        // 2. Aktivitas Terbaru (Gabungan Penjualan & Service)
        // Ambil 5 Penjualan Terakhir
        $sales = DetailPenjualan::with('hp')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'sale',
                    'title' => 'Terjual ' . ($item->hp->merk_model ?? 'HP Terhapus'),
                    'desc' => 'Profit: Rp ' . number_format($item->laba_rugi, 0, ',', '.'),
                    'amount' => $item->laba_rugi,
                    'date' => $item->created_at,
                    'icon_bg' => 'bg-green-100',
                    'icon_text' => 'text-green-600',
                    'icon_path' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                ];
            });

        // Ambil 5 Service Terakhir
        $services = Service::with('hp')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'service',
                    'title' => 'Service ' . ($item->hp->merk_model ?? 'HP Terhapus'),
                    'desc' => $item->deskripsi . ' - Rp ' . number_format($item->biaya, 0, ',', '.'),
                    'amount' => $item->biaya,
                    'date' => $item->created_at,
                    'icon_bg' => 'bg-yellow-100',
                    'icon_text' => 'text-yellow-600',
                    'icon_path' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'
                ];
            });

        // Gabung dan Sortir
        $recentActivities = $sales->merge($services)
            ->sortByDesc('date')
            ->take(10);

        // 3. Data Grafik (7 Hari Terakhir)
        $chartData = [];
        $dates = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dates[] = $date->format('d M');
            
            $daySales = DetailPenjualan::whereDate('created_at', $date)->get();
            
            $chartData['modal'][] = $daySales->sum('modal_terakhir');
            $chartData['profit'][] = $daySales->sum('laba_rugi');
        }

        return view('livewire.home', [
            'profitToday' => $profitToday,
            'profitTrend' => $profitTrend,
            'stokReadyCount' => $stokReadyCount,
            'totalModalStok' => $totalModalStok,
            'stokModalBelumDiisi' => $stokModalBelumDiisi,
            'recentActivities' => $recentActivities,
            'chartDates' => $dates,
            'chartModal' => $chartData['modal'],
            'chartProfit' => $chartData['profit'],
            'terjualHariIni' => $terjualHariIni,
            'isAdmin' => auth()->user()?->isAdmin(),
        ]);
    }
}
