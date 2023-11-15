<?php

namespace App\Controller;

use App\Repository\ExpenseRepository;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

#[Route('/api/data')]
#[AsController]
class DataAggregationController extends AbstractController
{
    #[Route('/aggregation', name: 'app_data_aggregation', methods: 'GET')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[OA\Response(
        response: 200,
        description: 'Get total money spent',
    )]
    #[OA\Parameter(
        name: 'date',
        description: 'The field used to get data by date',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', enum: ['month', 'quarter', 'year'])
    )]
    #[OA\Tag(name: 'Data aggregation')]
    #[Security(name: 'Bearer')]
    public function getMoneySpent(
        ExpenseRepository $expenseRepository,
        Request $request
    ): JsonResponse {
        $expenses = $expenseRepository->findAllByUser($this->getUser(), [
            'date' => $request->get('date'),
        ]);

        $total = 0;
        foreach ($expenses as $expense) {
            $total += $expense->getAmount();
        }

        return $this->json([
            'total' => number_format($total, 2),
        ]);
    }
}
