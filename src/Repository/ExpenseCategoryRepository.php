<?php

namespace App\Repository;

use App\Entity\ExpenseCategory;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<ExpenseCategory>
 *
 * @method ExpenseCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExpenseCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExpenseCategory[]    findAll()
 * @method ExpenseCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExpenseCategory::class);
    }

    /**
     * @param int|User $user
     *
     * @return ExpenseCategory[]
     */
    public function findByUser(int|UserInterface $user): array
    {
        $user = $user instanceof User ? $user->getId() : $user;

        return $this->createQueryBuilder('category')
                    ->where('category.user = :user')
                    ->setParameter('user', $user)
                    ->getQuery()
                    ->getResult();
    }

    public function findOneByUser(int|UserInterface $user, int $category_id): ?ExpenseCategory
    {
        $user = $user instanceof User ? $user->getId() : $user;

        $query = $this->createQueryBuilder('category')
                      ->where('category.user = :user')
                      ->andWhere('category.id = :category')
                      ->setParameter('category', $category_id)
                      ->setParameter('user', $user)
                      ->setMaxResults(1)
                      ->getQuery()
                      ->getResult();

        return ! empty($query[0]) ? $query[0] : null;
    }
}
