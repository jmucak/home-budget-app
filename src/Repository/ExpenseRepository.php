<?php

namespace App\Repository;

use App\Entity\Expense;
use App\Entity\User;
use DateTime;
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

        if ( ! empty($params['date'])) {
            switch ($params['date']) {
                case 'month':
                    $date = [
                        'start_date' => new DateTime('last month'),
                        'end_date'   => new DateTime('now'),
                    ];
                    break;
                case 'quarter':
                    $date = [
                        'start_date' => new DateTime('-3 month'),
                        'end_date'   => new DateTime('now'),
                    ];
                    break;
                case 'year':
                    $date = [
                        'start_date' => new DateTime('-1 year'),
                        'end_date'   => new DateTime('now'),
                    ];
                    break;
            }

            if ( ! empty($date)) {
                $query->andWhere('expenses.created >= (:start_date)')
                      ->andWhere('expenses.created <= (:end_date)')
                      ->setParameter('start_date', $date['start_date'])
                      ->setParameter('end_date', $date['end_date']);
            }
        }

        $orderBy = [
            'sort'  => 'expenses.created',
            'order' => 'DESC',
        ];
        if ( ! empty($params['order_by']) && 'date_desc' !== $params['order_by']) {
            $orderBy = match ($params['order_by']) {
                'date_asc' => [
                    'sort'  => 'expenses.created',
                    'order' => 'ASC',
                ],
                'price_asc' => [
                    'sort'  => 'expenses.amount',
                    'order' => 'ASC',
                ],
                'price_desc' => [
                    'sort'  => 'expenses.amount',
                    'order' => 'DESC',
                ]
            };
        }

        $query->orderBy($orderBy['sort'], $orderBy['order']);

        return $query->setMaxResults($params['limit'] ?? 100)->getQuery()->getResult();
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
