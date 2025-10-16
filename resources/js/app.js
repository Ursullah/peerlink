import './bootstrap';
import Alpine from 'alpinejs';
import theme from './theme';
import './animations';

Alpine.data('theme', theme);
window.Alpine = Alpine;
Alpine.start();