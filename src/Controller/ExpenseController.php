<?php

namespace App\Controller;

use App\Entity\Expense;
use App\Repository\ExpenseCategoryRepository;
use App\Repository\ExpenseRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/expense', name: 'expense_api')]
class ExpenseController extends AbstractController
{
    #[Route('/', name: 'app_expenses', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(
        ExpenseRepository $expenseRepository,
        Request $request,
    ): Response {
        $expenses = $expenseRepository->findAllByUser($this->getUser(), [
            'category' => ! empty($request->get('category')) ? $request->get('category') : '',
            'date'     => ! empty($request->get('date')) ? $request->get('date') : 'DESC',
            'limit'    => ! empty($request->get('limit')) ? $request->get('limit') : 100,
            'price'    => ! empty($request->get('price')) ? $request->get('price') : '',
        ]);

        if (empty($expenses)) {
            return $this->json([
                'message' => 'No expenses',
            ], 404);
        }

        $data = [];
        foreach ($expenses as $expense) {
            $category = $expense->getCategory();
            $data[]   = [
                'id'          => $expense->getId(),
                'description' => $expense->getDescription(),
                'amount'      => $expense->getAmount(),
                'category'    => [
                    'id'   => $category->getId(),
                    'name' => $category->getName(),
                ],
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', name: 'app_expense', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getExpense(int $id, ExpenseRepository $expenseRepository): Response
    {
        $expense = $expenseRepository->findByUser($this->getUser(), $id);

        if ( ! $expense) {
            return $this->json(array(
                'message' => 'No expense found',
            ), 404);
        }

        $category = $expense->getCategory();

        return $this->json([
            'id'          => $expense->getId(),
            'description' => $expense->getDescription(),
            'amount'      => $expense->getAmount(),
            'category'    => [
                'id'   => $category->getId(),
                'name' => $category->getName(),
            ],
        ]);
    }

    #[Route('/', name: 'app_expense_add', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function addExpense(
        Request $request,
        EntityManagerInterface $entityManager,
        ExpenseCategoryRepository $expenseCategoryRepository,
        UserRepository $userRepository,
        UserInterface $user
    ): Response {
        $category = $expenseCategoryRepository->findOneByUser(
            $this->getUser(),
            $request->get('category')
        );

        if (empty($category)) {
            return $this->json([
                'message' => 'Category not provided',
            ], 404);
        }
        $expense = new Expense();
        $expense->setCreated(new DateTime());
        $expense->setDescription($request->get('description'));
        $expense->setCategory($category);
        $expense->setAmount($request->get('amount'));

        $expense->setUser($this->getUser());

        $entityManager->persist($expense);
        $entityManager->flush();

        return $this->json([
            'id'          => $expense->getId(),
            'description' => $expense->getDescription(),
            'amount'      => $expense->getAmount(),
            'category'    => [
                'id'   => $category->getId(),
                'name' => $category->getName(),
            ],
        ]);
    }

    #[Route('/{id}', name: 'app_expense_edit', methods: ['PUT'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function updateExpense(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ExpenseRepository $expenseRepository,
        ExpenseCategoryRepository $expenseCategoryRepository
    ): Response {
        $expense = $expenseRepository->findByUser($this->getUser(), $id);

        if (empty($expense)) {
            return $this->json([
                'message' => 'No expense found',
            ], 404);
        }


        if (null !== $request->get('description')) {
            $expense->setDescription($request->get('description'));
        }

        if (null !== $request->get('amount')) {
            $expense->setAmount($request->get('amount'));
        }

        if ( ! empty($request->get('category'))) {
            $category = $expenseCategoryRepository->findOneByUser($currentUser->getId(), $request->get('category'));

            if (empty($category)) {
                return $this->json([
                    'message' => 'Wrong category ID',
                ], 404);
            }
            $expense->setCategory($category);
        }

        $entityManager->persist($expense);
        $entityManager->flush();

        $category = $expense->getCategory();

        return $this->json([
            'id'          => $expense->getId(),
            'description' => $expense->getDescription(),
            'amount'      => $expense->getAmount(),
            'category'    => [
                'id'   => $category->getId(),
                'name' => $category->getName(),
            ],
        ]);
    }

    #[Route('/{id}', name: 'app_expense_delete', methods: ['DELETE'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function deleteExpense(
        int $id,
        EntityManagerInterface $entityManager,
        ExpenseRepository $expenseRepository,
    ): Response {
        $expense = $expenseRepository->findByUser($this->getUser(), $id);

        if ( ! $expense) {
            return $this->json([
                'message' => 'Post not found',
            ], 404);
        }

        $description = $expense->getDescription();
        $entityManager->remove($expense);
        $entityManager->flush();

        return $this->json([
            'message' => sprintf('Expense "%s" successfully deleted', $description),
        ]);
    }
}
