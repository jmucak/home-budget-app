<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'title' => 'Hello to Home Budget App',
            'description' => 'The "Symfony Home Budget App" is a budgeting app for easier expense tracking. Connect to API and start tracking your expenses'
        ]);
    }
}
