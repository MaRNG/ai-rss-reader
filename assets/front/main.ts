import '@fontsource/onest/400.css';
import '@fontsource/onest/500.css';
import '@fontsource/onest/600.css';
import '@fontsource/onest/700.css';
import './main.css';

import naja from 'naja';
import {
	ArrowDown,
	ArrowLeft,
	ArrowUp,
	ArrowUpRight,
	ChartColumn,
	createIcons,
	EyeOff,
	House,
	Layers,
	LogIn,
	LogOut,
	Menu,
	Newspaper,
	Pencil,
	Plus,
	RefreshCw,
	Rows3,
	Rss,
	Settings,
	SlidersHorizontal,
	Star,
	ThumbsDown,
	ThumbsUp,
	Trash2,
	UserPlus,
	Users,
	X,
} from 'lucide';

const icons = {
	ArrowDown, ArrowLeft, ArrowUp, ArrowUpRight, ChartColumn, EyeOff, House, Layers, LogIn, LogOut, Menu, Newspaper,
	Pencil, Plus, RefreshCw, Rows3, Rss, Settings, SlidersHorizontal, Star, ThumbsDown, ThumbsUp, Trash2, UserPlus, Users, X,
};

createIcons({ icons, attrs: { 'stroke-width': 1.75, 'aria-hidden': 'true' } });
naja.initialize();

const app = document.querySelector<HTMLElement>('.app');

// Uložené preference (jen pohodlí, bez localStorage vše funguje s výchozím stavem)
function loadPref(key: string): boolean {
	try {
		return localStorage.getItem(`siftly:${key}`) === '1';
	} catch {
		return false;
	}
}

function savePref(key: string, on: boolean): void {
	try {
		localStorage.setItem(`siftly:${key}`, on ? '1' : '0');
	} catch {
		// soukromé okno nebo zakázané úložiště
	}
}

// Přepínače v hlavičce: kompaktní zobrazení a skrytí přečtených
function bindToggle(selector: string, pref: string, apply: (on: boolean) => void): void {
	const button = document.querySelector<HTMLButtonElement>(selector);
	if (!button) {
		return;
	}
	const set = (on: boolean): void => {
		button.setAttribute('aria-pressed', String(on));
		apply(on);
	};
	set(loadPref(pref));
	button.addEventListener('click', () => {
		const on = button.getAttribute('aria-pressed') !== 'true';
		set(on);
		savePref(pref, on);
	});
}

bindToggle('[data-density-toggle]', 'compact', (on) => app?.setAttribute('data-density', on ? 'compact' : 'comfortable'));
bindToggle('[data-hide-read]', 'hideRead', (on) => {
	app?.toggleAttribute('data-hide-read', on);
	document.querySelectorAll<HTMLElement>('[data-day]').forEach((day) => {
		const visible = Array.from(day.querySelectorAll('.article')).some((a) => !(on && a.classList.contains('is-read')));
		day.hidden = !visible;
	});
});

// Hvězdička: POST signál přes Naja, stav se přepne hned a při chybě vrátí
document.addEventListener('click', async (event) => {
	const button = (event.target as Element).closest<HTMLButtonElement>('[data-star]');
	if (!button?.dataset.url) {
		return;
	}
	const starred = button.getAttribute('aria-pressed') !== 'true';
	button.setAttribute('aria-pressed', String(starred));
	button.title = starred ? 'Odebrat hvězdičku' : 'Označit hvězdičkou';
	try {
		await naja.makeRequest('POST', `${button.dataset.url}&starred=${starred ? 1 : 0}`, null, { history: false });
	} catch {
		button.setAttribute('aria-pressed', String(!starred));
	}
});

// 👍 / 👎 na detailu článku
document.addEventListener('click', async (event) => {
	const button = (event.target as Element).closest<HTMLButtonElement>('[data-feedback]');
	if (!button?.dataset.url) {
		return;
	}
	try {
		const payload = await naja.makeRequest('POST', button.dataset.url, null, { history: false }) as { feedback?: number };
		document.querySelectorAll<HTMLButtonElement>('[data-feedback]').forEach((b) => {
			b.setAttribute('aria-pressed', String(Number(b.dataset.feedback) === payload.feedback));
		});
	} catch {
		// stav zůstane beze změny
	}
});

// Potvrzení před smazáním
document.addEventListener('submit', (event) => {
	const form = event.target as HTMLFormElement;
	if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
		event.preventDefault();
	}
});

// Vysouvací sidebar na úzkých displejích
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

// Grafy jen na stránce statistik (Chart.js se načte až tady)
const statsData = document.getElementById('stats-data');
if (statsData) {
	import('./stats').then(({ renderStats }) => renderStats(JSON.parse(statsData.textContent ?? '{}')));
}
