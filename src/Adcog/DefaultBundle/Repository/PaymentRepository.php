<?php

namespace Adcog\DefaultBundle\Repository;

use Adcog\DefaultBundle\Entity\Payment;
use EB\DoctrineBundle\Paginator\PaginatorHelper;
use EB\TranslationBundle\Translation\TranslationService;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class PaymentRepository
 *
 * @author "Emmanuel BALLERY" <emmanuel.ballery@gmail.com>
 */
class PaymentRepository extends EntityRepository
{
    /**
     * Create a paginator
     *
     * @param PaginatorHelper $paginatorHelper Paginator helper
     * @param array           $filters         Filters
     *
     * @return Payment[]|Paginator
     */
    public function getPaginator(PaginatorHelper $paginatorHelper, array $filters = [])
    {
        $qb = $this->createQueryBuilder('a');

        // Join user
        $qb
            ->leftJoin('a.user', 'b')
            ->addSelect('b');
        
        // Type
        if (array_key_exists('type', $filters) && null !== $type = $filters['type']) {
            $qb->andWhere(sprintf('a INSTANCE OF %s', get_class(Payment::createObject($type))));
        }
        
        // Recherche des users
        $paginatorHelper
            ->applyEqFilter($qb, 'user', $filters)
            ->applyValidatedFilter($qb, $filters);

        return $paginatorHelper->create($qb, ['created' => 'DESC']);
    }
    
    /**
    * Export Data (not paginated)
    *
    * @param array           $filters         Filters
    *
    * @return
    */
    public function exportData(array $filters = []) 
    {
        // Recherche les éléments
        $paginatorHelper = new PaginatorHelper();
        $data = $this->getPaginator($paginatorHelper, $filters);
        
        // Retourne les données
        return $data;
    }

    /**
     * @return int
     */
    public function countUnvalidatedItems()
    {
        $qb = $this->createQueryBuilder('a');

        return $qb
            ->select($qb->expr()->countDistinct('a.id'))
            ->andWhere($qb->expr()->isNull('a.validated'))
            ->andWhere($qb->expr()->isNull('a.invalidated'))
            ->getQuery()
            ->getSingleScalarResult();
    }
}
