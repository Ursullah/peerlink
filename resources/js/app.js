import './bootstrap';
import Alpine from 'alpinejs';
import theme from './theme';

Alpine.data('theme', theme);
window.Alpine = Alpine;
Alpine.start();