<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Category;

/**
 * CategoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CategoryRepository extends ServiceEntityRepository
{
    /**
     * Repository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @param bool $showEmpty
     * @param string $order
     * @param string $sort
     * @return Category[]|array
     */
    public function getAllCategories(bool $showEmpty = true, string $sort = 'name', string $order = 'ASC'): array
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c')
            ->from(Category::class, 'c');

        if (!$showEmpty) {
            $qb->innerJoin('c.products', 'p')
                ->andWhere('p.quantity <> 0');
        }

        $qb->orderBy('c.' . $sort, $order);

        return $qb->getQuery()->getResult();
    }

    /**
     * query for admin paginator
     *
     * @return QueryBuilder
     */
    public function getAllCategoriesAdminQB(): QueryBuilder
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c')
            ->from(Category::class, 'c');
    }

    /**
     * @param $slug string
     * @return Category|null
     * @throws NonUniqueResultException
     */
    public function findBySlug(string $slug): ?Category
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c')
            ->from(Category::class, 'c')
            ->where('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Category|null
     * @throws NonUniqueResultException
     */
    public function getFirstCategory(): ?Category
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ca')
            ->from(Category::class, 'ca')
            ->orderBy('ca.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array
     */
    public function getArrayForSitemap(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.slug, c.dateUpdated')
            ->getQuery()
            ->getArrayResult();
    }
}
