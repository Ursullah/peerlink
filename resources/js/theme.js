export default () => ({
    dark: false,

    init() {
        this.dark = JSON.parse(localStorage.getItem('dark')) || 
                    (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
        
        this.$watch('dark', val => {
            localStorage.setItem('dark', val);
            if (val) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        });
    },

    toggle() {
        this.dark = !this.dark;
    }
});