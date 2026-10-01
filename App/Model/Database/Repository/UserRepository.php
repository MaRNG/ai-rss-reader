<?php

declare(strict_types=1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\User;
use Doctrine\ORM\EntityManagerInterface;


final class UserRepository
{
	public function __construct(
		private readonly EntityManagerInterface $em,
	) {
	}


	public function find(int $id): ?User
	{
		return $this->em->find(User::class, $id);
	}


	public function findByEmail(string $email): ?User
	{
		return $this->em->getRepository(User::class)->findOneBy(['email' => mb_strtolower(trim($email))]);
	}


	/** @return list<User> */
	public function findAll(): array
	{
		return $this->em->getRepository(User::class)->findBy([], ['name' => 'ASC']);
	}
}
