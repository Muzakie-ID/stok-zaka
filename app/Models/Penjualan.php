<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $fillable = ['nama_pembeli', 'wa_pembeli', 'total_transaksi', 'tanggal_jual', 'user_id'];

    /**
     * Nomor WhatsApp pembeli dalam format internasional (628xxx).
     */
    public function getWaLinkAttribute(): ?string
    {
        $raw = preg_replace('/\D/', '', $this->wa_pembeli ?? '');

        if ($raw === '') {
            return null;
        }

        // Konversi 08xx / 8xx menjadi 628xx
        if (str_starts_with($raw, '0')) {
            $raw = '62' . substr($raw, 1);
        } elseif (str_starts_with($raw, '8')) {
            $raw = '62' . $raw;
        }

        return "https://wa.me/{$raw}";
    }

    public function details()
    {
        return $this->hasMany(DetailPenjualan::class, 'penjualan_id');
    }

    /** User (admin/karyawan) yang mencatat penjualan ini. */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
