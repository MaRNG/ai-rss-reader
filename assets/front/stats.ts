import {
	BarController,
	BarElement,
	CategoryScale,
	Chart,
	Filler,
	Legend,
	LinearScale,
	LineController,
	LineElement,
	PointElement,
	Tooltip,
} from 'chart.js';

Chart.register(BarController, BarElement, CategoryScale, Filler, Legend, LinearScale, LineController, LineElement, PointElement, Tooltip);

interface DailyStats {
	days: string[];
	totals: number[];
	feeds: { id: number; title: string; counts: number[] }[];
}

const css = getComputedStyle(document.documentElement);
const token = (name: string, fallback: string): string => css.getPropertyValue(name).trim() || fallback;

const orange = token('--orange', '#c94a05');
const ink = token('--ink', '#1c1917');
const inkFaint = token('--ink-faint', '#6b655f');
const line = token('--line', '#eceae7');

// Odstíny pro feedy ve skládaném grafu: od oranžové přes inkoustové tóny, aby oranžová dominovala
const feedColors = ['#c94a05', '#1c1917', '#e98a4f', '#6b655f', '#f2b48a', '#a8a29e', '#8a3504', '#d6d3d1', '#57524d', '#fbd5bb'];

function label(day: string): string {
	const [, month, date] = day.split('-');
	return `${Number(date)}. ${Number(month)}.`;
}

Chart.defaults.font.family = token('--font', 'system-ui');
Chart.defaults.font.size = 12;
Chart.defaults.color = inkFaint;

export function renderStats(data: DailyStats): void {
	const labels = data.days.map(label);
	const grid = { color: line };
	const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	const total = document.querySelector<HTMLCanvasElement>('[data-chart="total"]');
	if (total) {
		new Chart(total, {
			type: 'line',
			data: {
				labels,
				datasets: [{
					label: 'Nové články',
					data: data.totals,
					borderColor: orange,
					backgroundColor: `${orange}22`,
					fill: true,
					tension: 0.25,
					pointRadius: 0,
					pointHoverRadius: 4,
					borderWidth: 2,
				}],
			},
			options: {
				animation: reduced ? false : undefined,
				maintainAspectRatio: false,
				interaction: { mode: 'index', intersect: false },
				plugins: { legend: { display: false } },
				scales: {
					x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } },
					y: { beginAtZero: true, grid, ticks: { precision: 0 } },
				},
			},
		});
	}

	const feeds = document.querySelector<HTMLCanvasElement>('[data-chart="feeds"]');
	if (feeds) {
		new Chart(feeds, {
			type: 'bar',
			data: {
				labels,
				datasets: data.feeds.map((feed, i) => ({
					label: feed.title,
					data: feed.counts,
					backgroundColor: feedColors[i % feedColors.length],
					borderWidth: 0,
					stack: 'feeds',
				})),
			},
			options: {
				animation: reduced ? false : undefined,
				maintainAspectRatio: false,
				interaction: { mode: 'index', intersect: false },
				plugins: {
					legend: { position: 'bottom', labels: { boxWidth: 12, boxHeight: 12, color: ink } },
					tooltip: { filter: (item) => Number(item.raw) > 0 },
				},
				scales: {
					x: { stacked: true, grid: { display: false }, ticks: { maxTicksLimit: 10 } },
					y: { stacked: true, beginAtZero: true, grid, ticks: { precision: 0 } },
				},
			},
		});
	}
}
