import Chart from 'chart.js/auto';

function $(selector, root = document) {
    return root.querySelector(selector);
}

function $all(selector, root = document) {
    return Array.from(root.querySelectorAll(selector));
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function apiGet(url) {
    const res = await fetch(url, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    });

    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        const msg = data?.message || `Request failed (${res.status})`;
        throw new Error(msg);
    }
    return data;
}

async function apiPost(url, body) {
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify(body),
    });

    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        const msg = data?.message || `Request failed (${res.status})`;
        throw new Error(msg);
    }
    return data;
}

function prettyJson(obj) {
    return JSON.stringify(obj, null, 2);
}

function selectedValues(selectEl) {
    return Array.from(selectEl.selectedOptions).map((o) => Number(o.value)).filter(Boolean);
}

function setLoading(btn, loading, labelLoading = 'Loading...') {
    if (!btn) return;
    btn.disabled = loading;
    btn.dataset.labelIdle = btn.dataset.labelIdle || btn.textContent;
    btn.textContent = loading ? labelLoading : btn.dataset.labelIdle;
    btn.classList.toggle('opacity-60', loading);
    btn.classList.toggle('cursor-not-allowed', loading);
}

function toast(root, message, type = 'info') {
    const el = root.querySelector('[data-analytics="toast"]');
    if (!el) return;

    el.textContent = message;
    el.className =
        'mb-4 rounded-2xl px-4 py-3 text-sm border shadow-sm ' +
        (type === 'error'
            ? 'border-red-200 bg-red-50 text-red-900 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-100'
            : type === 'success'
              ? 'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-100'
              : 'border-gray-200 bg-white text-gray-900 dark:border-white/10 dark:bg-gray-900/50 dark:text-gray-100');

    el.hidden = false;
    window.clearTimeout(el._t);
    el._t = window.setTimeout(() => {
        el.hidden = true;
    }, 3500);
}

function ensureChart(canvas, prevChartRef) {
    if (prevChartRef.current) {
        prevChartRef.current.destroy();
        prevChartRef.current = null;
    }

    const ctx = canvas.getContext('2d');
    if (!ctx) return null;
    return ctx;
}

function initAnalyticsUI() {
    const root = document.getElementById('analytics-root');
    if (!root) return;

    // Athlete prediction
    const athleteForm = $('[data-analytics="athlete-form"]', root);
    const recBtn = $('[data-analytics="recommend-btn"]', root);
    const predScoreEl = $('[data-analytics="pred-score"]', root);
    const predConfEl = $('[data-analytics="pred-confidence"]', root);
    const predTrendEl = $('[data-analytics="pred-trend"]', root);
    const predJsonEl = $('[data-analytics="pred-json"]', root);
    const predCanvas = $('[data-analytics="pred-chart"]', root);
    const predChart = { current: null };

    async function runAthletePrediction(withRecommendations = false) {
        const submitBtn = athleteForm?.querySelector('button[type="submit"]');
        const userId = Number($('#athlete_user_id', root).value);
        const sportId = $('#athlete_sport_id', root).value ? Number($('#athlete_sport_id', root).value) : null;
        const horizonDays = Number($('#horizon_days', root).value || 14);

        if (!userId) throw new Error('Select an athlete.');

        setLoading(submitBtn, true, 'Predicting...');
        setLoading(recBtn, true, 'Working...');
        toast(root, 'Fetching prediction…', 'info');

        const params = new URLSearchParams();
        if (sportId) params.set('sport_id', String(sportId));
        if (horizonDays) params.set('horizon_days', String(horizonDays));

        const pred = await apiGet(`/api/predictions/athletes/${userId}?${params.toString()}`);

        predScoreEl.textContent = pred?.prediction?.predicted_score ?? '—';
        predConfEl.textContent = pred?.prediction?.confidence ?? '—';
        predTrendEl.textContent = pred?.prediction?.trend ?? '—';
        predJsonEl.textContent = prettyJson(pred);

        // Draw a simple 2-point chart (current recent_avg -> predicted)
        const ctx = ensureChart(predCanvas, predChart);
        if (ctx) {
            const recentAvg = pred?.prediction?.inputs?.recent_avg ?? null;
            const predicted = pred?.prediction?.predicted_score ?? null;
            const labels = ['Recent avg', 'Predicted'];
            const values = [recentAvg ?? 0, predicted ?? 0];

            predChart.current = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Score',
                            data: values,
                            backgroundColor: ['rgba(148,163,184,0.35)', 'rgba(79,70,229,0.45)'],
                            borderColor: ['rgba(148,163,184,0.8)', 'rgba(79,70,229,0.9)'],
                            borderWidth: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, max: 100 },
                    },
                },
            });
        }

        if (withRecommendations) {
            const rec = await apiGet(`/api/predictions/athletes/${userId}/recommendations?${sportId ? `sport_id=${sportId}` : ''}`);
            // append rec into JSON panel for convenience
            predJsonEl.textContent = prettyJson({ ...pred, recommendations: rec.recommendations });
        }

        toast(root, 'Prediction ready.', 'success');
        setLoading(submitBtn, false);
        setLoading(recBtn, false);
    }

    athleteForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            await runAthletePrediction(false);
        } catch (err) {
            predJsonEl.textContent = prettyJson({ error: err.message || String(err) });
            toast(root, err.message || 'Request failed.', 'error');
            setLoading(athleteForm?.querySelector('button[type="submit"]'), false);
            setLoading(recBtn, false);
        }
    });

    recBtn?.addEventListener('click', async () => {
        try {
            await runAthletePrediction(true);
        } catch (err) {
            predJsonEl.textContent = prettyJson({ error: err.message || String(err) });
            toast(root, err.message || 'Request failed.', 'error');
            setLoading(athleteForm?.querySelector('button[type="submit"]'), false);
            setLoading(recBtn, false);
        }
    });

    // Win probability
    const wpForm = $('[data-analytics="winprob-form"]', root);
    const wpAEl = $('[data-analytics="wp-a"]', root);
    const wpBEl = $('[data-analytics="wp-b"]', root);
    const wpJsonEl = $('[data-analytics="wp-json"]', root);

    wpForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const btn = wpForm.querySelector('button[type="submit"]');
            const sportId = $('#wp_sport_id', root).value ? Number($('#wp_sport_id', root).value) : null;
            const teamA = selectedValues($('#team_a', root));
            const teamB = selectedValues($('#team_b', root));

            if (teamA.length === 0 || teamB.length === 0) {
                throw new Error('Select at least one athlete for each team.');
            }

            setLoading(btn, true, 'Calculating...');
            toast(root, 'Calculating win probability…', 'info');

            const body = {
                sport_id: sportId,
                team_a_user_ids: teamA,
                team_b_user_ids: teamB,
            };

            const res = await apiPost('/api/predictions/teams/win-probability', body);
            wpAEl.textContent = `${res.team_a.win_probability}%`;
            wpBEl.textContent = `${res.team_b.win_probability}%`;
            wpJsonEl.textContent = prettyJson(res);
            toast(root, 'Win probability ready.', 'success');
            setLoading(btn, false);
        } catch (err) {
            wpJsonEl.textContent = prettyJson({ error: err.message || String(err) });
            toast(root, err.message || 'Request failed.', 'error');
            setLoading(wpForm?.querySelector('button[type="submit"]'), false);
        }
    });

    // Strongest lineup
    const luForm = $('[data-analytics="lineup-form"]', root);
    const luStrengthEl = $('[data-analytics="lu-strength"]', root);
    const luListEl = $('[data-analytics="lu-list"]', root);
    const luJsonEl = $('[data-analytics="lu-json"]', root);

    luForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const btn = luForm.querySelector('button[type="submit"]');
            const sportId = $('#lu_sport_id', root).value ? Number($('#lu_sport_id', root).value) : null;
            const candidates = selectedValues($('#lu_candidates', root));
            const lineupSize = Number($('#lineup_size', root).value || 5);

            if (candidates.length === 0) {
                throw new Error('Select at least one candidate athlete.');
            }

            setLoading(btn, true, 'Scoring...');
            toast(root, 'Building strongest lineup…', 'info');

            const body = {
                sport_id: sportId,
                candidate_user_ids: candidates,
                lineup_size: lineupSize,
            };

            const res = await apiPost('/api/predictions/teams/strongest-lineup', body);
            luStrengthEl.textContent = res.lineup_strength ?? '—';
            luJsonEl.textContent = prettyJson(res);

            luListEl.innerHTML = '';
            (res.lineup || []).forEach((p, idx) => {
                const row = document.createElement('div');
                row.className = 'flex items-center justify-between rounded-lg bg-gray-50 dark:bg-gray-900/40 px-3 py-2';
                row.innerHTML = `<div class="text-sm text-gray-900 dark:text-gray-100">#${idx + 1} ${p.name}</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">${p.predicted_score ?? '—'} · ${p.trend ?? '—'} · conf ${p.confidence ?? '—'}</div>`;
                luListEl.appendChild(row);
            });
            toast(root, 'Lineup ready.', 'success');
            setLoading(btn, false);
        } catch (err) {
            luJsonEl.textContent = prettyJson({ error: err.message || String(err) });
            toast(root, err.message || 'Request failed.', 'error');
            setLoading(luForm?.querySelector('button[type="submit"]'), false);
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAnalyticsUI);
} else {
    initAnalyticsUI();
}

