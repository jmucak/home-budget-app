<?php

namespace App\Repository;

use App\Entity\Expense;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Expense>
 *
 * @method Expense|null find($id, $lockMode = null, $lockVersion = null)
 * @method Expense|null findOneBy(array $criteria, array $orderBy = null)
 * @method Expense[]    findAll()
 * @method Expense[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    /**
     * @param int|UserInterface $user
     *
     * @return Expense[]
     */
    public function findAllByUser(int|UserInterface $user, array $params = []): array
    {
        $user = $user instanceof User ? $user->getId() : $user;

        $query = $this->createQueryBuilder('expenses')
                      ->leftJoin('expenses.user', 'user')
                      ->addSelect('user')
                      ->where('expenses.user = (:user)')
                      ->setParameter('user', $user);

        if ( ! empty($params['category'])) {
            $query->andWhere('expenses.category = (:category)')
                  ->setParameter('category', $params['category']);
        }

        if ( ! empty($params['price'])) {
            $orderBy = [
                'sort' => 'expenses.amount',
            ];
            switch ($params['price']) {
                case 'min-max':
                    $orderBy['order'] = 'ASC';
                    break;
                case 'max-min':
                    $orderBy['order'] = 'DESC';
                    break;
            }
        }

        if (empty($orderBy['order'])) {
            $orderBy = [
                'sort'  => 'expenses.created',
                'order' => $params['date'],
            ];
        }

        $query->orderBy($orderBy['sort'], $orderBy['order']);

        return $query->setMaxResults($params['limit'])->getQuery()->getResult();
    }

    public function findByUser(int|UserInterface $user, int $expense_id): ?Expense
    {
        $user = $user instanceof User ? $user->getId() : $user;

        $query = $this->createQueryBuilder('expense')
                      ->innerJoin('expense.user', 'user')
                      ->addSelect('user')
                      ->where('expense.user = (:user)')
                      ->andWhere('expense.id = (:id)')
                      ->setParameter('user', $user)
                      ->setParameter('id', $expense_id)
                      ->setMaxResults(1)
                      ->getQuery()
                      ->getResult();

        return ! empty($query[0]) ? $query[0] : null;
    }
}
