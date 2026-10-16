import './bootstrap';
import { BrowserMultiFormatReader } from '@zxing/browser';
import { BarcodeFormat, DecodeHintType, NotFoundException } from '@zxing/library';

window.BrowserMultiFormatReader = BrowserMultiFormatReader;
window.BarcodeFormat = BarcodeFormat;
window.DecodeHintType = DecodeHintType;
window.NotFoundException = NotFoundException;
