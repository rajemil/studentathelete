import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Notifications dropdown — must be registered BEFORE Alpine.start()
window.notificationsDropdown = function () {
    return {
        open: false,
        loading: false,
        unreadCount: 0,
        notifications: [],
        poller: null,
        init() {
            this.refresh();
            this.poller = window.setInterval(() => this.refresh(), 15000);
        },
        destroy() {
            if (this.poller) window.clearInterval(this.poller);
        },
        async refresh() {
            try {
                this.loading = this.notifications.length === 0;
                const res = await fetch('/notifications', {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                const data = await res.json();
                if (!res.ok) return;
                this.unreadCount = data.unread_count || 0;
                this.notifications = data.notifications || [];
            } finally {
                this.loading = false;
            }
        },
        async markRead(id) {
            try {
                await fetch(`/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    credentials: 'same-origin',
                });
            } finally {
                await this.refresh();
            }
        },
        async readAll() {
            try {
                await fetch('/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    credentials: 'same-origin',
                });
            } finally {
                await this.refresh();
            }
        },
        formatTime(iso) {
            if (!iso) return '';
            const d = new Date(iso);
            return d.toLocaleString();
        },
    };
};

Alpine.start();

// Lazy-load page-specific JS to keep the core bundle small.
if (document.querySelector('[data-landing]')) {
    import('./landing');
}

if (document.getElementById('analytics-root')) {
    import('./analytics-ui');
}

if (document.querySelector('canvas[data-chart]')) {
    import('./dashboards');
}
