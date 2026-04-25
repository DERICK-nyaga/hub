import './bootstrap';
import './payments.js';
import './deductions.js';
import './employee-balance.js';
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const sidebar = document.querySelector('.sidebar');
    const menuToggler = document.querySelector('.navbar-toggler');

    if (menuToggler) {
        menuToggler.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }

    // Submenu toggle
    const hasSubmenu = document.querySelectorAll('.has-submenu > .nav-link');

    hasSubmenu.forEach(item => {
        item.addEventListener('click', function(e) {
            if (window.innerWidth < 992) {
                e.preventDefault();
                const parent = this.parentElement;
                parent.classList.toggle('show');
            }
        });
    });
});
document.getElementById('type').addEventListener('change', function() {
    // Remove all color classes
    this.className = 'form-control';
    // Add the appropriate color class
    this.classList.add('type-' + this.value);
});

// Initialize color on page load
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    typeSelect.classList.add('type-' + typeSelect.value);
});


    var triggerTabList = [].slice.call(document.querySelectorAll('#stationTabs button'))
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl)
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault()
            tabTrigger.show()
        })
    });
