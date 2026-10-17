/**
 * Driver Niimbot B1 (label 50x30mm) via Web Bluetooth.
 *
 * Protokol Niimbot (dari @mmote/niimbluelib, reverse-engineered & dipakai niimblue):
 *   - BLE service UUID e7810a71-73ae-499d-8c15-faa9aef0c3f2,
 *     characteristic dengan properties notify + writeWithoutResponse.
 *   - Frame: 55 55 [cmd] [len] [data...] [crc] aa aa  (crc = XOR cmd^len^semua data)
 *   - Connect (cmd 0xc1) harus diberi prefix byte 0x03.
 *   - Urutan print task B1:
 *       SetDensity -> SetLabelType -> PrintStart(u16 totalPages + 00000000 + pageColor)
 *       -> PageStart -> SetPageSize(u16 rows, u16 cols, u16 qty) [wajib 6 byte utk B1]
 *       -> bitmap rows (PrintBitmapRow 0x85 / PrintEmptyRow 0x84) -> PageEnd
 *       -> poll PrintStatus sampai selesai (timeout 8s) -> PrintEnd.
 *
 * API (dipakai label-print.js):
 *   const printer = await NiimbotPrinter.connect();
 *   await NiimbotPrinter.printLabel(printer, [{ model, code }]);
 *   NiimbotPrinter.disconnect(printer);
 */
import JsBarcode from 'jsbarcode';

const NiimbotPrinter = (() => {
    const SERVICE_UUID = 'e7810a71-73ae-499d-8c15-faa9aef0c3f2';

    // Command ID (TX = client -> printer, RX = printer -> client)
    const TX = {
        Connect: 0xc1, PrinterInfo: 0x40, Heartbeat: 0xdc, SetDensity: 0x21,
        SetLabelType: 0x23, PrintStart: 0x01, SetPageSize: 0x13, PageStart: 0x03,
        PrintBitmapRow: 0x85, PrintEmptyRow: 0x84, PageEnd: 0xe3, PrintEnd: 0xf3,
        PrintStatus: 0xa3, PrintClear: 0x20,
    };
    const RX = {
        In_Connect: 0xc2, In_PrinterStatusData: 0xb5, In_PrintStatus: 0xb3,
        In_PageEnd: 0xe4, In_PrinterPageIndex: 0xe0, In_HeartbeatBasic: 0xde,
        In_PrintError: 0xdb, In_ResetTimeout: 0xc6,
    };

    const PACKET_INTERVAL_MS = 10;
    const STATUS_POLL_MS = 100;
    const STATUS_TIMEOUT_MS = 15000;
    // B1: 203 DPI, printhead 384px, printDirection "top" -> width harus multiple of 8.
    const CANVAS_WIDTH = 384;   // 48 mm @ 8 px/mm (area cetak label 50mm)
    const CANVAS_HEIGHT = 240;  // 30 mm

    // ===== Util =====
    const u16be = (v) => [(v >> 8) & 0xff, v & 0xff];

    function buildPacket(cmd, data = []) {
        const crc = [cmd, data.length, ...data].reduce((a, b) => a ^ b, 0);
        return [0x55, 0x55, cmd, data.length, ...data, crc, 0xaa, 0xaa];
    }

    /** Packet Connect butuh prefix 0x03 sebelum frame. */
    function buildConnectPacket() {
        return [0x03, ...buildPacket(TX.Connect, [0x01])];
    }

    const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

    // ===== Frame parser dari notification stream =====
    function makeParser(onPacket) {
        let buf = [];
        return {
            feed(byte) {
                buf.push(byte);
                // Minimal 8 byte; cari header 5555 dan footer aaaa
                while (buf.length >= 8) {
                    if (buf[0] !== 0x55 || buf[1] !== 0x55) { buf.shift(); continue; }
                    const len = buf[3];
                    const total = len + 8;
                    if (buf.length < total) return; // tunggu sisa frame
                    if (buf[total - 2] === 0xaa && buf[total - 1] === 0xaa) {
                        const pkt = { cmd: buf[2], data: buf.slice(4, 4 + len) };
                        buf = buf.slice(total);
                        onPacket(pkt);
                    } else {
                        buf.shift(); // footer salah -> buang 1 byte, cari header lagi
                    }
                    if (buf.length >= 8 && buf[0] === 0x55 && buf[1] === 0x55) continue;
                    return;
                }
            },
        };
    }

    // ===== Rasterisasi label ke canvas 1-bit =====
    // [model, code] -> canvas 384x240: nama model (wrap 2 baris), barcode Code128, kode 4 digit.
    async function renderLabel(item) {
        const canvas = document.createElement('canvas');
        canvas.width = CANVAS_WIDTH;
        canvas.height = CANVAS_HEIGHT;
        const ctx = canvas.getContext('2d');

        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, CANVAS_WIDTH, CANVAS_HEIGHT);
        ctx.fillStyle = '#000000';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        // Nama model: muat 2 baris, perkecil font sampai muat per baris (max 460px).
        ctx.font = 'bold 26px "Plus Jakarta Sans", Arial, sans-serif';
        const words = String(item.model).trim().split(/\s+/);
        const lines = [];
        let cur = '';
        for (const w of words) {
            const cand = cur ? `${cur} ${w}` : w;
            if (ctx.measureText(cand).width > 460 && cur) { lines.push(cur); cur = w; }
            else cur = cand;
        }
        if (cur) lines.push(cur);
        let fontSize = 26;
        while (lines.length > 2 || lines.some((l) => ctx.measureText(l).width > 460)) {
            fontSize -= 2;
            ctx.font = `bold ${fontSize}px "Plus Jakarta Sans", Arial, sans-serif`;
            if (fontSize <= 12) break;
        }
        const showLines = lines.slice(0, 2);
        showLines.forEach((line, i) => {
            ctx.fillText(line, CANVAS_WIDTH / 2, 22 + i * (fontSize + 6));
        });

        // Barcode Code128 dari kode 4 digit.
        try {
            JsBarcode(canvas, String(item.code), {
                format: 'CODE128',
                width: 2,
                height: 88,
                displayValue: false,
                margin: 0,
                flat: true,
            });
        } catch (e) {
            console.warn('JsBarcode gagal:', e);
        }

        // Kode 4 digit besar di bawah barcode.
        ctx.font = 'bold 44px "Plus Jakarta Sans", Arial, sans-serif';
        ctx.fillText(String(item.code), CANVAS_WIDTH / 2, 206);

        return canvas;
    }

    // ===== Klasifikasi bitmap =====
    // Konversi ImageData jadi per-baris: { black pixels count, packed bits (1=hitam, MSB first) }
    function encodeRows(canvas) {
        const ctx = canvas.getContext('2d');
        const { width, height } = canvas;
        const img = ctx.getImageData(0, 0, width, height).data;
        const rows = [];
        const bytesPerRow = width / 8;

        for (let y = 0; y < height; y++) {
            const bits = new Uint8Array(bytesPerRow);
            let black = 0;
            for (let x = 0; x < width; x++) {
                const i = (y * width + x) * 4;
                const lum = 0.299 * img[i] + 0.587 * img[i + 1] + 0.114 * img[i + 2];
                if (lum < 128) {
                    black++;
                    bits[x >> 3] |= 0x80 >> (x & 7);
                }
            }
            rows.push({ pos: y, black, bits });
        }
        return { rows, bytesPerRow };
    }

    // ===== Protokol =====
    class B1Session {
        constructor(char) {
            this.char = char;
            this.queue = Promise.resolve();
            this.pending = []; // parser antrean packet masuk
            this.parser = makeParser((pkt) => {
                const w = this.pending.shift();
                if (w) w(pkt);
            });
            this.listener = (event) => {
                const dv = event.target.value;
                for (let i = 0; i < dv.byteLength; i++) this.parser.feed(dv.getUint8(i));
            };
        }

        /** Kirim frame dan tunggu packet balasan pertama. */
        send(cmd, data, timeoutMs = 2000) {
            const run = async () => {
                const frame = new Uint8Array(buildPacket(cmd, data));
                const p = this._waitPacket(timeoutMs);
                await sleep(PACKET_INTERVAL_MS);
                await this.char.writeValueWithoutResponse(frame);
                const pkt = await p;
                return pkt;
            };
            this.queue = this.queue.then(run, run);
            return this.queue;
        }

        _waitPacket(timeoutMs) {
            return new Promise((resolve, reject) => {
                const entry = { resolve, reject };
                this.pending.push(entry);
                setTimeout(() => {
                    const idx = this.pending.indexOf(entry);
                    if (idx !== -1) {
                        this.pending.splice(idx, 1);
                        reject(new Error('Timeout menunggu respon printer'));
                    }
                }, timeoutMs);
            });
        }

        /** Kirim tanpa menunggu balasan (bitmap rows bisa banyak). */
        async sendOneWay(cmd, data) {
            const frame = new Uint8Array(buildPacket(cmd, data));
            await sleep(PACKET_INTERVAL_MS);
            await this.char.writeValueWithoutResponse(frame);
        }

        /** Kirim frame mentah tanpa menunggu balasan (untuk handshake Connect). */
        async sendRawFrame(frame) {
            await sleep(PACKET_INTERVAL_MS);
            await this.char.writeValueWithoutResponse(new Uint8Array(frame));
        }

        startNotify() { this.char.addEventListener('characteristicvaluechanged', this.listener); }
        stopNotify() { try { this.char.removeEventListener('characteristicvaluechanged', this.listener); } catch (e) { /* noop */ } }
    }

    // ===== Public API =====
    async function connect() {
        if (!navigator.bluetooth) {
            throw new Error('Browser tidak mendukung Web Bluetooth. Gunakan Chrome/Edge (desktop atau Android).');
        }

        const device = await navigator.bluetooth.requestDevice({
            filters: [{ services: [SERVICE_UUID] }],
            optionalServices: [SERVICE_UUID],
        });

        const server = await device.gatt.connect();

        // Cari characteristic: notify + writeWithoutResponse (pola niimbluelib).
        const services = await server.getPrimaryServices();
        let char = null;
        for (const svc of services) {
            for (const c of await svc.getCharacteristics()) {
                if (c.properties.notify && c.properties.writeWithoutResponse) { char = c; break; }
            }
            if (char) break;
        }
        if (!char) {
            server.disconnect();
            throw new Error('Characteristic printer tidak ditemukan.');
        }

        const session = new B1Session(char);
        session.startNotify();

        // Handshake: Connect (prefix 0x03) -> In_Connect (0xc2)
        const hello = session._waitPacket(3000).catch(() => null);
        await session.sendRawFrame(buildConnectPacket());
        const reply = await hello;
        if (!reply || reply.cmd !== RX.In_Connect) {
            // Tidak fatal — lanjut, beberapa firmware jarang membalas handshake pertama.
            console.warn('Handshake Niimbot: In_Connect tidak diterima, lanjut.');
        }

        return { device, server, session };
    }

    async function printLabel(printer, items) {
        const { session } = printer;

        for (const item of items) {
            const canvas = await renderLabel(item);
            const { rows, bytesPerRow } = encodeRows(canvas);

            // printInit: SetDensity(3) -> SetLabelType(WithGaps=1) -> PrintStart(7b)
            await session.send(TX.SetDensity, [3]);
            await session.send(TX.SetLabelType, [1]);
            await session.send(TX.PrintStart, [...u16be(1), 0x00, 0x00, 0x00, 0x00, 0x00]);

            // printPage: PageStart -> SetPageSize6b -> rows -> PageEnd
            await session.send(TX.PageStart);
            await session.send(TX.SetPageSize, [...u16be(canvas.height), ...u16be(canvas.width), ...u16be(1)]);

            for (const row of rows) {
                if (row.black === 0) {
                    if (row.pos % 8 === 0) await session.sendOneWay(TX.PrintEmptyRow, [...u16be(row.pos), 8]);
                } else {
                    // PrintBitmapRow: u16 pos, u24 hitam (BE), repeats=1, packed bits.
                    const count = [(row.black >> 16) & 0xff, (row.black >> 8) & 0xff, row.black & 0xff];
                    await session.sendOneWay(TX.PrintBitmapRow, [...u16be(row.pos), ...count, 0x01, ...row.bits]);
                }
            }
            await session.send(TX.PageEnd);

            // waitForFinished: poll PrintStatus (0xa3 -> In_PrintStatus 0xb3)
            const deadline = Date.now() + STATUS_TIMEOUT_MS;
            let done = false;
            while (Date.now() < deadline && !done) {
                await sleep(STATUS_POLL_MS);
                try {
                    const st = await session.send(TX.PrintStatus, []);
                    // data: page(1), pagePrintProgress(1), pageFeedProgress(1), error(1)
                    if (st && st.cmd === RX.In_PrintStatus) {
                        if (st.data[3] !== 0) {
                            throw new Error(`Printer error 0x${st.data[3].toString(16)}`);
                        }
                        if (st.data[1] >= 100 && st.data[2] >= 100) done = true;
                    }
                } catch (e) {
                    if (String(e.message).includes('Printer error')) throw e;
                    // timeout tunggu status -> coba lagi sampai deadline
                }
            }
            if (!done) throw new Error('Cetak tidak selesai (timeout). Pastikan kertas terpasang & tutup tertutup.');
        }

        // printEnd
        await session.send(TX.PrintEnd, []);
    }

    function disconnect(printer) {
        try {
            printer.session.stopNotify();
            printer.server.disconnect();
        } catch (e) {
            // ignore
        }
    }

    return { connect, printLabel, disconnect };
})();

window.NiimbotPrinter = NiimbotPrinter;
