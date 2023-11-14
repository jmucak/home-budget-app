<?php

namespace App\Controller;

use App\Entity\ExpenseCategory;
use App\Repository\ExpenseCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/expense/category', name: 'expense_category_api')]
class ExpenseCategoryController extends AbstractController
{
    #[Route('/', name: 'app_expense_categories', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(ExpenseCategoryRepository $expenseCategoryRepository): Response
    {
        $categories = $expenseCategoryRepository->findByUser($this->getUser());

        if (empty($categories)) {
            return $this->json([
                'message' => 'No categories found',
            ], 404);
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
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getCategory(int $id, ExpenseCategoryRepository $expenseCategoryRepository): Response
    {
        $category = $expenseCategoryRepository->findOneByUser($this->getUser(), $id);

        if ( ! $category) {
            return $this->json([
                'message' => 'Category not found',
            ], 404);
        }

        return $this->json([
            'id'   => $category->getId(),
            'name' => $category->getName(),
        ]);
    }

    #[Route('/', name: 'app_expense_category_add', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function addCategory(
        Request $request,
        ExpenseCategoryRepository $expenseCategoryRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $category = new ExpenseCategory();
        $category->setName($request->get('name'));
        $category->setUser($this->getUser());
        $entityManager->persist($category);
        $entityManager->flush();

        return $this->json([
            'id'   => $category->getId(),
            'name' => $category->getName(),
        ]);
    }

    #[Route('/{id}', name: 'app_expense_category_edit', methods: ['PUT'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function updateCategory(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ExpenseCategoryRepository $expenseCategoryRepository
    ): Response {
        $category = $expenseCategoryRepository->findOneByUser($this->getUser(), $id);

        if (empty($category)) {
            return $this->json([
                'message' => 'Category not found',
            ], 404);
        }

        $category->setName($request->get('name'));
        $entityManager->persist($category);
        $entityManager->flush();

        return $this->json([
            'id'   => $category->getId(),
            'name' => $category->getName(),
        ]);
    }

    #[Route('/{id}', name: 'app_expense_category_delete', methods: ['DELETE'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function deleteCategory(
        int $id,
        EntityManagerInterface $entityManager,
        ExpenseCategoryRepository $expenseCategoryRepository
    ): Response {
        $category = $expenseCategoryRepository->findOneByUser($this->getUser(), $id);

        if (empty($category)) {
            return $this->json([
                'message' => 'Category not found',
            ], 404);
        }

        $name = $category->getName();
        $entityManager->remove($category);
        $entityManager->flush();

        return $this->json([
            'message' => sprintf('Category "%s" successfully deleted', $name),
        ]);
    }
}
