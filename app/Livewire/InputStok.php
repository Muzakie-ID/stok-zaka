<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Hp;
use App\Models\CashFlow;
use Illuminate\Support\Facades\DB;

class InputStok extends Component
{
    public $mode = 'satuan'; // 'satuan' atau 'borongan'
    
    // Form Fields
    public $imei;
    public $merk_model;
    public $warna;
    public $keterangan_minus;
    public $harga_beli_awal;
    public $sumber_beli;
    public $sudah_bayar = false; // Checkbox untuk Stok Pending

    // Bulk Data
    public $bulkItems = []; // Array of items
    public $total_borongan = 0;

    private function authorizeAdmin(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Hanya admin yang bisa mengelola stok.');
        }
    }

    protected $rules = [
        'imei' => 'required|unique:hps,imei',
        'merk_model' => 'required',
        'warna' => 'nullable|string',
        'keterangan_minus' => 'nullable|string',
        'harga_beli_awal' => 'required|numeric|min:0',
        'sumber_beli' => 'nullable|string',
    ];

    public function setMode($mode)
    {
        $this->mode = $mode;
        $this->reset(['imei', 'merk_model', 'warna', 'keterangan_minus', 'harga_beli_awal', 'bulkItems', 'total_borongan', 'sudah_bayar']);
    }

    // --- Logic Satuan ---
    public function save()
    {
        $this->authorizeAdmin();
        $this->validate();

        DB::transaction(function () {
            $hp = Hp::create([
                'imei' => $this->imei,
                'merk_model' => $this->merk_model,
                'warna' => $this->warna,
                'keterangan_minus' => $this->keterangan_minus,
                'harga_beli_awal' => $this->harga_beli_awal,
                'total_modal' => $this->harga_beli_awal,
                'sumber_beli' => $this->sumber_beli,
                'status' => 'READY',
            ]);

            // Catat Pengeluaran Kas (Hanya jika belum dibayar sebelumnya)
            if (!$this->sudah_bayar) {
                CashFlow::create([
                    'date' => now(),
                    'type' => 'expense',
                    'category' => 'stok',
                    'amount' => $this->harga_beli_awal,
                    'description' => "Beli Stok: {$this->merk_model} ({$this->imei}) dari {$this->sumber_beli}",
                    'reference_type' => Hp::class,
                    'reference_id' => $hp->id,
                ]);
            }
        });

        $this->reset(['imei', 'merk_model', 'warna', 'keterangan_minus', 'harga_beli_awal', 'sumber_beli', 'sudah_bayar']);
        
        $this->dispatch('stok-saved'); 
        $this->dispatch('close-modal');
    }

    // --- Logic Borongan ---
    public function addBulkItem()
    {
        $this->authorizeAdmin();

        $this->validate([
            'imei' => 'required|unique:hps,imei',
            'merk_model' => 'required',
            'warna' => 'nullable|string',
            'keterangan_minus' => 'nullable|string',
            'harga_beli_awal' => 'required|numeric|min:0',
        ]);

        // Cek duplikasi IMEI di list sementara
        foreach ($this->bulkItems as $item) {
            if ($item['imei'] == $this->imei) {
                $this->addError('imei', 'IMEI ini sudah ada di daftar antrian.');
                return;
            }
        }

        $this->bulkItems[] = [
            'imei' => $this->imei,
            'merk_model' => $this->merk_model,
            'warna' => $this->warna,
            'keterangan_minus' => $this->keterangan_minus,
            'harga_beli_awal' => $this->harga_beli_awal,
        ];

        $this->calculateTotalBorongan();
        $this->reset(['imei', 'merk_model', 'warna', 'keterangan_minus', 'harga_beli_awal']);
    }

    public function removeBulkItem($index)
    {
        unset($this->bulkItems[$index]);
        $this->bulkItems = array_values($this->bulkItems); // Re-index
        $this->calculateTotalBorongan();
    }

    public function calculateTotalBorongan()
    {
        $total = 0;
        foreach ($this->bulkItems as $item) {
            $total += (float) ($item['harga_beli_awal'] ?? 0);
        }
        $this->total_borongan = $total;
    }

    public function saveBulk()
    {
        $this->authorizeAdmin();

        $this->validate([
            'sumber_beli' => 'required|string',
            'bulkItems' => 'required|array|min:1',
        ]);

        DB::transaction(function () {
            foreach ($this->bulkItems as $item) {
                Hp::create([
                    'imei' => $item['imei'],
                    'merk_model' => $item['merk_model'],
                    'warna' => $item['warna'] ?? null,
                    'keterangan_minus' => $item['keterangan_minus'] ?? null,
                    'harga_beli_awal' => $item['harga_beli_awal'],
                    'total_modal' => $item['harga_beli_awal'],
                    'sumber_beli' => $this->sumber_beli,
                    'status' => 'READY',
                ]);
            }

            // Catat Pengeluaran Kas (Total Borongan) - Hanya jika belum lunas
            if (!$this->sudah_bayar) {
                CashFlow::create([
                    'date' => now(),
                    'type' => 'expense',
                    'category' => 'stok',
                    'amount' => $this->total_borongan,
                    'description' => "Beli Stok Borongan (" . count($this->bulkItems) . " unit) dari {$this->sumber_beli}",
                ]);
            }
        });

        $this->reset(['imei', 'merk_model', 'warna', 'keterangan_minus', 'harga_beli_awal', 'sumber_beli', 'bulkItems', 'total_borongan', 'sudah_bayar']);
        
        $this->dispatch('stok-saved'); 
        $this->dispatch('close-modal');
    }

    public function render()
    {
        return view('livewire.input-stok');
    }
}
