import Chart from 'chart.js/auto';

function safeJsonParse(value, fallback) {
    try {
        return JSON.parse(value);
    } catch {
        return fallback;
    }
}

function palette(n) {
    const colors = [
        '#FF7A1A', // Accent Orange
        '#FFB24D', // Light Orange
        '#10B981', // emerald-500
        '#0EA5E9', // sky-500
        '#A855F7', // purple-500
        '#F59E0B', // amber-500
        '#EF4444', // red-500
        '#14B8A6', // teal-500
        '#111827', // gray-900
    ];

    return Array.from({ length: n }, (_, i) => colors[i % colors.length]);
}

function initCharts() {
    const canvases = document.querySelectorAll('canvas[data-chart]');

    canvases.forEach((canvas) => {
        const type = canvas.dataset.chart;
        const label = canvas.dataset.chartLabel || 'Value';
        const labels = safeJsonParse(canvas.dataset.chartLabels || '[]', []);
        const values = safeJsonParse(canvas.dataset.chartValues || '[]', []);

        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(148, 163, 184, 0.25)';
        const tickColor = isDark ? 'rgba(255, 255, 255, 0.6)' : '#475569';

        const colors = palette(Array.isArray(labels) ? labels.length : 6);

        // Avoid creating a chart with empty data (keeps UI clean)
        const hasData = Array.isArray(values) && values.length > 0 && values.some((v) => Number(v) !== 0);

        if (!hasData) {
            // Simple empty-state drawing
            const ctx2d = canvas.getContext('2d');
            if (!ctx2d) return;
            ctx2d.clearRect(0, 0, canvas.width, canvas.height);
            ctx2d.font = '14px ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial';
            ctx2d.fillStyle = isDark ? 'rgba(255, 255, 255, 0.4)' : '#64748B';
            ctx2d.textAlign = 'center';
            ctx2d.fillText('No data yet', canvas.width / 2, canvas.height / 2);
            return;
        }

        // eslint-disable-next-line no-new
        new Chart(canvas, {
            type,
            data: {
                labels,
                datasets: [
                    {
                        label,
                        data: values,
                        borderColor: colors[0],
                        backgroundColor:
                            type === 'doughnut' || type === 'pie'
                                ? colors
                                : 'rgba(255, 122, 26, 0.15)',
                        tension: 0.35,
                        fill: type === 'line',
                        pointRadius: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: type === 'doughnut' || type === 'pie',
                        labels: {
                            color: tickColor,
                        },
                    },
                    tooltip: {
                        enabled: true,
                    },
                },
                scales:
                    type === 'doughnut' || type === 'pie'
                        ? {}
                        : {
                              x: {
                                  grid: { color: gridColor },
                                  ticks: { color: tickColor, maxRotation: 0, autoSkip: true },
                              },
                              y: {
                                  grid: { color: gridColor },
                                  ticks: { color: tickColor },
                              },
                          },
            },
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCharts);
} else {
    initCharts();
}

