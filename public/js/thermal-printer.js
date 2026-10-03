/**
 * ESC/POS helper untuk cetak label barcode via printer thermal Bluetooth (58mm).
 * Web Bluetooth API (Chrome/Edge desktop & Android Chrome).
 *
 * Cara pakai:
 *   const printer = await ThermalPrinter.connect();
 *   await ThermalPrinter.printLabel(printer, [{ model: 'iPhone 11', code: '1234' }]);
 */

const ESC = '\x1B';
const GS = '\x1D';

/** Barcode CODE128(B) — 4 digit terakhir IMEI. */
function code128B(text) {
    return `${GS}h80${GS}w3${GS}kE${String.fromCharCode(text.length)}${text}\x00`;
}

/** Barcode CODE39 — fallback bila printer tak dukung CODE128 (karakter A-Z, 0-9). */
function code39(text) {
    return `${GS}kA${String.fromCharCode(text.length)}${text}\x00`;
}

const ThermalPrinter = {
    SERVICE_UUID: '0000ff00-0000-1000-8000-00805f9b34fb',
    CHAR_UUID: '0000ff02-0000-1000-8000-00805f9b34fb',

    async connect() {
        if (!navigator.bluetooth) {
            throw new Error('Browser tidak mendukung Web Bluetooth. Gunakan Chrome/Edge (desktop atau Android).');
        }

        const device = await navigator.bluetooth.requestDevice({
            filters: [{ services: [this.SERVICE_UUID] }],
            optionalServices: [this.SERVICE_UUID],
        });

        const server = await device.gatt.connect();
        const service = await server.getPrimaryService(this.SERVICE_UUID);
        const char = await service.getCharacteristic(this.CHAR_UUID);

        return { device, server, char };
    },

    /** Enkode string perintah ke bytes dan kirim bertahap (chunk 180 bytes). */
    async send(printer, commands) {
        const encoder = new TextEncoder();
        const bytes = encoder.encode(commands);

        for (let i = 0; i < bytes.length; i += 180) {
            const chunk = bytes.slice(i, i + 180);
            await printer.char.writeValueWithoutResponse(chunk);
        }
    },

    init(printer) {
        return this.send(printer, `${ESC}@`); // reset printer
    },

    /** Cetak label: [model, kode] */
    async printLabel(printer, items) {
        let cmds = `${ESC}@`; // init
        cmds += `${ESC}a\x01`; // align center

        for (const item of items) {
            const model = item.model.slice(0, 24);
            const code = item.code;

            // Nama HP, bold
            cmds += `${ESC}!\x30${model}\n`;

            // Barcode CODE128
            cmds += code128B(code) + '\n';

            // Kode 4 digit besar (double width + double height)
            cmds += `${ESC}!\x60${code}\n`;

            // Feed & cut
            cmds += `\n\n`;
        }

        cmds += `\n\n\n`;
        await this.send(printer, cmds);
    },

    disconnect(printer) {
        try {
            printer.server.disconnect();
        } catch (e) {
            // ignore
        }
    },
};

window.ThermalPrinter = ThermalPrinter;
