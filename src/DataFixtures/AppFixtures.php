<?php

namespace App\DataFixtures;

use App\Entity\Expense;
use App\Entity\ExpenseCategory;
use App\Entity\User;
use DateTime;
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

    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher
    ) {

    }

    public function load(ObjectManager $manager): void
    {
        $user1 = new User();
        $user1->setEmail('email@email.com');
        $user1->setPassword($this->userPasswordHasher->hashPassword($user1, 'user1'));
        $manager->persist($user1);

        $user2 = new User();
        $user2->setEmail('email2@email.com');
        $user2->setPassword($this->userPasswordHasher->hashPassword($user1, 'user2'));
        $manager->persist($user2);

        // Set default categories for each user
        foreach ($this->categories as $key => $expense_category) {
            $category = new ExpenseCategory();
            $category->setName($expense_category['name']);
            $category->setUser($user1);
            $manager->persist($category);

            if (0 === $key) {
                $expense = new Expense();
                $expense->setDescription('Food supplies');
                $expense->setAmount(24.53);
                $expense->setCreated(new DateTime());
                $expense->setUser($user1);
                $expense->setCategory($category);
                $manager->persist($expense);
            }

            if (2 === $key) {
                $expense = new Expense();
                $expense->setDescription('Rent');
                $expense->setAmount(489.96);
                $expense->setCreated(new DateTime());
                $expense->setUser($user1);
                $expense->setCategory($category);
                $manager->persist($expense);
            }
        }

        foreach ($this->categories as $key => $expense_category) {
            $category = new ExpenseCategory();
            $category->setName($expense_category['name']);
            $category->setUser($user2);
            $manager->persist($category);

            if (0 === $key) {
                $expense = new Expense();
                $expense->setDescription('Supermarket');
                $expense->setAmount(33.74);
                $expense->setCreated(new DateTime());
                $expense->setUser($user2);
                $expense->setCategory($category);
                $manager->persist($expense);
            }

            if (1 === $key) {
                $expense = new Expense();
                $expense->setDescription('Phone bills');
                $expense->setAmount(10.43);
                $expense->setCreated(new DateTime());
                $expense->setUser($user2);
                $expense->setCategory($category);
                $manager->persist($expense);
            }
        }

        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
