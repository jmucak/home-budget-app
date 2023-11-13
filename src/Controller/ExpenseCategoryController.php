<?php

namespace App\Controller;

use App\Entity\ExpenseCategory;
use App\Repository\ExpenseCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/expense/category', name: 'expense_category_api')]
class ExpenseCategoryController extends AbstractController
{
    #[Route('/', name: 'app_expense_categories', methods: ['GET'])]
    public function index(ExpenseCategoryRepository $expenseCategoryRepository): Response
    {
        $categories = $expenseCategoryRepository->findAll();

        if (empty($categories)) {
            return $this->json(array(
                'status'  => '404',
                'message' => 'No categories found',
            ));
        }

        $data = array();

        foreach ($categories as $category) {
            $data[] = array(
                'id'   => $category->getId(),
                'name' => $category->getName(),
            );
        }

        return $this->json($data);
    }

    #[Route('/{id}', name: 'app_expense_category', methods: ['GET'])]
    public function getCategory(int $id, ExpenseCategoryRepository $expenseCategoryRepository): Response
    {
        $category = $expenseCategoryRepository->find($id);

        if ( ! $category) {
            return $this->json(array(
                'status'  => '404',
                'message' => 'No found category',
            ));
        }

        return $this->json(array(
            'id'   => $category->getId(),
            'name' => $category->getName(),
        ));
    }

    #[Route('/', name: 'app_expense_category_add', methods: ['POST'])]
    public function addCategory(
        Request $request,
        ExpenseCategoryRepository $expenseCategoryRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $category = new ExpenseCategory();
        $category->setName($request->get('name'));
        $entityManager->persist($category);
        $entityManager->flush();

        return $this->json(array(
            'message' => 'Category '.$category->getName().' added',
        ));
    }

    #[Route('/{id}', name: 'app_expense_category_edit', methods: ['PUT'])]
    public function updateCategory(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ExpenseCategoryRepository $expenseCategoryRepository
    ): Response {
        $category = $expenseCategoryRepository->find($id);

        if (empty($request->get('name'))) {
            return $this->json(array(
                'status'  => '404',
                'message' => 'No name for category provided',
            ));
        }

        $category->setName($request->get('name'));
        $entityManager->persist($category);
        $entityManager->flush();

        return $this->json(array(
            'message'  => 'Category '.$category->getName().' updated',
            'category' => array(
                'id'   => $category->getId(),
                'name' => $category->getName(),
            ),
        ));
    }

    #[Route('/{id}', name: 'app_expense_category_delete', methods: ['DELETE'])]
    public function deleteCategory(
        int $id,
        EntityManagerInterface $entityManager,
        ExpenseCategoryRepository $expenseCategoryRepository
    ): Response {
        $category = $expenseCategoryRepository->find($id);

        if ( ! $category) {
            return $this->json(array(
                'status'  => '404',
                'message' => 'No category found',
            ));
        }

        $entityManager->remove($category);
        $entityManager->flush();

        return $this->json(array(
            'status'  => 200,
            'message' => 'Category deleted',
        ));
    }
}
