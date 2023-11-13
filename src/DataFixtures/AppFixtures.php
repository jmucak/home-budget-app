<?php

namespace App\DataFixtures;

use App\Entity\ExpenseCategory;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $expense_categories = array(
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

        foreach ($expense_categories as $expense_category) {
            $category = new ExpenseCategory();
            $category->setName($expense_category['name']);
            $manager->persist($category);
        }

        $user = new User();
        $user->setEmail('jmucak22@gmail.com');
        $user->setPassword('1234');
        $manager->persist($user);

        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
