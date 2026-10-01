<?php

declare(strict_types=1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Article;
use App\Model\Database\Entity\Feed;
use App\Model\Database\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;


final class ImageRepository
{
	public function __construct(
		private readonly EntityManagerInterface $em,
	) {
	}


	/**
	 * Nově založené (zatím neuložené) obrázky, aby stejná URL ve dvou článcích nevytvořila duplicitu
	 * @var array<string, Image>
	 */
	private array $created = [];


	public function findOrCreate(Feed $feed, string $sourceUrl): Image
	{
		$hash = Image::hashUrl($sourceUrl);
		$key = $feed->getId() . ':' . $hash;
		if (isset($this->created[$key]) && $this->em->contains($this->created[$key])) {
			return $this->created[$key];
		}

		$image = $this->em->getRepository(Image::class)->findOneBy(['feed' => $feed, 'hash' => $hash]);
		if (!$image) {
			$image = new Image($feed, $sourceUrl);
			$this->em->persist($image);
			$this->created[$key] = $image;
		}
		return $image;
	}


	/** @return list<Image> */
	public function findPending(int $limit): array
	{
		return $this->em->getRepository(Image::class)->findBy(
			['status' => Image::StatusPending],
			['id' => 'ASC'],
			$limit,
		);
	}


	/** @return list<Article> */
	public function findArticlesUsing(Image $image): array
	{
		return $this->em->createQueryBuilder()
			->select('a')
			->from(Article::class, 'a')
			->join('a.images', 'i')
			->where('i = :image')
			->setParameter('image', $image)
			->getQuery()
			->getResult();
	}


	/**
	 * Obrázky, na které nevede žádný článek.
	 * @return list<Image>
	 */
	public function findOrphans(): array
	{
		return $this->em->createQueryBuilder()
			->select('i')
			->from(Image::class, 'i')
			->where('NOT EXISTS (SELECT 1 FROM ' . Article::class . ' a JOIN a.images ai WHERE ai = i)')
			->andWhere('NOT EXISTS (SELECT 1 FROM ' . Article::class . ' m WHERE m.mainImage = i)')
			->getQuery()
			->getResult();
	}
}
