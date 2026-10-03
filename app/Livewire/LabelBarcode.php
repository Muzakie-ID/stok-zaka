<?php

namespace App\Livewire;

use App\Models\Hp;
use Illuminate\Support\Facades\Blade;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Picqer\Barcode\BarcodeGeneratorSVG;

#[Layout('components.layouts.app')]
#[Title('Label Barcode')]
class LabelBarcode extends Component
{
    /** @var array<int> */
    public array $selected = [];

    public string $search = '';

    /** Unit yang penanda cetaknya akan dihapus (modal konfirmasi). */
    public ?Hp $resetTarget = null;

    public bool $showResetModal = false;

    public function mount(): void
    {
        $this->resetTarget = null;
    }

    public function render()
    {
        $hps = Hp::query()
            ->where('status', 'READY')
            ->when($this->search !== '', fn ($q) => $q->where(fn ($sub) => $sub
                ->where('merk_model', 'like', '%'.$this->search.'%')
                ->orWhere('imei', 'like', '%'.$this->search.'%')))
            ->orderByDesc('created_at')
            ->take(100)
            ->get();

        return view('livewire.label-barcode', [
            'hps' => $hps,
        ]);
    }

    public function toggle(int $id): void
    {
        if (($key = array_search($id, $this->selected)) !== false) {
            unset($this->selected[$key]);
        } else {
            $this->selected[] = $id;
        }

        $this->selected = array_values($this->selected);
    }

    public function pilihSemua(): void
    {
        $this->selected = Hp::where('status', 'READY')
            ->when($this->search !== '', fn ($q) => $q->where(fn ($sub) => $sub
                ->where('merk_model', 'like', '%'.$this->search.'%')
                ->orWhere('imei', 'like', '%'.$this->search.'%')))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function resetPilihan(): void
    {
        $this->selected = [];
    }

    /** Tandai unit terpilih sebagai label sudah dicetak (dipicu dari JS setelah print sukses). */
    #[On('mark-label-printed')]
    public function tandaiSudahCetak(): void
    {
        if (empty($this->selected)) {
            return;
        }

        Hp::whereIn('id', $this->selected)
            ->update(['label_printed_at' => now()]);

        $this->dispatch('label-printed');
    }

    /** Buka modal konfirmasi hapus penanda cetak. */
    public function konfirmasiReset(int $id): void
    {
        $this->resetTarget = Hp::find($id);
        $this->showResetModal = $this->resetTarget !== null;
    }

    public function tutupResetModal(): void
    {
        $this->showResetModal = false;
        $this->resetTarget = null;
    }

    /** Hapus penanda "sudah dicetak" (mis. label hilang / mau cetak ulang). */
    public function resetCetak(): void
    {
        if ($this->resetTarget === null) {
            return;
        }

        Hp::where('id', $this->resetTarget->id)->update(['label_printed_at' => null]);

        $this->tutupResetModal();
    }

    /** SVG barcode Code128 dari 4 digit terakhir IMEI. */
    public function barcodeSvg(string $imei): string
    {
        $code = substr(preg_replace('/[^0-9]/', '', $imei), -4);

        $generator = new BarcodeGeneratorSVG;

        return $generator->getBarcode($code, $generator::TYPE_CODE_128, 2, 40);
    }

    public static function kodeLabel(string $imei): string
    {
        return substr(preg_replace('/[^0-9]/', '', $imei), -4);
    }
}
