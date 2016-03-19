<?php

namespace Eshop\ShopBundle\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Eshop\ShopBundle\Entity\Category;
use Eshop\ShopBundle\Entity\Manufacturer;
use Eshop\UserBundle\Entity\User;

/**
 * ProductRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductRepository extends EntityRepository
{
    /**
     * @param string $slug
     * @return mixed
     */
    public function findBySlug($slug)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('p')
            ->from('ShopBundle:Product', 'p')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Category $category
     * @param User $user
     * @return QueryBuilder
     */
    public function findByCategoryQB($category, $user = null)
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select(array('p', 'pi', 'pm', 'pfa', 'pfe'))
            ->from('ShopBundle:Product', 'p')
            ->innerJoin('p.category', 'ca')
            ->leftJoin('p.images', 'pi')
            ->leftJoin('p.measure', 'pm')
            ->leftJoin('p.favourites', 'pfa', 'WITH', 'pfa.user = :user') //if liked
            ->leftJoin('p.featured', 'pfe')
            ->where('ca = :category')
            ->andWhere('p.quantity <> 0')
            ->setParameter('category', $category)
            ->setParameter('user', $user);

        return $qb;
    }

    /**
     * @param Manufacturer $manufacturer
     * @param User $user
     * @return QueryBuilder
     */
    public function findByManufacturerQB($manufacturer, $user = null)
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select(array('p', 'pi', 'pm', 'pfa', 'pfe'))
            ->from('ShopBundle:Product', 'p')
            ->innerJoin('p.manufacturer', 'ma')
            ->leftJoin('p.images', 'pi')
            ->leftJoin('p.measure', 'pm')
            ->leftJoin('p.favourites', 'pfa', 'WITH', 'pfa.user = :user') //if liked
            ->leftJoin('p.featured', 'pfe')
            ->where('ma.id = :manufacturer')
            ->andWhere('p.quantity <> 0')
            ->setParameter('manufacturer', $manufacturer)
            ->setParameter('user', $user);

        return $qb;
    }

    /**
     * @param User $user
     * @return QueryBuilder
     */
    public function getFavouritesQB($user = null)
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select(array('p', 'pi', 'pm', 'pfa', 'pfe'))
            ->from('ShopBundle:Product', 'p')
            ->leftJoin('p.images', 'pi')
            ->leftJoin('p.measure', 'pm')
            ->innerJoin('p.favourites', 'pfa', 'WITH', 'pfa.user = :user') //only liked
            ->leftJoin('p.featured', 'pfe')
            ->andWhere('p.quantity <> 0')
            ->setParameter('user', $user);

        return $qb;
    }

    /**
     * @param array $searchWords
     * @param User $user
     * @return QueryBuilder
     */
    public function getSearchQB($searchWords, $user = null)
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select(array('p', 'pi', 'pm', 'pfa', 'pfe'))
            ->from('ShopBundle:Product', 'p')
            ->leftJoin('p.images', 'pi')
            ->leftJoin('p.measure', 'pm')
            ->leftJoin('p.favourites', 'pfa', 'WITH', 'pfa.user = :user') //if liked
            ->leftJoin('p.featured', 'pfe')
            ->where('p.quantity <> 0')
            ->setParameter('user', $user);

        $cqbORX = array();
        foreach ($searchWords as $searchWord) {
            $cqbORX[] = $qb->expr()->like('p.name', $qb->expr()->literal('%' . $searchWord . '%'));
            $cqbORX[] = $qb->expr()->like('p.description', $qb->expr()->literal('%' . $searchWord . '%'));
        }

        $qb->andWhere(call_user_func_array(array($qb->expr(), "orx"), $cqbORX));

        return $qb;
    }

    /**
     * @param int $quantity
     * @param User $user
     * @return array
     */
    public function getLatest($quantity = 1, $user = null)
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select(array('p', 'pi', 'pm', 'pfa', 'pfe'))
            ->from('ShopBundle:Product', 'p')
            ->leftJoin('p.images', 'pi')
            ->leftJoin('p.measure', 'pm')
            ->leftJoin('p.favourites', 'pfa', 'WITH', 'pfa.user = :user') //if liked
            ->leftJoin('p.featured', 'pfe')
            ->where('p.quantity <> 0')
            ->setMaxResults($quantity)
            ->setParameter('user', $user)
            ->addOrderBy('p.dateCreated', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * return products for admin
     *
     * @return QueryBuilder
     */
    public function getAllProductsAdminQB()
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select(array('p', 'pi', 'pm', 'pc'))
            ->from('ShopBundle:Product', 'p')
            ->leftJoin('p.images', 'pi')
            ->leftJoin('p.manufacturer', 'pm')
            ->leftJoin('p.category', 'pc');

        return $qb;
    }
}
