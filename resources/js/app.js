import './bootstrap';
import Alpine from 'alpinejs';
import { initEcho } from './echo';

window.Alpine = Alpine;
Alpine.start();

// Inisialisasi Echo/Reverb di background; aman bila ENV/package belum siap.
initEcho();
