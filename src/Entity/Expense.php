<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\ExpenseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExpenseRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/expense/{id}',
            controller: 'App\Controller\ExpenseController::getExpense',
            name: 'app_expense'
        ),
        new Post(
            uriTemplate: '/expense',
            controller: 'App\Controller\ExpenseController::addExpense',
            name: 'app_expense_add'
        ),
        new Patch(
            uriTemplate: '/expense/{id}',
            controller: 'App\Controller\ExpenseController::updateExpense',
            name: 'app_expense_edit'
        ),
        new Delete(
            uriTemplate: '/expense/{id}',
            controller: 'App\Controller\ExpenseController::deleteExpense',
            name: 'app_expense_delete'
        ),
        new GetCollection(
            uriTemplate: '/expense',
            controller: 'App\Controller\ExpenseController::index',
            name: 'app_expenses'
        ),
    ]
)]
class Expense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'expenses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ExpenseCategory $category = null;

    #[ORM\ManyToOne(inversedBy: 'expenses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): static
    {
        $this->created = $created;

        return $this;
    }

    public function getCategory(): ?ExpenseCategory
    {
        return $this->category;
    }

    public function setCategory(?ExpenseCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
