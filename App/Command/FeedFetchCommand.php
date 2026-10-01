<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Database\Repository\FeedRepository;
use App\Model\Feed\FeedFetcher;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\Debugger;


#[AsCommand(name: 'feeds:fetch', description: 'Stáhne aktivní feedy a uloží nové články')]
final class FeedFetchCommand extends Command
{
	public function __construct(
		private readonly FeedRepository $feeds,
		private readonly FeedFetcher $fetcher,
		private readonly LockFactory $locks,
	) {
		parent::__construct();
	}


	protected function configure(): void
	{
		$this->addOption('feed', null, InputOption::VALUE_REQUIRED, 'Jen feed s tímto ID')
			->addOption('force', null, InputOption::VALUE_NONE, 'Ignorovat ETag/Last-Modified');
	}


	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$lock = $this->locks->create('feeds-fetch');
		if (!$lock->acquire()) {
			$output->writeln('feeds:fetch už běží, končím.');
			return self::SUCCESS;
		}

		try {
			$id = $input->getOption('feed');
			$feeds = $id !== null ? array_filter([$this->feeds->find((int) $id)]) : $this->feeds->findActive();
			$total = 0;

			foreach ($feeds as $feed) {
				$result = $this->fetcher->fetch($feed, (bool) $input->getOption('force'));
				$total += $result->newArticles;

				if ($result->error) {
					Debugger::log("Feed {$feed->getId()} ({$feed->getUrl()}): {$result->error}", 'feeds');
					$output->writeln(sprintf('<error>%s: %s</error>%s', $feed->getTitle(), $result->error, $result->deactivated ? ' (feed deaktivován)' : ''));
				} elseif ($result->notModified) {
					$output->writeln(sprintf('%s: beze změny', $feed->getTitle()), OutputInterface::VERBOSITY_VERBOSE);
				} else {
					$output->writeln(sprintf('%s: %d nových', $feed->getTitle(), $result->newArticles), OutputInterface::VERBOSITY_VERBOSE);
				}
			}

			$output->writeln(sprintf('Hotovo: %d feedů, %d nových článků.', count($feeds), $total));
			return self::SUCCESS;
		} finally {
			$lock->release();
		}
	}
}
