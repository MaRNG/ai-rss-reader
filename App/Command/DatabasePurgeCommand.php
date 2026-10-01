<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Image\ImageStorage;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;


/**
 * Smazání dat z databáze. Vždy vyžaduje ruční potvrzení napsáním slova „smazat“;
 * bez interaktivního terminálu (-n, cron) se nic nesmaže.
 */
#[AsCommand(name: 'db:purge', description: 'Smaže data z databáze (vyžaduje potvrzení)')]
final class DatabasePurgeCommand extends Command
{
	private const string ConfirmWord = 'smazat';


	public function __construct(
		private readonly Connection $db,
		private readonly ImageStorage $imageStorage,
		private readonly LockFactory $locks,
	) {
		parent::__construct();
	}


	protected function configure(): void
	{
		$this->addOption('feeds', null, InputOption::VALUE_NONE, 'Smazat i feedy (jinak jen články a obrázky)')
			->addOption('all', null, InputOption::VALUE_NONE, 'Smazat úplně vše včetně uživatelů a nastavení')
			->setHelp(<<<'HELP'
				Bez přepínačů smaže články a obrázky (záznamy i soubory ve www/files), feedy, uživatelé a nastavení zůstanou.
				  --feeds  smaže navíc feedy
				  --all    smaže vše včetně uživatelů a nastavení (pak je potřeba znovu user:create)

				Před smazáním vypíše, co se smaže, a čeká na napsání slova „smazat“.
				HELP);
	}


	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$all = (bool) $input->getOption('all');
		$feeds = $all || (bool) $input->getOption('feeds');

		$tables = ['article_image', 'article', 'image'];
		if ($feeds) {
			$tables[] = 'feed';
		}
		if ($all) {
			array_push($tables, 'user', 'setting');
		}

		$output->writeln('<comment>Smaže se:</comment>');
		foreach (array_diff($tables, ['article_image']) as $table) {
			$count = (int) $this->db->fetchOne("SELECT COUNT(*) FROM `$table`");
			$output->writeln(sprintf('  %-8s %d záznamů', $table, $count));
		}
		$output->writeln('  soubory  všechny stažené obrázky ve www/files');
		$output->writeln('');

		if (!$input->isInteractive()) {
			$output->writeln('<error>Smazání je potřeba potvrdit v interaktivním terminálu, nic se nesmazalo.</error>');
			return self::FAILURE;
		}

		$answer = (new QuestionHelper)->ask($input, $output, new Question(sprintf('Pro potvrzení napište „%s“: ', self::ConfirmWord)));
		if (trim((string) $answer) !== self::ConfirmWord) {
			$output->writeln('Nepotvrzeno, nic se nesmazalo.');
			return self::FAILURE;
		}

		// Během mazání nesmí běžet stahování feedů ani obrázků
		$fetchLock = $this->locks->create('feeds-fetch');
		$imagesLock = $this->locks->create('images-download');
		if (!$fetchLock->acquire() || !$imagesLock->acquire()) {
			$fetchLock->release();
			$output->writeln('<error>Právě běží feeds:fetch nebo images:download, zkuste to za chvíli.</error>');
			return self::FAILURE;
		}

		try {
			$this->db->transactional(function (Connection $db) use ($tables): void {
				foreach ($tables as $table) {
					$db->executeStatement("DELETE FROM `$table`");
				}
			});
			$this->imageStorage->deleteAll();
		} finally {
			$imagesLock->release();
			$fetchLock->release();
		}

		$output->writeln('<info>Hotovo, data byla smazána.</info>');
		if ($all) {
			$output->writeln('Vytvořte nového uživatele: bin/console user:create <email>');
		}
		return self::SUCCESS;
	}
}
