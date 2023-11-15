<?php

namespace App\Controller;

use App\Entity\ExpenseCategory;
use App\Repository\ExpenseCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

#[Route('/api/expense_category', name: 'expense_category_api')]
class ExpenseCategoryController extends AbstractController
{
    #[Route('/', name: 'app_expense_categories', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[OA\Response(
        response: 200,
        description: 'Returns the expense categories of an user'
    )]
    #[OA\Tag(name: 'Expense Category')]
    #[Security(name: 'Bearer')]
    public function index(ExpenseCategoryRepository $expenseCategoryRepository): JsonResponse
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
    #[OA\Response(
        response: 200,
        description: 'Returns the expense category of an user'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'The field used to get expense category by id',
        in: 'path',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Tag(name: 'Expense Category')]
    #[Security(name: 'Bearer')]
    public function getCategory(int $id, ExpenseCategoryRepository $expenseCategoryRepository): JsonResponse
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
    #[OA\Response(
        response: 200,
        description: 'Add new expense category for the user'
    )]
    #[OA\Parameter(
        name: 'name',
        description: 'The field used to get expense category by id',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'Expense Category')]
    #[Security(name: 'Bearer')]
    public function addCategory(
        Request $request,
        ExpenseCategoryRepository $expenseCategoryRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        if(empty($request->get('name'))) {
            return $this->json([
                'message' => 'Parameter name not provided',
            ], 404);
        }

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

    #[Route('/{id}', name: 'app_expense_category_edit', methods: ['PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[OA\Response(
        response: 200,
        description: 'Update existing expense category for the user'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'The field used to get expense category by id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'name',
        description: 'The field used for category name',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'Expense Category')]
    #[Security(name: 'Bearer')]
    public function updateCategory(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ExpenseCategoryRepository $expenseCategoryRepository
    ): JsonResponse {
        if(empty($request->get('name'))) {
            return $this->json([
                'message' => 'Parameter name not provided',
            ], 404);
        }
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
    #[OA\Response(
        response: 200,
        description: 'Delete expense category for the user'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'The field used to get expense category by id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Tag(name: 'Expense Category')]
    #[Security(name: 'Bearer')]
    public function deleteCategory(
        int $id,
        EntityManagerInterface $entityManager,
        ExpenseCategoryRepository $expenseCategoryRepository
    ): JsonResponse {
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
