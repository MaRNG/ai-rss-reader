<?php

declare(strict_types=1);

namespace App\Model\Image;

use App\Model\Database\Entity\Image;
use App\Model\Database\Repository\ImageRepository;
use App\Model\Http\HttpClient;
use App\Model\Http\HttpException;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Utils\FileSystem;


/**
 * Stahuje čekající obrázky do www/files a přepisuje jejich URL v článcích.
 * Typ se ověřuje z obsahu souboru; SVG se nestahuje (může obsahovat skripty).
 */
final class ImageDownloader
{
	private const array AllowedTypes = [
		'image/jpeg' => 'jpg',
		'image/png' => 'png',
		'image/gif' => 'gif',
		'image/webp' => 'webp',
		'image/avif' => 'avif',
	];


	public function __construct(
		private readonly ImageRepository $images,
		private readonly ImageStorage $storage,
		private readonly HttpClient $http,
		private readonly EntityManagerInterface $em,
		private readonly string $tempDir,
		private readonly int $maxBytes,
		private readonly int $timeout,
		private readonly int $maxAttempts,
	) {
	}


	/**
	 * @return array{downloaded: int, failed: int}
	 */
	public function downloadPending(int $limit, ?callable $onProgress = null): array
	{
		$stats = ['downloaded' => 0, 'failed' => 0];
		foreach ($this->images->findPending($limit) as $image) {
			try {
				$this->download($image);
				$this->rewriteArticles($image);
				$stats['downloaded']++;
				$onProgress && $onProgress($image, null);
			} catch (HttpException | ImageException $e) {
				$permanent = $e instanceof ImageException || $e->permanent;
				$permanent ? $image->markFailed() : $image->markAttemptFailed($this->maxAttempts);
				$stats['failed']++;
				$onProgress && $onProgress($image, $e->getMessage());
			}
			$this->em->flush();
		}
		return $stats;
	}


	private function download(Image $image): void
	{
		$response = $this->http->get(
			$image->getSourceUrl(),
			['Accept' => 'image/avif,image/webp,image/png,image/jpeg,image/gif;q=0.9'],
			$this->maxBytes,
			$this->timeout,
		);

		FileSystem::createDir($this->tempDir);
		$temp = $this->tempDir . '/' . $image->getHash() . '.' . bin2hex(random_bytes(4)) . '.tmp';
		FileSystem::write($temp, $response->body, null);

		try {
			$mime = (new \finfo(FILEINFO_MIME_TYPE))->file($temp) ?: '';
			$extension = self::AllowedTypes[$mime] ?? throw new ImageException("Nepovolený typ obrázku '$mime'");
			$size = @getimagesize($temp);
			if (!$size || $size[0] < 1 || $size[1] < 1) {
				throw new ImageException('Soubor není platný obrázek');
			}

			$path = $this->storage->relativePath($image->getFeed()->getId(), $image->getHash(), $extension);
			$this->storage->store($temp, $path);
			$image->markDownloaded($path, $mime, strlen($response->body), $size[0], $size[1]);
		} finally {
			FileSystem::delete($temp);
		}
	}


	/** V content_html článků, které obrázek používají, přepíše src na lokální URL */
	private function rewriteArticles(Image $image): void
	{
		$source = $image->getSourceUrl();
		$local = $this->storage->getUrl($image);
		$search = array_unique([$source, htmlspecialchars($source, ENT_QUOTES | ENT_HTML5, 'UTF-8'), str_replace('&', '&amp;', $source)]);

		foreach ($this->images->findArticlesUsing($image) as $article) {
			$html = $article->getContentHtml();
			if ($html === null) {
				continue;
			}
			$new = $html;
			foreach ($search as $needle) {
				$new = str_replace('src="' . $needle . '"', 'src="' . $local . '"', $new);
			}
			if ($new !== $html) {
				$article->setContent($new, $article->getContentText());
			}
		}
	}
}
