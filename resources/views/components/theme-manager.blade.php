<script>
// Enhanced Theme Manager with Cache Clearing
class ThemeManager {
    constructor() {
        console.log('ThemeManager: Initializing...');
        
        this.currentTheme = localStorage.getItem('theme') || 'light';
        this.currentBrightness = parseInt(localStorage.getItem('brightness') || '100');
        
        // Apply settings immediately
        this.applyThemeImmediately(this.currentTheme);
        this.applyBrightnessImmediately(this.currentBrightness);
        
        // Initialize
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.updateUI();
                this.loadUserPreferences();
                this.setupEventListeners();
            });
        } else {
            this.updateUI();
            this.loadUserPreferences();
            this.setupEventListeners();
        }
    }

    applyThemeImmediately(theme) {
        console.log('Applying theme:', theme);
        const htmlElement = document.documentElement;
        const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        
        htmlElement.classList.remove('light-mode', 'dark-mode');
        
        if (isDark) {
            htmlElement.classList.add('dark-mode');
            htmlElement.setAttribute('data-theme', 'dark');
            document.body.setAttribute('data-theme', 'dark');
            this.injectDarkModeStyles();
        } else {
            htmlElement.classList.add('light-mode');
            htmlElement.setAttribute('data-theme', 'light');
            document.body.setAttribute('data-theme', 'light');
            this.removeDarkModeStyles();
        }
        
        this.currentTheme = theme;
        localStorage.setItem('theme', theme);
        
        // Clear caches without refresh
        this.clearAffectedCaches();
    }

    // Clear all relevant caches without page refresh
    clearAffectedCaches() {
        this.clearCSSVariableCache();
        this.clearImageCache();
        this.clearFontCache();
        this.clearElementCaches();
        this.forceRepaint();
    }

    clearCSSVariableCache() {
        const root = document.documentElement;
        const computedStyle = getComputedStyle(root);
        
        const allVariables = ['--bg-primary', '--bg-secondary', '--text-primary', '--text-secondary'];
        allVariables.forEach(variable => {
            const value = computedStyle.getPropertyValue(variable);
            root.style.setProperty(variable, value);
        });
        
        document.querySelectorAll('[style*="background-color"]').forEach(el => {
            const currentBg = window.getComputedStyle(el).backgroundColor;
            if (el.style.backgroundColor !== currentBg) {
                el.style.backgroundColor = '';
            }
        });
    }

    clearImageCache() {
        const images = document.querySelectorAll('img[data-theme-sensitive="true"]');
        images.forEach(img => {
            const src = img.getAttribute('src');
            if (src) {
                const newSrc = src.replace(/\?.*$/, '') + '?t=' + Date.now();
                img.style.opacity = '0.5';
                img.onload = () => {
                    img.style.opacity = '1';
                };
                img.src = newSrc;
            }
        });
        
        const elementsWithBg = document.querySelectorAll('[style*="background-image"]');
        elementsWithBg.forEach(el => {
            const bgImage = window.getComputedStyle(el).backgroundImage;
            if (bgImage && bgImage !== 'none') {
                const url = bgImage.match(/url\(["']?([^"']*)["']?\)/);
                if (url && url[1]) {
                    const newUrl = url[1] + (url[1].includes('?') ? '&' : '?') + 't=' + Date.now();
                    el.style.backgroundImage = `url('${newUrl}')`;
                }
            }
        });
    }

    clearFontCache() {
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(() => {
                document.body.style.fontFamily = window.getComputedStyle(document.body).fontFamily;
            });
        }
    }

    clearElementCaches() {
        const allElements = document.querySelectorAll('*');
        
        allElements.forEach(el => {
            if (el._cachedStyles) {
                delete el._cachedStyles;
            }
            
            if (el.style.transition) {
                const originalTransition = el.style.transition;
                el.style.transition = 'none';
                setTimeout(() => {
                    el.style.transition = originalTransition;
                }, 50);
            }
        });
        
        this.clearChartCaches();
        this.clearTableCaches();
        this.clearAnimationCaches();
    }

    clearChartCaches() {
        if (window.Chart && window.Chart.instances) {
            window.Chart.instances.forEach(chart => {
                chart.update();
            });
        }
        
        document.querySelectorAll('canvas').forEach(canvas => {
            const ctx = canvas.getContext('2d');
            if (ctx) {
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                setTimeout(() => {
                    ctx.putImageData(imageData, 0, 0);
                }, 10);
            }
        });
    }

    clearTableCaches() {
        if (typeof $ !== 'undefined' && $.fn && $.fn.DataTable) {
            $('.dataTable').each(function() {
                if ($.fn.dataTable.isDataTable(this)) {
                    $(this).DataTable().draw(false);
                }
            });
        }
        
        document.querySelectorAll('table').forEach(table => {
            const rows = table.querySelectorAll('tr');
            rows.forEach((row, index) => {
                if (index % 2 === 0) {
                    row.style.backgroundColor = '';
                }
            });
        });
    }

    clearAnimationCaches() {
        const animatedElements = document.querySelectorAll('[class*="animate-"], [style*="animation"]');
        animatedElements.forEach(el => {
            const animation = el.style.animation;
            if (animation) {
                el.style.animation = 'none';
                setTimeout(() => {
                    el.style.animation = animation;
                }, 10);
            }
        });
    }

    forceRepaint() {
        const root = document.documentElement;
        
        root.classList.add('force-repaint');
        setTimeout(() => {
            root.classList.remove('force-repaint');
        }, 5);
        
        void document.body.offsetHeight;
        this.forceComponentRepaint();
        this.clearPendingTransitions();
    }

    forceComponentRepaint() {
        const components = [
            '.modern-header',
            '.sidebar',
            '.content-area',
            '.card',
            '.table'
        ];
        
        components.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(el => {
                const originalTransform = el.style.transform;
                const originalTransition = el.style.transition;
                
                el.style.transition = 'none';
                el.style.transform = 'scale(0.999)';
                setTimeout(() => {
                    el.style.transform = originalTransform || '';
                    setTimeout(() => {
                        el.style.transition = originalTransition || '';
                    }, 0);
                }, 0);
            });
        });
    }

    clearPendingTransitions() {
        const frames = requestAnimationFrame(() => {});
        for (let i = 0; i < frames; i++) {
            cancelAnimationFrame(i);
        }
    }

    clearLocalStorageCache() {
        const keysToKeep = ['theme', 'brightness', 'user_preferences'];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && !keysToKeep.includes(key) && key.startsWith('cache_')) {
                localStorage.removeItem(key);
            }
        }
    }

    clearSessionStorageCache() {
        for (let i = 0; i < sessionStorage.length; i++) {
            const key = sessionStorage.key(i);
            if (key && (key.includes('theme') || key.includes('style'))) {
                sessionStorage.removeItem(key);
            }
        }
    }

    applyBrightnessImmediately(percentage) {
        const brightness = Math.min(150, Math.max(30, percentage));
        document.documentElement.style.filter = `brightness(${brightness}%)`;
        this.currentBrightness = brightness;
        localStorage.setItem('brightness', brightness);
        this.updateBrightnessUI();
    }

    async saveTheme(theme) {
        console.log('Saving theme:', theme);
        this.applyThemeImmediately(theme);
        
        @auth
            try {
                await fetch('{{ route("preferences.theme") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ theme: theme })
                });
                console.log('Theme saved to server');
            } catch (error) {
                console.error('Failed to save theme:', error);
            }
        @endauth
    }

    async saveBrightness(percentage) {
        console.log('Saving brightness:', percentage);
        this.applyBrightnessImmediately(percentage);
        
        @auth
            try {
                await fetch('{{ route("preferences.brightness") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ brightness: percentage })
                });
                console.log('Brightness saved to server');
            } catch (error) {
                console.error('Failed to save brightness:', error);
            }
        @endauth
    }

    injectDarkModeStyles() {
        this.removeDarkModeStyles();
        
        const styleTag = document.createElement('style');
        styleTag.id = 'dark-mode-styles';
        styleTag.textContent = `
            .dark-mode, .dark-mode * {
                transition: none !important;
                animation: none !important;
            }
            
            .force-repaint {
                transform: translateZ(0);
                backface-visibility: hidden;
            }
            
            .dark-mode,
            .dark-mode body {
                background-color: #1a1a1a !important;
            }
        `;
        
        document.head.appendChild(styleTag);
    }

    removeDarkModeStyles() {
        const styleTag = document.getElementById('dark-mode-styles');
        if (styleTag) styleTag.remove();
    }

    async loadUserPreferences() {
        @auth
            try {
                const response = await fetch('{{ route("preferences.get") }}');
                const data = await response.json();
                this.applyThemeImmediately(data.theme);
                this.applyBrightnessImmediately(data.brightness);
                this.updateUI();
            } catch (error) {
                console.error('Failed to load preferences:', error);
            }
        @endauth
        
        const userStatusSpan = document.getElementById('userStatusText');
        if (userStatusSpan) {
            @auth
                userStatusSpan.innerHTML = '<i class="fas fa-check-circle"></i> Saved to your account';
            @else
                userStatusSpan.innerHTML = '<i class="fas fa-info-circle"></i> Saved locally (Sign in to save permanently)';
            @endauth
        }
    }

    updateActiveThemeButton() {
        const lightBtn = document.getElementById('themeLightBtn');
        const darkBtn = document.getElementById('themeDarkBtn');
        const systemBtn = document.getElementById('themeSystemBtn');
        
        if (lightBtn && darkBtn && systemBtn) {
            lightBtn.classList.remove('active');
            darkBtn.classList.remove('active');
            systemBtn.classList.remove('active');
            
            if (this.currentTheme === 'light') {
                lightBtn.classList.add('active');
            } else if (this.currentTheme === 'dark') {
                darkBtn.classList.add('active');
            } else if (this.currentTheme === 'system') {
                systemBtn.classList.add('active');
            }
        }
    }

    updateBrightnessUI() {
        const brightnessValueSpan = document.getElementById('brightnessValue');
        const brightnessSlider = document.getElementById('brightnessSlider');
        
        if (brightnessValueSpan) {
            brightnessValueSpan.textContent = this.currentBrightness;
        }
        
        if (brightnessSlider) {
            brightnessSlider.value = this.currentBrightness;
        }
    }

    updateUI() {
        this.updateActiveThemeButton();
        this.updateBrightnessUI();
    }

    setupEventListeners() {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (this.currentTheme === 'system') {
                this.applyThemeImmediately('system');
            }
        });
        
        const brightnessSlider = document.getElementById('brightnessSlider');
        if (brightnessSlider) {
            brightnessSlider.addEventListener('input', (e) => {
                const value = parseInt(e.target.value);
                this.saveBrightness(value);
            });
        }
        
        console.log('Event listeners setup complete');
    }
}

// GLOBAL HELPER FUNCTIONS
window.setTheme = function(theme) {
    console.log('setTheme called with:', theme);
    if (window.themeManager) {
        window.themeManager.saveTheme(theme);
    } else {
        console.error('ThemeManager not initialized');
    }
};

window.increaseBrightness = function() {
    console.log('increaseBrightness called');
    if (window.themeManager) {
        let newValue = Math.min(150, window.themeManager.currentBrightness + 10);
        window.themeManager.saveBrightness(newValue);
    }
};

window.decreaseBrightness = function() {
    console.log('decreaseBrightness called');
    if (window.themeManager) {
        let newValue = Math.max(30, window.themeManager.currentBrightness - 10);
        window.themeManager.saveBrightness(newValue);
    }
};

window.resetBrightness = function() {
    console.log('resetBrightness called');
    if (window.themeManager) {
        window.themeManager.saveBrightness(100);
    }
};

// Cache-Busting Helper Functions
(function() {
    window.clearElementCache = function(element) {
        if (!element) return;
        
        const computedStyles = window.getComputedStyle(element);
        const styleProperties = ['color', 'backgroundColor', 'borderColor', 'boxShadow'];
        
        styleProperties.forEach(prop => {
            const value = computedStyles.getPropertyValue(prop);
            if (value) {
                element.style.setProperty(prop, value);
            }
        });
        
        element.style.transform = 'translateZ(0)';
        setTimeout(() => {
            element.style.transform = '';
        }, 10);
    };
    
    window.clearAllComponentCaches = function() {
        if (window.Livewire) {
            Livewire.dispatch('themeChanged');
        }
        
        if (window.Vue && window.Vue.prototype.$forceUpdate) {
            document.querySelectorAll('[data-v-app]').forEach(el => {
                if (el.__vue__) {
                    el.__vue__.$forceUpdate();
                }
            });
        }
        
        window.dispatchEvent(new CustomEvent('themeChanged', {
            detail: { theme: window.themeManager?.currentTheme }
        }));
        
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            if (img.complete) {
                const src = img.src;
                img.src = '';
                img.src = src;
            }
        });
    };
    
    window.clearCacheByType = function(type) {
        switch(type) {
            case 'css':
                const sheets = document.styleSheets;
                for(let i = 0; i < sheets.length; i++) {
                    try {
                        const rules = sheets[i].cssRules;
                        if(rules) {
                            for(let j = 0; j < rules.length; j++) {
                                sheets[i].insertRule(rules[j].cssText, j);
                            }
                        }
                    } catch(e) {}
                }
                break;
            case 'images':
                const allImages = document.querySelectorAll('img');
                allImages.forEach(img => {
                    const currentSrc = img.src;
                    img.src = '';
                    img.src = currentSrc;
                });
                break;
            case 'fonts':
                if(document.fonts && document.fonts.clear) {
                    document.fonts.clear();
                }
                break;
        }
    };
    
    console.log('Cache-busting helper functions initialized');
})();

// Initialize theme manager
window.themeManager = new ThemeManager();

// Manual cache clear helper
window.manualCacheClear = function() {
    if (window.themeManager) {
        window.themeManager.clearAffectedCaches();
        window.clearAllComponentCaches();
        console.log('Manual cache clear triggered');
    }
};

// Debug helper
window.debugTheme = function() {
    console.log('=== Theme Debug ===');
    console.log('Current theme:', window.themeManager?.currentTheme);
    console.log('Current brightness:', window.themeManager?.currentBrightness);
    console.log('HTML classes:', document.documentElement.classList);
    console.log('localStorage theme:', localStorage.getItem('theme'));
    console.log('localStorage brightness:', localStorage.getItem('brightness'));
};
</script>