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
    public function render()
    {
        $user = Auth::user();

        return view('livewire.saldo-saya', [
            'saldo' => SalaryTransaction::saldoFor($user->id),
            'riwayat' => SalaryTransaction::with('admin')
                ->where('user_id', $user->id)
                ->latest()
                ->take(50)
                ->get(),
            'totalEarn' => (float) SalaryTransaction::where('user_id', $user->id)->where('type', SalaryTransaction::TYPE_EARN)->sum('amount'),
            'totalPaid' => (float) SalaryTransaction::where('user_id', $user->id)->where('type', SalaryTransaction::TYPE_PAID)->sum('amount'),
        ]);
    }
}
