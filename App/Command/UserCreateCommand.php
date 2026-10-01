<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Security\UserException;
use App\Model\Security\UserFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;


#[AsCommand(name: 'user:create', description: 'Vytvoří uživatele (heslo se zadá interaktivně)')]
final class UserCreateCommand extends Command
{
	public function __construct(
		private readonly UserFacade $users,
	) {
		parent::__construct();
	}


	protected function configure(): void
	{
		$this->addArgument('email', InputArgument::REQUIRED, 'E-mail (přihlašovací jméno)')
			->addOption('name', null, InputOption::VALUE_REQUIRED, 'Jméno');
	}


	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$helper = new QuestionHelper;
		$question = (new Question('Heslo: '))->setHidden(true)->setHiddenFallback(false);
		$password = (string) $helper->ask($input, $output, $question);
		$confirm = (string) $helper->ask($input, $output, (new Question('Heslo znovu: '))->setHidden(true)->setHiddenFallback(false));
		if ($password !== $confirm) {
			$output->writeln('<error>Hesla se neshodují.</error>');
			return self::FAILURE;
		}

		try {
			$user = $this->users->create((string) $input->getArgument('email'), (string) $input->getOption('name'), $password);
		} catch (UserException $e) {
			$output->writeln('<error>' . $e->getMessage() . '</error>');
			return self::FAILURE;
		}

		$output->writeln(sprintf('Uživatel <info>%s</info> vytvořen (ID %d).', $user->getEmail(), $user->getId()));
		return self::SUCCESS;
	}
}
