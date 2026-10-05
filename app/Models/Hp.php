<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hp extends Model
{
    protected $fillable = [
        'imei',
        'merk_model',
        'warna',
        'keterangan_minus',
        'sumber_beli',
        'harga_beli_awal',
        'total_modal',
        'status',
        'label_printed_at',
        'ditambahkan_oleh',
    ];

    protected function casts(): array
    {
        return [
            'label_printed_at' => 'datetime',
        ];
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /** User (admin/karyawan) yang menambahkan unit ini ke stok. */
    public function pelacak()
    {
        return $this->belongsTo(User::class, 'ditambahkan_oleh');
    }

    /** True jika harga modal belum diisi (biasanya inputan karyawan). */
    public function modalBelumDiisi(): bool
    {
        return is_null($this->total_modal) || is_null($this->harga_beli_awal);
    }

    public function cashFlow()
    {
        return $this->morphOne(CashFlow::class, 'reference');
    }

    protected static function booted()
    {
        static::deleting(function ($hp) {
            // 1. Hapus transaksi pembelian stok (CashFlow)
            if ($hp->cashFlow) {
                $hp->cashFlow->delete();
            }

            // 2. Hapus service satu per satu agar event deleting di Service model jalan
            // (sehingga CashFlow service juga terhapus)
            foreach ($hp->services as $service) {
                $service->delete();
            }
        });
    }
}
