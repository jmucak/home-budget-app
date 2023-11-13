<?php

namespace App\Controller;

use App\Entity\Expense;
use App\Entity\ExpenseCategory;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExpenseController extends AbstractController
{
    #[Route('/expense', name: 'app_expense')]
    public function index(EntityManagerInterface $entityManager): Response
    {
//        $category = new ExpenseCategory();
//        $category->setName('Food');
//
//        $expense = new Expense();
//        $expense->setAmount('11.22');
//        $expense->setDescription('Food supplies');
//        $expense->setCreated(new DateTime());
//        $expense->setCategory($category);
//
//        $entityManager->persist($expense);
//        $entityManager->flush();

        return $this->render('expense/index.html.twig', [
            'controller_name' => 'ExpenseController',
        ]);
    }
}
