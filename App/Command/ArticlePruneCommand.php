<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Database\Repository\ArticleRepository;
use App\Model\Database\Repository\ImageRepository;
use App\Model\Image\ImageStorage;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(name: 'articles:prune', description: 'Smaže staré články (kromě hvězdičkových) a nepoužívané obrázky')]
final class ArticlePruneCommand extends Command
{
	public function __construct(
		private readonly ArticleRepository $articles,
		private readonly ImageRepository $images,
		private readonly ImageStorage $storage,
		private readonly EntityManagerInterface $em,
		private readonly LockFactory $locks,
	) {
		parent::__construct();
	}


	protected function configure(): void
	{
		$this->addOption('days', null, InputOption::VALUE_REQUIRED, 'Smazat články stažené před více než N dny', '90');
	}


	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$lock = $this->locks->create('articles-prune');
		if (!$lock->acquire()) {
			$output->writeln('articles:prune už běží, končím.');
			return self::SUCCESS;
		}

		try {
			$days = max(1, (int) $input->getOption('days'));
			$deleted = $this->articles->deleteOlderThan(new DateTimeImmutable("-$days days"));

			$orphans = $this->images->findOrphans();
			foreach ($orphans as $image) {
				$this->storage->delete($image);
				$this->em->remove($image);
			}
			$this->em->flush();

			$output->writeln(sprintf('Smazáno %d článků a %d obrázků.', $deleted, count($orphans)));
			return self::SUCCESS;
		} finally {
			$lock->release();
		}
	}
}
