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

#[Route('/api/expense', name: 'expense_api')]
class ExpenseController extends AbstractController
{
    #[Route('/', name: 'app_expenses', methods: ['GET'])]
    public function index(ExpenseRepository $expenseRepository): Response
    {
        $expenses = $expenseRepository->findAll();

        if (empty($expenses)) {
            return $this->json(array(
                'message' => 'No expenses',
            ));
        }

        $data = array();
        foreach ($expenses as $expense) {
            $category = $expense->getCategory();
            $data[]   = array(
                'id'          => $expense->getId(),
                'description' => $expense->getDescription(),
                'amount'      => $expense->getAmount(),
                'category'    => array(
                    'id'   => $category->getId(),
                    'name' => $category->getName(),
                ),
                'created'     => $expense->getCreated(),
            );
        }

        return $this->json($data);
    }

    #[Route('/', name: 'app_expense', methods: ['GET'])]
    public function getExpense(int $id, ExpenseRepository $expenseRepository): Response
    {
        $expense = $expenseRepository->find($id);

        if ( ! $expense) {
            return $this->json(array(
                'status'  => 404,
                'message' => 'No expense found',
            ));
        }

        $category = $expense->getCategory();

        return $this->json(array(
            'status'  => 200,
            'expense' => array(
                'id'          => $expense->getId(),
                'description' => $expense->getDescription(),
                'amount'      => $expense->getAmount(),
                'created'     => $expense->getCreated(),
                'category'    => array(
                    'id'   => $category->getId(),
                    'name' => $category->getName(),
                ),
            ),
        ));
    }

    #[Route('/', name: 'app_expense_add', methods: ['POST'])]
    public function addExpense(
        Request $request,
        EntityManagerInterface $entityManager,
        ExpenseCategoryRepository $expenseCategoryRepository,
        UserRepository $userRepository,
    ): Response {

        $expense  = new Expense();
        $category = $expenseCategoryRepository->find($request->get('category'));
        $expense->setCreated(new DateTime());
        $expense->setDescription($request->get('description'));
        $expense->setCategory($category);
        $expense->setAmount($request->get('amount'));

        $user = $userRepository->find($request->get('user'));
        $expense->setUser($user);

        $entityManager->persist($expense);
        $entityManager->flush();

        return $this->json(array(
            'message' => 'Expense '.$expense->getDescription().' added to database',
        ));
    }

    #[Route('/{id}', name: 'app_expense_edit', methods: ['PUT'])]
    public function updateExpense(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ExpenseRepository $expenseRepository,
        ExpenseCategoryRepository $expenseCategoryRepository
    ): Response {
        $expense = $expenseRepository->find($id);

        if ( ! empty($request->get('description'))) {
            $expense->setDescription($request->get('description'));
        }

        if ( ! empty($request->get('amount'))) {
            $expense->setAmount($request->get('amount'));
        }

        if ( ! empty($request->get('category'))) {
            $category = $expenseCategoryRepository->find($request->get('category'));
            $expense->setCategory($category);
        }

        $entityManager->persist($expense);
        $entityManager->flush();

        return $this->json(array(
            'message' => 'Expense successfully updated',
        ));
    }

    #[Route('/{id}', name: 'app_expense_delete', methods: ['DELETE'])]
    public function deleteExpense(
        int $id,
        EntityManagerInterface $entityManager,
        ExpenseRepository $expenseRepository,
    ): Response {
        $expense = $expenseRepository->find($id);

        if ( ! $expense) {
            return $this->json(array(
                'status'  => 404,
                'message' => 'Post not found',
            ));
        }

        $entityManager->remove($expense);
        $entityManager->flush();

        return $this->json(array(
            'status'  => 200,
            'message' => 'Expense deleted successfully',
        ));
    }
}
