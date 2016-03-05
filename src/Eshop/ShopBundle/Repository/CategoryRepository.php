<?php

namespace Eshop\ShopBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CategoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CategoryRepository extends EntityRepository
{
    /**
     * @param bool $showEmpty
     * @param string $order
     * @param string $sort
     * @return array
     */
    public function getAllCategories($showEmpty = true, $sort = 'name', $order = 'ASC')
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c')
            ->from('ShopBundle:Category', 'c');

        if (!$showEmpty) {
            $qb->innerJoin('c.products', 'p');
        }

        $qb->orderBy('c.'.$sort, $order);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $slug string
     * @return mixed
     */
    public function findBySlug($slug)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c')
            ->from('ShopBundle:Category', 'c')
            ->where('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @return mixed
     */
    public function getFirstCategory()
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ca')
            ->from('ShopBundle:Category', 'ca')
            ->orderBy('ca.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }
}
