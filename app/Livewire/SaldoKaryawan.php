<?php

namespace App\Livewire;

use App\Models\SalaryTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Saldo Karyawan')]
class SaldoKaryawan extends Component
{
    use WithPagination;

    public string $search = '';

    // Form beri upah / cairkan
    public ?int $userId = null;

    public string $userName = '';

    public string $type = SalaryTransaction::TYPE_EARN;

    public string $amount = '';

    public string $description = '';

    public string $flash = '';

    public function render()
    {
        $karyawans = User::query()
            ->where('role', User::ROLE_KARYAWAN)
            ->when($this->search !== '', fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('email', 'like', '%'.$this->search.'%')))
            ->orderBy('name')
            ->get()
            ->map(function (User $u) {
                $u->saldo = SalaryTransaction::saldoFor($u->id);

                return $u;
            });

        return view('livewire.saldo-karyawan', [
            'karyawans' => $karyawans,
            'totalSaldo' => $karyawans->sum('saldo'),
        ]);
    }

    public function openForm(int $userId, string $type): void
    {
        $user = User::findOrFail($userId);
        $this->userId = $user->id;
        $this->userName = $user->name;
        $this->type = $type === SalaryTransaction::TYPE_PAID ? SalaryTransaction::TYPE_PAID : SalaryTransaction::TYPE_EARN;
        $this->amount = '';
        $this->description = '';
        $this->resetErrorBag();
    }

    public function closeForm(): void
    {
        $this->reset(['userId', 'userName', 'amount', 'description']);
    }

    public function simpan(): void
    {
        $this->validate([
            'amount' => 'required|string|min:1',
            'description' => 'nullable|string|max:255',
        ], [
            'amount.required' => 'Nominal wajib diisi.',
        ]);

        // Input sudah diformat ribuan id-ID oleh Alpine ("50.000") → ambil digitnya saja.
        $digits = preg_replace('/\D/', '', (string) $this->amount);
        $amount = (int) $digits;

        if ($amount < 1000) {
            $this->addError('amount', 'Nominal minimal Rp 1.000.');

            return;
        }

        $user = User::where('role', User::ROLE_KARYAWAN)->findOrFail($this->userId);

        DB::transaction(function () use ($user, $amount) {
            SalaryTransaction::create([
                'user_id' => $user->id,
                'admin_id' => Auth::id(),
                'type' => $this->type,
                'amount' => $amount,
                'description' => $this->description ?: ($this->type === SalaryTransaction::TYPE_EARN ? 'Upah dari admin' : 'Pencairan upah'),
            ]);
        });

        $aksi = $this->type === SalaryTransaction::TYPE_EARN ? 'Upah diberikan kepada' : 'Upah dicairkan kepada';
        $this->flash = "{$aksi} {$user->name}.";

        $this->closeForm();
        $this->dispatch('tutup-modal-upah');
    }

    public function riwayat(int $userId)
    {
        return SalaryTransaction::with('admin')
            ->where('user_id', $userId)
            ->latest()
            ->take(20)
            ->get();
    }
}
