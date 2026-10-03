<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Hp;
use App\Models\Penjualan as PenjualanModel;
use App\Models\DetailPenjualan;
use App\Models\CashFlow;
use Illuminate\Support\Facades\DB;

class Penjualan extends Component
{
    public $search = '';
    public $selectedHps = []; // Array ID HP yang dipilih
    public $step = 1; // 1: Pilih Barang, 2: Checkout (Split Harga)
    
    // Mode View
    public $viewMode = 'input'; // 'input', 'history'
    public $historySearch = '';

    // Retur State
    public $detailIdToReturn = null;
    public $stokToReturn = null; // Untuk preview di modal

    // Data Checkout
    public $nama_pembeli;
    public $wa_pembeli;
    public $total_transaksi;
    public $harga_jual_items = []; // Array [hp_id => harga_jual]

    public function toggleSelection($hpId)
    {
        if (in_array($hpId, $this->selectedHps)) {
            $this->selectedHps = array_diff($this->selectedHps, [$hpId]);
        } else {
            $this->selectedHps[] = $hpId;
        }
    }

    public function nextStep()
    {
        if (empty($this->selectedHps)) {
            return; // Harus pilih minimal 1
        }
        $this->step = 2;
        
        // Inisialisasi array harga jual dengan 0
        foreach ($this->selectedHps as $id) {
            $this->harga_jual_items[$id] = 0;
        }
    }

    public function prevStep()
    {
        $this->step = 1;
    }

    // Fitur Pintar: Auto Hitung Total
    // Total transaksi dihitung otomatis dari penjumlahan harga per item
    public function updatedHargaJualItems()
    {
        // Pastikan semua nilai numerik
        $total = 0;
        foreach ($this->harga_jual_items as $harga) {
            $total += (float) $harga;
        }
        $this->total_transaksi = $total;
    }

    public function confirmReturn($detailId)
    {
        $this->detailIdToReturn = $detailId;
        $this->stokToReturn = DetailPenjualan::with('hp')->find($detailId);
        $this->dispatch('open-modal-retur');
    }

    public function processReturn()
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Hanya admin yang bisa retur barang.');
        }

        if(!$this->detailIdToReturn) return;

        $detail = DetailPenjualan::with(['penjualan', 'hp'])->find($this->detailIdToReturn);
        
        if(!$detail) {
            session()->flash('error', 'Data tidak ditemukan.');
            return;
        }

        DB::transaction(function () use ($detail) {
            $hp = $detail->hp;
            $penjualan = $detail->penjualan;
            $refundAmount = $detail->harga_jual_unit;

            // 1. Kembalikan Status HP -> READY
            if($hp) {
                $hp->update(['status' => 'READY']);
            }

            // 2. Catat Pengeluaran (Refund)
            CashFlow::create([
                'date' => now(),
                'type' => 'expense',
                'category' => 'lainnya', 
                'amount' => $refundAmount,
                'description' => "Retur: {$hp->merk_model} ({$hp->imei}). Pembeli: {$penjualan->nama_pembeli}",
            ]);

            // 3. Hapus Detail Penjualan
            $detail->delete();

            // 4. Update Header Penjualan
            if ($penjualan) {
                $penjualan->total_transaksi -= $refundAmount;
                $penjualan->save();

                // Jika penjualan kosong (semua item diretur), hapus header + referensi
                if ($penjualan->details()->count() == 0) {
                    $penjualan->delete();
                }
            }
        });

        $this->reset(['detailIdToReturn', 'stokToReturn']);
        $this->dispatch('close-modal-retur');
        session()->flash('message', 'Barang berhasil diretur & stok dikembalikan.');
    }

    public function processPenjualan()
    {
        $this->validate([
            'nama_pembeli' => 'nullable|string',
            'wa_pembeli' => 'nullable|string|max:30',
            'total_transaksi' => 'required|numeric|min:0',
            'harga_jual_items.*' => 'required|numeric|min:0',
        ]);

        // Validasi Total Item harus sama dengan Total Transaksi
        $sumItems = 0;
        foreach ($this->harga_jual_items as $val) {
            $sumItems += (float) $val;
        }
        if ($sumItems != $this->total_transaksi) {
            $this->addError('total_transaksi', 'Total rincian item (Rp '.number_format($sumItems).') tidak sama dengan Total Transaksi.');
            return;
        }

        DB::transaction(function () {
            // 1. Buat Header Penjualan
            $penjualan = PenjualanModel::create([
                'nama_pembeli' => $this->nama_pembeli,
                'wa_pembeli' => $this->wa_pembeli,
                'total_transaksi' => $this->total_transaksi,
                'tanggal_jual' => now(),
            ]);

            // 2. Buat Detail & Update Stok
            foreach ($this->selectedHps as $hpId) {
                $hp = Hp::find($hpId);
                $hargaJual = $this->harga_jual_items[$hpId];
                
                DetailPenjualan::create([
                    'penjualan_id' => $penjualan->id,
                    'hp_id' => $hp->id,
                    'modal_terakhir' => $hp->total_modal,
                    'harga_jual_unit' => $hargaJual,
                    'laba_rugi' => $hargaJual - $hp->total_modal,
                ]);

                // Update Status HP
                $hp->update(['status' => 'SOLD']);
            }

            // 3. Catat Pemasukan Kas
            CashFlow::create([
                'date' => now(),
                'type' => 'income',
                'category' => 'penjualan',
                'amount' => $this->total_transaksi,
                'description' => "Penjualan " . count($this->selectedHps) . " unit. Pembeli: {$this->nama_pembeli}",
                'reference_type' => PenjualanModel::class,
                'reference_id' => $penjualan->id,
            ]);
        });

        // Reset & Redirect
        session()->flash('message', 'Penjualan berhasil disimpan!');
        return redirect()->to('/');
    }

    public function render()
    {
        if ($this->viewMode == 'history') {
            $history = PenjualanModel::with(['details.hp'])
                ->where(function($q) {
                    $q->where('nama_pembeli', 'like', '%'.$this->historySearch.'%')
                      ->orWhereHas('details.hp', function($subQ) {
                          $subQ->where('merk_model', 'like', '%'.$this->historySearch.'%')
                               ->orWhere('imei', 'like', '%'.$this->historySearch.'%');
                      });
                })
                ->latest()
                ->take(50)
                ->get();

            return view('livewire.penjualan', [
                'history' => $history,
                'hps' => []
            ]);
        }

        $hps = [];
        if ($this->step == 1) {
            $hps = Hp::where('status', 'READY')
                ->where(function($q) {
                    $q->where('merk_model', 'like', '%'.$this->search.'%')
                      ->orWhere('imei', 'like', '%'.$this->search.'%');
                })
                ->get();
        } else {
            // Load data HP yang dipilih untuk step 2
            $hps = Hp::whereIn('id', $this->selectedHps)->get();
        }

        return view('livewire.penjualan', [
            'hps' => $hps
        ]);
    }
}
