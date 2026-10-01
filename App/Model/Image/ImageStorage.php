<?php

declare(strict_types=1);

namespace App\Model\Image;

use App\Model\Database\Entity\Image;
use Nette\Utils\FileSystem;


/**
 * Umístění stažených obrázků: www/files/<feed_id>/<h1>/<h2>/<h3>/<h4>/<hash>.<ext>
 * Jediné místo, které zná schéma cest (viz docs/images.md).
 */
final class ImageStorage
{
	public function __construct(
		private readonly string $dir,
		private readonly string $url,
	) {
	}


	public function relativePath(int $feedId, string $hash, string $extension): string
	{
		return sprintf('%d/%s/%s/%s/%s/%s.%s', $feedId, $hash[0], $hash[1], $hash[2], $hash[3], $hash, $extension);
	}


	public function store(string $tempFile, string $relativePath): void
	{
		$target = $this->dir . '/' . $relativePath;
		FileSystem::createDir(dirname($target));
		FileSystem::rename($tempFile, $target);
		@chmod($target, 0o644);
	}


	public function delete(Image $image): void
	{
		if ($image->getPath()) {
			FileSystem::delete($this->dir . '/' . $image->getPath());
		}
	}


	public function deleteFeed(int $feedId): void
	{
		FileSystem::delete($this->dir . '/' . $feedId);
	}


	/** Smaže všechny stažené obrázky (obsah www/files, složka zůstane) */
	public function deleteAll(): void
	{
		foreach (glob($this->dir . '/*') ?: [] as $path) {
			FileSystem::delete($path);
		}
	}


	/** Veřejná URL obrázku: lokální, pokud je stažený, jinak původní */
	public function getUrl(Image $image): string
	{
		return $image->isDone() && $image->getPath()
			? $this->url . '/' . $image->getPath()
			: $image->getSourceUrl();
	}
}
