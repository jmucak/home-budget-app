<?php

namespace App\Controller;

use App\Repository\ExpenseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/data')]
#[AsController]
class DataAggregationController extends AbstractController
{
    #[Route('/aggregation', name: 'app_data_aggregation', methods: 'GET')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getMoneySpent(
        ExpenseRepository $expenseRepository,
        Request $request
    ): Response {
        $expenses = $expenseRepository->findAllByUser($this->getUser(), [
            'date' => $request->get('date'),
        ]);

        $total = 0;
        foreach ($expenses as $expense) {
            $total += $expense->getAmount();
        }

        return $this->json([
            'total' => $total,
        ]);
    }
}
