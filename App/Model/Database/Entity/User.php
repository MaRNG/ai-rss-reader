<?php

declare(strict_types=1);

namespace App\Model\Database\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: 'user')]
class User
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\Column(length: 255, unique: true)]
	private string $email;

	#[ORM\Column(length: 255)]
	private string $name;

	#[ORM\Column(length: 255)]
	private string $passwordHash;

	#[ORM\Column]
	private bool $active = true;

	#[ORM\Column]
	private DateTimeImmutable $createdAt;

	#[ORM\Column(nullable: true)]
	private ?DateTimeImmutable $lastLoginAt = null;


	public function __construct(string $email, string $name, string $passwordHash)
	{
		$this->email = $email;
		$this->name = $name;
		$this->passwordHash = $passwordHash;
		$this->createdAt = new DateTimeImmutable;
	}


	public function getId(): int
	{
		return $this->id ?? throw new \LogicException('User is not persisted.');
	}


	public function getEmail(): string
	{
		return $this->email;
	}


	public function setEmail(string $email): void
	{
		$this->email = $email;
	}


	public function getName(): string
	{
		return $this->name;
	}


	public function setName(string $name): void
	{
		$this->name = $name;
	}


	public function getPasswordHash(): string
	{
		return $this->passwordHash;
	}


	public function setPasswordHash(string $passwordHash): void
	{
		$this->passwordHash = $passwordHash;
	}


	public function isActive(): bool
	{
		return $this->active;
	}


	public function setActive(bool $active): void
	{
		$this->active = $active;
	}


	public function getCreatedAt(): DateTimeImmutable
	{
		return $this->createdAt;
	}


	public function getLastLoginAt(): ?DateTimeImmutable
	{
		return $this->lastLoginAt;
	}


	public function markLoggedIn(): void
	{
		$this->lastLoginAt = new DateTimeImmutable;
	}


	public function getInitials(): string
	{
		$parts = preg_split('~\s+~u', trim($this->name)) ?: [];
		$initials = '';
		foreach (array_slice($parts, 0, 2) as $part) {
			$initials .= mb_strtoupper(mb_substr($part, 0, 1));
		}
		return $initials !== '' ? $initials : mb_strtoupper(mb_substr($this->email, 0, 1));
	}
}
