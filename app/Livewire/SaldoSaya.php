<?php

namespace App\Livewire;

use App\Models\SalaryTransaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Saldo Saya')]
class SaldoSaya extends Component
{
    public string $periode = 'all'; // all | bulan | minggu

    public function render()
    {
        $user = Auth::user();

        $range = match ($this->periode) {
            'minggu' => [now()->startOfWeek(), now()->endOfWeek()],
            'bulan' => [now()->startOfMonth(), now()->endOfMonth()],
            default => null,
        };

        return view('livewire.saldo-saya', [
            'saldo' => SalaryTransaction::saldoFor($user->id),
            'riwayat' => SalaryTransaction::with('admin')
                ->where('user_id', $user->id)
                ->when($range, fn ($q) => $q->whereBetween('created_at', $range))
                ->latest()
                ->take(50)
                ->get(),
            'totalEarn' => (float) SalaryTransaction::where('user_id', $user->id)
                ->where('type', SalaryTransaction::TYPE_EARN)
                ->when($range, fn ($q) => $q->whereBetween('created_at', $range))
                ->sum('amount'),
            'totalPaid' => (float) SalaryTransaction::where('user_id', $user->id)
                ->where('type', SalaryTransaction::TYPE_PAID)
                ->when($range, fn ($q) => $q->whereBetween('created_at', $range))
                ->sum('amount'),
        ]);
    }
}
