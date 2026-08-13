(function() {
    'use strict';

    function initWidget() {
        const containers = document.querySelectorAll('.tembo-booking-widget, [data-tembo-property]');
        containers.forEach(function(el) {
            if (el.dataset.mounted) return;
            el.dataset.mounted = 'true';

            const property = el.dataset.property || el.dataset.temboProperty || 'tembo-hotel';
            const host = el.dataset.host || window.location.origin;
            const height = el.dataset.height || '160px';

            const iframe = document.createElement('iframe');
            iframe.src = host.replace(/\/$/, '') + '/booking/' + property + '/widget';
            iframe.style.width = '100%';
            iframe.style.height = height;
            iframe.style.border = 'none';
            iframe.style.overflow = 'hidden';
            iframe.style.display = 'block';
            iframe.setAttribute('frameborder', '0');
            iframe.setAttribute('scrolling', 'no');
            iframe.setAttribute('title', 'Book Direct — ' + property);

            el.innerHTML = '';
            el.appendChild(iframe);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWidget);
    } else {
        initWidget();
    }
})();
