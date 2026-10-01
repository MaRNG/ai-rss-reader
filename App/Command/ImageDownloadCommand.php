<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Database\Entity\Image;
use App\Model\Image\ImageDownloader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(name: 'images:download', description: 'Stáhne čekající obrázky článků do www/files')]
final class ImageDownloadCommand extends Command
{
	public function __construct(
		private readonly ImageDownloader $downloader,
		private readonly LockFactory $locks,
	) {
		parent::__construct();
	}


	protected function configure(): void
	{
		$this->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximální počet obrázků', '200');
	}


	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$lock = $this->locks->create('images-download');
		if (!$lock->acquire()) {
			$output->writeln('images:download už běží, končím.');
			return self::SUCCESS;
		}

		try {
			$stats = $this->downloader->downloadPending(
				max(1, (int) $input->getOption('limit')),
				function (Image $image, ?string $error) use ($output): void {
					if ($error !== null) {
						$output->writeln("<comment>{$image->getSourceUrl()}: $error</comment>", OutputInterface::VERBOSITY_VERBOSE);
					}
				},
			);
			$output->writeln(sprintf('Hotovo: %d stažených, %d chyb.', $stats['downloaded'], $stats['failed']));
			return self::SUCCESS;
		} finally {
			$lock->release();
		}
	}
}
