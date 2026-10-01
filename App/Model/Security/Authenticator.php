<?php

declare(strict_types=1);

namespace App\Model\Security;

use App\Model\Database\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Security\AuthenticationException;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;


final class Authenticator implements \Nette\Security\Authenticator, \Nette\Security\IdentityHandler
{
	public function __construct(
		private readonly UserRepository $users,
		private readonly Passwords $passwords,
		private readonly EntityManagerInterface $em,
	) {
	}


	public function authenticate(string $username, string $password): IIdentity
	{
		$user = $this->users->findByEmail($username);
		if (!$user || !$user->isActive() || !$this->passwords->verify($password, $user->getPasswordHash())) {
			throw new AuthenticationException('Nesprávný e-mail nebo heslo.');
		}

		if ($this->passwords->needsRehash($user->getPasswordHash())) {
			$user->setPasswordHash($this->passwords->hash($password));
		}
		$user->markLoggedIn();
		$this->em->flush();

		return new SimpleIdentity($user->getId(), [], ['name' => $user->getName(), 'email' => $user->getEmail()]);
	}


	public function sleepIdentity(IIdentity $identity): IIdentity
	{
		return new SimpleIdentity($identity->getId());
	}


	/** Při každém požadavku ověří, že uživatel pořád existuje a je aktivní */
	public function wakeupIdentity(IIdentity $identity): ?IIdentity
	{
		$user = $this->users->find((int) $identity->getId());
		return $user && $user->isActive()
			? new SimpleIdentity($user->getId(), [], ['name' => $user->getName(), 'email' => $user->getEmail(), 'initials' => $user->getInitials()])
			: null;
	}
}
