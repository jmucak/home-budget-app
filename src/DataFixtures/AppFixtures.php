<?php

namespace App\DataFixtures;

use App\Entity\ExpenseCategory;
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

        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
