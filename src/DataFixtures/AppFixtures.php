<?php

namespace App\DataFixtures;

use App\Entity\ExpenseCategory;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public array $categories = array(
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

    public array $expenses = array(
        array(
            'description' => 'Food supplies',
            'amount'      => 70.98,
        ),
        array(
            'description' => 'Phone bills',
            'amount'      => '10.24',
        ),
    );

    public array $users = array(
        array(
            'email' => 'jmucak22@gmail.com',
            'pass'  => 'admin',
        ),
        array(
            'email' => 'test@test.com',
            'pass'  => 'user',
        ),
    );

    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher
    ) {

    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->categories as $expense_category) {
            $category = new ExpenseCategory();
            $category->setName($expense_category['name']);
            $manager->persist($category);
        }

        foreach ($this->users as $user) {
            $user_object = new User();
            $user_object->setEmail($user['email']);
            $user_object->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user_object,
                    $user['pass'],
                ),
            );
            $manager->persist($user_object);
        }

        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
