<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Feed\FeedException;
use App\Model\Feed\FeedFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(name: 'feeds:add', description: 'Přidá feed, ověří ho a stáhne články')]
final class FeedAddCommand extends Command
{
	public function __construct(
		private readonly FeedFacade $feeds,
	) {
		parent::__construct();
	}


	protected function configure(): void
	{
		$this->addArgument('url', InputArgument::REQUIRED, 'URL feedu')
			->addOption('title', null, InputOption::VALUE_REQUIRED, 'Vlastní název')
			->addOption('full-text', null, InputOption::VALUE_NONE, 'Stahovat plný text článků (pro feedy s jen perexem)');
	}


	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		try {
			$feed = $this->feeds->add(
				(string) $input->getArgument('url'),
				$input->getOption('title'),
				(bool) $input->getOption('full-text'),
			);
		} catch (FeedException $e) {
			$output->writeln('<error>' . $e->getMessage() . '</error>');
			return self::FAILURE;
		}

		$output->writeln(sprintf('Feed <info>%s</info> přidán (ID %d).', $feed->getTitle(), $feed->getId()));
		return self::SUCCESS;
	}
}
