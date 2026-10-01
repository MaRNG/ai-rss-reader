import { defineConfig } from 'vite';

export default defineConfig(({ mode }) => {
	// Statický build mockupů pro Caddy: www/mockups/ → https://ai-rss-reader.localhost/
	if (mode === 'mockups') {
		return {
			root: 'mockups',
			base: '/',
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

	return {
		server: {
			open: '/mockups/news.html',
		},
		build: {
			outDir: 'www/assets',
			emptyOutDir: true,
			manifest: true,
			rollupOptions: {
				input: {
					front: 'assets/front/main.ts',
				},
			},
		},
	};
});
