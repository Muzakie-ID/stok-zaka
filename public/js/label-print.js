/**
 * Logika cetak label barcode (halaman /label).
 * Dimuat via @assets agar dieksekusi Livewire sekali per halaman penuh.
 * Sumber data: elemen #print-area yang dirender server sesuai unit terpilih.
 */

function labelItems() {
    const area = document.getElementById('print-area');
    if (!area) return [];

    return [...area.querySelectorAll('.label-item')].map(item => ({
        model: item.querySelector('.label-model')?.textContent.trim() || '',
        barcodeSvg: item.querySelector('.label-barcode')?.innerHTML || '',
        code: item.querySelector('.label-code')?.textContent.trim() || '',
    }));
}

/** Beri tahu komponen Livewire bahwa label terpilih sudah dicetak. */
function markPrinted() {
    try {
        // Klik tombol trigger hidden (wire:click="tandaiSudahCetak") — mekanisme
        // yang sama dengan tombol lain di halaman ini, terbukti andal.
        document.getElementById('mark-printed-trigger')?.click();
    } catch (e) {
        console.warn('Gagal menandai label dicetak:', e);
    }
}

// ===== Print A4 via dialog print browser =====
function printLabels() {
    const items = labelItems();
    if (!items.length) {
        alert('Pilih unit dulu.');
        return;
    }

    const rows = items.map(item => `
        <div class="label-item">
            <div class="label-model">${item.model}</div>
            <div class="label-barcode">${item.barcodeSvg}</div>
            <div class="label-code">${item.code}</div>
        </div>`).join('');

    const w = window.open('', '_blank', 'width=800,height=600');
    if (!w) {
        alert('Popup diblokir browser. Izinkan popup untuk mencetak.');
        return;
    }

    w.document.write(printHtml(rows));
    w.document.close();
    w.focus();
    w.print();

    // Dialog print sudah lewat (atau ditutup) → tandai sudah dicetak
    markPrinted();
}

function printHtml(rows) {
    return [
        '<html><head><title>Print Label</title><style>',
        '@page { size: A4; margin: 8mm; }',
        "body { font-family: 'Plus Jakarta Sans', Arial, sans-serif; margin: 0; }",
        '.label-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4mm; }',
        '.label-item { border: 1px dashed #bbb; border-radius: 3mm; padding: 3mm 2mm; text-align: center;',
        '  page-break-inside: avoid; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 30mm; }',
        '.label-model { font-size: 9pt; font-weight: 700; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }',
        '.label-barcode svg { max-width: 90%; height: 14mm; }',
        '.label-code { font-size: 13pt; font-weight: 800; letter-spacing: 3px; font-family: monospace; }',
        '</style></head><body>',
        '<div class="label-grid">' + rows + '</div>',
        '</body></html>',
    ].join('\n');
}

// ===== Cetak Bluetooth thermal (ESC/POS 58mm) via Web Bluetooth =====
async function printBluetooth() {
    const items = labelItems();
    if (!items.length) {
        alert('Pilih unit dulu.');
        return;
    }

    if (typeof window.ThermalPrinter === 'undefined') {
        alert('Modul printer belum termuat. Muat ulang halaman.');
        return;
    }

    try {
        const printer = await ThermalPrinter.connect();
        await ThermalPrinter.printLabel(printer, items.map(i => ({ model: i.model, code: i.code })));
        ThermalPrinter.disconnect(printer);
        markPrinted(); // cetak bluetooth sukses → tandai
    } catch (err) {
        alert('Gagal cetak: ' + (err.message || err));
    }
}

window.printLabels = printLabels;
window.printBluetooth = printBluetooth;
