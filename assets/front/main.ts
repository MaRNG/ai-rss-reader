import '@fontsource/onest/400.css';
import '@fontsource/onest/500.css';
import '@fontsource/onest/600.css';
import '@fontsource/onest/700.css';
import './main.css';

import {
	ArrowDown,
	ArrowUpRight,
	ChartColumn,
	createIcons,
	EyeOff,
	House,
	Layers,
	Menu,
	Newspaper,
	Plus,
	Rows3,
	Rss,
	Settings,
	Star,
	X,
} from 'lucide';

createIcons({
	icons: { ArrowDown, ArrowUpRight, ChartColumn, EyeOff, House, Layers, Menu, Newspaper, Plus, Rows3, Rss, Settings, Star, X },
	attrs: { 'stroke-width': 1.75, 'aria-hidden': 'true' },
});

const app = document.querySelector<HTMLElement>('.app');

// Hvězdička (v aplikaci půjde přes Naja signál)
document.querySelectorAll<HTMLButtonElement>('[data-star]').forEach((button) => {
	button.addEventListener('click', () => {
		const pressed = button.getAttribute('aria-pressed') !== 'true';
		button.setAttribute('aria-pressed', String(pressed));
		button.title = pressed ? 'Odebrat hvězdičku' : 'Označit hvězdičkou';
	});
});

// Přepínače v hlavičce: kompaktní zobrazení a skrytí přečtených (jen pro přihlášené)
function bindToggle(selector: string, onChange: (on: boolean) => void): void {
	const button = document.querySelector<HTMLButtonElement>(selector);
	button?.addEventListener('click', () => {
		const on = button.getAttribute('aria-pressed') !== 'true';
		button.setAttribute('aria-pressed', String(on));
		onChange(on);
	});
}

bindToggle('[data-density-toggle]', (on) => app?.setAttribute('data-density', on ? 'compact' : 'comfortable'));
bindToggle('[data-hide-read]', (on) => {
	app?.toggleAttribute('data-hide-read', on);
	updateDays();
});

// Vysouvací seznam feedů na úzkých displejích
const drawerOpen = document.querySelector<HTMLButtonElement>('[data-drawer-open]');
const scrim = document.querySelector<HTMLElement>('.scrim');

function setDrawer(open: boolean): void {
	app?.classList.toggle('is-drawer-open', open);
	drawerOpen?.setAttribute('aria-expanded', String(open));
	if (scrim) {
		scrim.hidden = !open;
	}
}

drawerOpen?.addEventListener('click', () => setDrawer(true));
document.querySelectorAll('[data-drawer-close]').forEach((el) => el.addEventListener('click', () => setDrawer(false)));
document.addEventListener('keydown', (event) => {
	if (event.key === 'Escape') {
		setDrawer(false);
	}
});

// Mockup: filtr podle feedu (v aplikaci to bude odkaz na /feeds/<id>)
const title = document.querySelector<HTMLElement>('[data-title]');
const count = document.querySelector<HTMLElement>('[data-count]');
const subtitle = document.querySelector<HTMLElement>('[data-subtitle]');
const subtitleShort = document.querySelector<HTMLElement>('[data-subtitle-short]');
const empty = document.querySelector<HTMLElement>('[data-empty]');
const articles = document.querySelectorAll<HTMLElement>('.article');

function isVisible(article: HTMLElement): boolean {
	return !article.hidden && !(app?.hasAttribute('data-hide-read') && article.classList.contains('is-read'));
}

// Den bez viditelných článků se skryje i s nadpisem
function updateDays(): void {
	let visible = 0;
	document.querySelectorAll<HTMLElement>('[data-day]').forEach((day) => {
		const dayVisible = Array.from(day.querySelectorAll<HTMLElement>('.article')).filter(isVisible).length;
		day.hidden = dayVisible === 0;
		visible += dayVisible;
	});
	if (empty) empty.hidden = visible > 0;
}

function adjective(n: number): string {
	if (n === 1) return 'nový';
	if (n >= 2 && n <= 4) return 'nové';
	return 'nových';
}

function noun(n: number): string {
	if (n === 1) return 'článek';
	if (n >= 2 && n <= 4) return 'články';
	return 'článků';
}

document.querySelectorAll<HTMLAnchorElement>('.feed[data-feed]').forEach((link) => {
	link.addEventListener('click', (event) => {
		event.preventDefault();
		const feed = link.dataset.feed ?? 'all';
		document.querySelectorAll('.feed.is-active').forEach((el) => el.classList.remove('is-active'));
		link.classList.add('is-active');

		articles.forEach((article) => {
			article.hidden = feed !== 'all' && article.dataset.feed !== feed;
		});
		updateDays();

		const n = Number(link.querySelector('.feed__count')?.textContent ?? 0);
		if (title) title.textContent = feed === 'all' ? 'Novinky' : (link.querySelector('.feed__name')?.textContent ?? '');
		if (count) count.textContent = String(n);
		if (subtitle) subtitle.textContent = `${adjective(n)} ${noun(n)} za posledních 24 hodin`;
		if (subtitleShort) subtitleShort.textContent = `${adjective(n)} za 24 h`;
		setDrawer(false);
	});
});
