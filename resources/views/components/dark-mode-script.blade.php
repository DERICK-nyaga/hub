<script>
function ensureFullPageDarkMode() {
    if (document.documentElement.classList.contains('dark-mode')) {
        document.body.style.backgroundColor = '#1a1a1a';
        document.body.style.color = '#e5e5e5';
        
        const allElements = document.querySelectorAll('*');
        allElements.forEach(el => {
            const bgColor = window.getComputedStyle(el).backgroundColor;
            if (bgColor === 'rgb(255, 255, 255)' || bgColor === 'white' || bgColor === '#fff') {
                el.style.backgroundColor = '#2d2d2d';
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', ensureFullPageDarkMode);

const observer = new MutationObserver(() => {
    if (document.documentElement.classList.contains('dark-mode')) {
        ensureFullPageDarkMode();
    }
});
observer.observe(document.body, { childList: true, subtree: true });
</script>