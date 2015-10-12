<?php

namespace Eshop\ShopBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * GoodRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class GoodRepository extends EntityRepository
{
    //query for pagination without "getQuery()"
    public function findByCategoryForPaginator($categoryId){
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('g')
            ->from('ShopBundle:Good', 'g')
            ->innerJoin('   g.category', 'ca')
            ->where('ca.id = :categoryid')
            ->setParameter('categoryid', $categoryId
            );
    }
}
