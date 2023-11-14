<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\ExpenseCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExpenseCategoryRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/expense_category/{id}',
            controller: 'App\Controller\ExpenseCategoryController::getCategory',
            name: 'app_expense_category'
        ),
        new Post(
            uriTemplate: '/expense_category',
            controller: 'App\Controller\ExpenseCategoryController::addCategory',
            name: 'app_expense_category_add'
        ),
        new Patch(
            uriTemplate: '/expense_category/{id}',
            controller: 'App\Controller\ExpenseCategoryController::updateCategory',
            name: 'app_expense_category_edit'
        ),
        new Delete(
            uriTemplate: '/expense_category/{id}',
            controller: 'App\Controller\ExpenseCategoryController::deleteCategory',
            name: 'app_expense_category_delete'
        ),
        new GetCollection(
            uriTemplate: '/expense_category',
            controller: 'App\Controller\ExpenseCategoryController::index',
            name: 'app_expense_categories'
        ),
    ]
)]
class ExpenseCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Expense::class)]
    private Collection $expenses;

    #[ORM\ManyToOne(inversedBy: 'expenseCategories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function __construct()
    {
        $this->expenses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Expense>
     */
    public function getExpenses(): Collection
    {
        return $this->expenses;
    }

    public function addExpense(Expense $expense): static
    {
        if ( ! $this->expenses->contains($expense)) {
            $this->expenses->add($expense);
            $expense->setCategory($this);
        }

        return $this;
    }

    public function removeExpense(Expense $expense): static
    {
        if ($this->expenses->removeElement($expense)) {
            // set the owning side to null (unless already changed)
            if ($expense->getCategory() === $this) {
                $expense->setCategory(null);
            }
        }

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
