<?php

namespace App\Controller;

use App\Entity\Expense;
use App\Repository\ExpenseCategoryRepository;
use App\Repository\ExpenseRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

#[Route('/api/expense', name: 'expense_api')]
class ExpenseController extends AbstractController
{
    #[Route('/', name: 'app_expenses', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[OA\Response(
        response: 200,
        description: 'Returns the expenses of an user'
    )]
    #[OA\Parameter(
        name: 'category',
        description: 'The field used to get expenses by category id',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'order_by',
        description: 'The field used to get expenses and order by',
        in: 'query',
        schema: new OA\Schema(type: 'string', enum: ['price_asc', 'price_desc', 'date_asc', 'date_desc'])
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'The field used to limit expenses',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: 100)
    )]
    #[OA\Tag(name: 'Expenses')]
    #[Security(name: 'Bearer')]
    public function index(
        ExpenseRepository $expenseRepository,
        Request $request,
    ): JsonResponse {
        $expenses = $expenseRepository->findAllByUser($this->getUser(), [
            'category' => ! empty($request->get('category')) ? $request->get('category') : '',
            'order_by' => ! empty($request->get('order_by')) ? $request->get('order_by') : 'price_desc',
            'limit'    => ! empty($request->get('limit')) ? $request->get('limit') : 100,
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
    #[OA\Response(
        response: 200,
        description: 'Returns the expense of an user by expense id'
    )]
    #[OA\Tag(name: 'Expenses')]
    #[Security(name: 'Bearer')]
    public function getExpense(int $id, ExpenseRepository $expenseRepository): JsonResponse
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
    #[OA\Response(
        response: 200,
        description: 'Add expense for the user',
    )]
    #[OA\Parameter(
        name: 'category',
        description: 'The field used to add expense to a category',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'amount',
        description: 'The field used to add expense amount',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'description',
        description: 'The field used to add expense description',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'Expenses')]
    #[Security(name: 'Bearer')]
    public function addExpense(
        Request $request,
        EntityManagerInterface $entityManager,
        ExpenseCategoryRepository $expenseCategoryRepository,
        UserRepository $userRepository,
        UserInterface $user
    ): JsonResponse {
        if (empty($request->get('category')) || empty($request->get('amount')) || empty($request->get('description'))) {
            return $this->json([
                'message' => 'Category, description and amount are required fields',
            ], 404);
        }

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

    #[Route('/{id}', name: 'app_expense_edit', methods: ['PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[OA\Response(
        response: 200,
        description: 'Update existing expense for the user',
    )]
    #[OA\Parameter(
        name: 'category',
        description: 'The field used to update expense to a category',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'amount',
        description: 'The field used to update expense amount',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'description',
        description: 'The field used to update expense description',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'Expenses')]
    #[Security(name: 'Bearer')]
    public function updateExpense(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ExpenseRepository $expenseRepository,
        ExpenseCategoryRepository $expenseCategoryRepository
    ): JsonResponse {
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
            $category = $expenseCategoryRepository->findOneByUser($this->getUser(), $request->get('category'));

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
    #[OA\Response(
        response: 200,
        description: 'Delete expense for the user',
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'The field used to delete correct expense',
        in: 'path',
        required: false,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Tag(name: 'Expenses')]
    #[Security(name: 'Bearer')]
    public function deleteExpense(
        int $id,
        EntityManagerInterface $entityManager,
        ExpenseRepository $expenseRepository,
    ): JsonResponse {
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
