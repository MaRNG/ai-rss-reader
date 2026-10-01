<?php

declare(strict_types=1);

namespace App\UI\Shared;

use App\Model\Database\Entity\Feed;
use App\Model\Database\Entity\Image;
use App\Model\Image\ImageStorage;
use DateTimeInterface;
use Latte\Extension;


/**
 * Filtry pro šablony: české datumy, URL obrázků, skloňování.
 */
final class LatteExtension extends Extension
{
	private const array Days = ['neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota'];
	private const array Months = [1 => 'ledna', 'února', 'března', 'dubna', 'května', 'června', 'července', 'srpna', 'září', 'října', 'listopadu', 'prosince'];


	public function __construct(
		private readonly ImageStorage $imageStorage,
	) {
	}


	public function getFilters(): array
	{
		return [
			'imageUrl' => fn(Image $image): string => $this->imageStorage->getUrl($image),
			'favicon' => self::favicon(...),
			'czDate' => self::czDate(...),
			'dayLabel' => self::dayLabel(...),
			'shortTime' => self::shortTime(...),
			'plural' => self::plural(...),
		];
	}


	/** Favikona webu feedu (služba Google, 64 px) */
	public static function favicon(Feed $feed): string
	{
		return 'https://www.google.com/s2/favicons?domain=' . rawurlencode($feed->getHost()) . '&sz=64';
	}


	/** „středa 1. října“, případně s rokem, pokud není letošní */
	public static function czDate(DateTimeInterface $date): string
	{
		$text = self::Days[(int) $date->format('w')] . ' ' . $date->format('j') . '. ' . self::Months[(int) $date->format('n')];
		return $date->format('Y') === date('Y') ? $text : $text . ' ' . $date->format('Y');
	}


	public static function dayLabel(DateTimeInterface $date): string
	{
		return match ($date->format('Y-m-d')) {
			date('Y-m-d') => 'Dnes',
			date('Y-m-d', strtotime('-1 day')) => 'Včera',
			default => mb_convert_case(self::Days[(int) $date->format('w')], MB_CASE_TITLE),
		};
	}


	/** Čas pro řádek článku: dnes jen „7:52“, jinak „včera 7:52“ / „28. 9.“ */
	public static function shortTime(DateTimeInterface $date, bool $withDay = true): string
	{
		$time = $date->format('G:i');
		if (!$withDay || $date->format('Y-m-d') === date('Y-m-d')) {
			return $time;
		}
		if ($date->format('Y-m-d') === date('Y-m-d', strtotime('-1 day'))) {
			return "včera $time";
		}
		return $date->format('Y') === date('Y') ? $date->format('j. n.') : $date->format('j. n. Y');
	}


	/** České skloňování: 1 článek, 2–4 články, 5+ článků */
	public static function plural(int $count, string $one, string $few, string $many): string
	{
		return match (true) {
			$count === 1 => $one,
			$count >= 2 && $count <= 4 => $few,
			default => $many,
		};
	}
}
