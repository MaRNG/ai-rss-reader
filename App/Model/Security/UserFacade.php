<?php

declare(strict_types=1);

namespace App\Model\Security;

use App\Model\Database\Entity\User;
use App\Model\Database\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Security\Passwords;
use Nette\Utils\Validators;


/**
 * Zakládání a úpravy uživatelů – sdílí administrace i CLI (user:create).
 */
final class UserFacade
{
	public const int MinPasswordLength = 8;


	public function __construct(
		private readonly UserRepository $users,
		private readonly Passwords $passwords,
		private readonly EntityManagerInterface $em,
	) {
	}


	/** @throws UserException */
	public function create(string $email, string $name, string $password): User
	{
		$email = $this->normalizeEmail($email);
		if ($this->users->findByEmail($email)) {
			throw new UserException("Uživatel s e-mailem $email už existuje.");
		}
		$this->validatePassword($password);

		$user = new User($email, trim($name) !== '' ? trim($name) : $email, $this->passwords->hash($password));
		$this->em->persist($user);
		$this->em->flush();
		return $user;
	}


	/** @throws UserException */
	public function update(User $user, string $email, string $name, bool $active, ?string $password, ?int $currentUserId): void
	{
		$email = $this->normalizeEmail($email);
		$other = $this->users->findByEmail($email);
		if ($other && $other->getId() !== $user->getId()) {
			throw new UserException("Uživatel s e-mailem $email už existuje.");
		}
		if (!$active && $user->getId() === $currentUserId) {
			throw new UserException('Nemůžete deaktivovat sami sebe.');
		}
		if ($password !== null && $password !== '') {
			$this->validatePassword($password);
			$user->setPasswordHash($this->passwords->hash($password));
		}
		$user->setEmail($email);
		$user->setName(trim($name) !== '' ? trim($name) : $email);
		$user->setActive($active);
		$this->em->flush();
	}


	/** @throws UserException */
	public function delete(User $user, ?int $currentUserId): void
	{
		if ($user->getId() === $currentUserId) {
			throw new UserException('Nemůžete smazat sami sebe.');
		}
		$this->em->remove($user);
		$this->em->flush();
	}


	/** @throws UserException */
	private function normalizeEmail(string $email): string
	{
		$email = mb_strtolower(trim($email));
		if (!Validators::isEmail($email)) {
			throw new UserException("Neplatný e-mail '$email'.");
		}
		return $email;
	}


	/** @throws UserException */
	private function validatePassword(string $password): void
	{
		if (mb_strlen($password) < self::MinPasswordLength) {
			throw new UserException('Heslo musí mít alespoň ' . self::MinPasswordLength . ' znaků.');
		}
	}
}
