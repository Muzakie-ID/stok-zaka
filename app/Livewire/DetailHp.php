<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Hp;
use App\Models\Service;
use App\Models\CashFlow;
use Livewire\Attributes\On;

class DetailHp extends Component
{
    public $hp;
    public $deskripsi_service;
    public $biaya_service;
    public $showServiceForm = false;

    // Edit Mode Properties
    public $isEditing = false;
    public $edit_merk_model;
    public $edit_warna;
    public $edit_minus;
    public $edit_sumber_beli;
    public $edit_harga_beli_awal;

    public function toggleServiceForm()
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Hanya admin yang bisa mengelola service.');
        }

        $this->showServiceForm = !$this->showServiceForm;
    }

    public function toggleEdit()
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Hanya admin yang bisa edit data HP.');
        }

        $this->isEditing = !$this->isEditing;
        if ($this->isEditing && $this->hp) {
            $this->edit_merk_model = $this->hp->merk_model;
            $this->edit_warna = $this->hp->warna;
            $this->edit_minus = $this->hp->keterangan_minus;
            $this->edit_sumber_beli = $this->hp->sumber_beli;
            $this->edit_harga_beli_awal = $this->hp->harga_beli_awal;
        }
    }

    public function updateHp()
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Hanya admin yang bisa edit data HP.');
        }

        $this->validate([
            'edit_merk_model' => 'required|string',
            'edit_warna' => 'nullable|string',
            'edit_minus' => 'nullable|string',
            'edit_sumber_beli' => 'nullable|string',
            'edit_harga_beli_awal' => 'required|numeric|min:0',
        ]);

        // Hitung selisih harga beli jika berubah, untuk update total modal
        // Unit inputan karyawan: modal masih null → dihitung dari 0
        $selisih = $this->edit_harga_beli_awal - ($this->hp->harga_beli_awal ?? 0);
        
        $this->hp->update([
            'merk_model' => $this->edit_merk_model,
            'warna' => $this->edit_warna,
            'keterangan_minus' => $this->edit_minus,
            'sumber_beli' => $this->edit_sumber_beli,
            'harga_beli_awal' => $this->edit_harga_beli_awal,
            'total_modal' => ($this->hp->total_modal ?? 0) + $selisih,
        ]);

        $this->isEditing = false;
        $this->dispatch('stok-saved'); // Refresh list utama
    }

    public function deleteHp()
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Hanya admin yang bisa hapus HP.');
        }

        if ($this->hp) {
            $this->hp->delete();
            $this->dispatch('stok-saved');
            $this->dispatch('close-modal-detail'); // Perlu handle ini di JS view
        }
    }

    #[On('open-detail-hp')]
    public function loadHp($id)
    {
        $this->hp = Hp::with('services')->find($id);
        $this->showServiceForm = false;
        $this->isEditing = false;
        $this->dispatch('show-modal-detail');
    }

    public function saveService()
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Hanya admin yang bisa mengelola service.');
        }

        $this->validate([
            'deskripsi_service' => 'required|string',
            'biaya_service' => 'required|numeric|min:0',
        ]);

        // 1. Simpan data service
        Service::create([
            'hp_id' => $this->hp->id,
            'deskripsi' => $this->deskripsi_service,
            'biaya' => $this->biaya_service,
            'tanggal_service' => now(),
        ]);

        // 2. Catat Pengeluaran Kas
        if ($this->biaya_service > 0) {
            CashFlow::create([
                'date' => now(),
                'type' => 'expense',
                'category' => 'operasional', // Service masuk operasional atau kategori khusus
                'amount' => $this->biaya_service,
                'description' => "Biaya Service {$this->hp->merk_model}: {$this->deskripsi_service}",
                'reference_type' => Hp::class,
                'reference_id' => $this->hp->id,
            ]);
        }

        // 2. Update Total Modal HP (null-safe untuk unit inputan karyawan)
        $this->hp->total_modal = ($this->hp->total_modal ?? 0) + $this->biaya_service;
        $this->hp->status = 'SERVICE'; // Ubah status jadi SERVICE sementara? Atau tetap READY?
        $this->hp->save();

        // 3. Reset Form
        $this->deskripsi_service = '';
        $this->biaya_service = '';
        $this->showServiceForm = false;

        // 4. Refresh data HP & List Stok Utama
        $this->hp->refresh();
        $this->dispatch('stok-saved'); // Refresh list stok di halaman utama
    }

    public function markAsReady()
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Hanya admin yang bisa ubah status HP.');
        }

        if ($this->hp) {
            $this->hp->update(['status' => 'READY']);
            $this->hp->refresh();
            $this->dispatch('stok-saved');
        }
    }

    public function render()
    {
        return view('livewire.detail-hp');
    }
}
