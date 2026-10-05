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

    /** Karyawan hanya input data unit, tanpa harga modal. */
    public function isKaryawan(): bool
    {
        return ! auth()->user()?->isAdmin();
    }

    protected function rules(): array
    {
        $rules = [
            'imei' => 'required|unique:hps,imei',
            'merk_model' => 'required',
            'warna' => 'nullable|string',
            'keterangan_minus' => 'nullable|string',
        ];

        if (! $this->isKaryawan()) {
            $rules['harga_beli_awal'] = 'required|numeric|min:0';
            $rules['sumber_beli'] = 'nullable|string';
        }

        return $rules;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
        $this->reset(['imei', 'merk_model', 'warna', 'keterangan_minus', 'harga_beli_awal', 'bulkItems', 'total_borongan', 'sudah_bayar']);
    }

    // --- Logic Satuan ---
    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            $hp = Hp::create([
                'imei' => $this->imei,
                'merk_model' => $this->merk_model,
                'warna' => $this->warna,
                'keterangan_minus' => $this->keterangan_minus,
                'harga_beli_awal' => $this->isKaryawan() ? null : $this->harga_beli_awal,
                'total_modal' => $this->isKaryawan() ? null : $this->harga_beli_awal,
                'sumber_beli' => $this->isKaryawan() ? null : $this->sumber_beli,
                'status' => 'READY',
                'ditambahkan_oleh' => auth()->id(),
            ]);

            // Catat Pengeluaran Kas (Hanya jika belum dibayar sebelumnya)
            if (! $this->isKaryawan() && !$this->sudah_bayar) {
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

        session()->flash('message', $this->isKaryawan()
            ? 'Stok tersimpan. Menunggu admin melengkapi harga modal.'
            : 'Stok berhasil disimpan.');
        $this->dispatch('stok-saved');
        $this->dispatch('close-modal');
    }

    // --- Logic Borongan ---
    public function addBulkItem()
    {
        $this->validate([
            'imei' => 'required|unique:hps,imei',
            'merk_model' => 'required',
            'warna' => 'nullable|string',
            'keterangan_minus' => 'nullable|string',
        ] + ($this->isKaryawan() ? [] : [
            'harga_beli_awal' => 'required|numeric|min:0',
        ]));

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
            'harga_beli_awal' => $this->isKaryawan() ? null : $this->harga_beli_awal,
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
        $this->validate([
            'bulkItems' => 'required|array|min:1',
        ] + ($this->isKaryawan() ? [] : [
            'sumber_beli' => 'required|string',
        ]));

        DB::transaction(function () {
            foreach ($this->bulkItems as $item) {
                Hp::create([
                    'imei' => $item['imei'],
                    'merk_model' => $item['merk_model'],
                    'warna' => $item['warna'] ?? null,
                    'keterangan_minus' => $item['keterangan_minus'] ?? null,
                    'harga_beli_awal' => $this->isKaryawan() ? null : ($item['harga_beli_awal'] ?? null),
                    'total_modal' => $this->isKaryawan() ? null : ($item['harga_beli_awal'] ?? null),
                    'sumber_beli' => $this->isKaryawan() ? null : $this->sumber_beli,
                    'status' => 'READY',
                    'ditambahkan_oleh' => auth()->id(),
                ]);
            }


            // Catat Pengeluaran Kas (Total Borongan) - Hanya jika belum lunas
            if (! $this->isKaryawan() && !$this->sudah_bayar) {
                CashFlow::create([
                    'date' => now(),
                    'type' => 'expense',
                    'category' => 'stok',
                    'amount' => $this->total_borongan,
                    'description' => "Beli Stok Borongan (" . count($this->bulkItems) . " unit) dari {$this->sumber_beli}",
                ]);
            }
        });

        $jumlahItem = count($this->bulkItems);
        $this->reset(['imei', 'merk_model', 'warna', 'keterangan_minus', 'harga_beli_awal', 'sumber_beli', 'bulkItems', 'total_borongan', 'sudah_bayar']);

        session()->flash('message', $this->isKaryawan()
            ? "{$jumlahItem} stok tersimpan. Menunggu admin melengkapi harga modal."
            : 'Stok borongan berhasil disimpan.');
        $this->dispatch('stok-saved');
        $this->dispatch('close-modal');
    }

    public function render()
    {
        return view('livewire.input-stok');
    }
}
