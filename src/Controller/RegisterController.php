<?php

namespace App\Controller;

use App\Entity\ExpenseCategory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    #[Route('/api/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password'])) {
            return $this->json([
                'message' => 'Email or password not provided',
            ], 404);
        }

        $email    = $data['email'];
        $password = $data['password'];

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
            'message' => sprintf('User %s registered successfully', $user->getUserIdentifier()),
        ]);
    }
}
