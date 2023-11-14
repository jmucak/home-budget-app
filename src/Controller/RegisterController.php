<?php

namespace App\Controller;

use App\Entity\ExpenseCategory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $email    = $request->get('email');
        $password = $request->get('password');

        $user = new User();
        $user->setEmail($email);
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $password,
            ),
        );

        $entityManager->persist($user);

        $categories = array(
            array(
                'name' => 'Food',
            ),
            array(
                'name' => 'Bills',
            ),
            array(
                'name' => 'Rent',
            ),
        );

        foreach ($categories as $expense_category) {
            $category = new ExpenseCategory();
            $category->setName($expense_category['name']);
            $category->setUser($user);
            $entityManager->persist($category);
        }

        $entityManager->flush();

        return $this->json([
            'status'  => 200,
            'message' => 'User registered successfully',
            'user'    => [
                'username' => $user->getUserIdentifier(),
            ],
        ]);
    }
}
