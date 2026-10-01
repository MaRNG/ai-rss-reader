import { defineConfig } from 'vite';
import nette from '@nette/vite-plugin';

export default defineConfig(({ mode }) => {
	// Statický build mockupů pro Caddy: www/mockups/ (npm run build:mockups)
	if (mode === 'mockups') {
		return {
			root: 'mockups',
			base: '/mockups/',
			build: {
				outDir: '../www/mockups',
				emptyOutDir: true,
				rollupOptions: {
					input: {
						news: 'mockups/news.html',
					},
				},
			},
		};
	}

	// Aplikace: assets/ → www/assets/ + manifest pro nette/assets ({asset 'vite:front/main.ts'})
	return {
		plugins: [
			nette({
				entry: ['front/main.ts'],
				refresh: ['App/**/*.latte'],
			}),
		],
		build: {
			emptyOutDir: true,
		},
	};
});
