<?php

namespace Grachev\Model;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;

/**
 * @author Konstantin Grachev <ko@grachev.io>
 */
abstract class AbstractManager
{
    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @return string
     */
    abstract protected function getClass();

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param ObjectManager $entityManager
     *
     * @return static
     */
    public function setEntityManager(ObjectManager $em)
    {
        $this->em = $em;

        return $this;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->em;
    }

    /**
     * @param bool $andPersist
     *
     * @return object
     *
     * @throws ORMInvalidArgumentException
     */
    public function createNew($andPersist = false)
    {
        $class = $this->getClass();
        $entity = new $class();

        if (true === $andPersist) {
            $this->getEntityManager()->persist($entity);
        }

        return $entity;
    }

    /**
     * @param object $entity
     *
     * @return object|void
     *
     * @throws ORMInvalidArgumentException
     */
    public function refresh($entity)
    {
        $this->getEntityManager()->refresh($entity);
    }

    /**
     * @param object $entity
     * @param bool   $andFlush
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     */
    public function remove($entity, $andFlush = false)
    {
        $this->getEntityManager()->remove($entity);
        if (true === $andFlush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param object $entity
     * @param bool   $andFlush
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     */
    public function persist($entity, $andFlush = false)
    {
        $this->getEntityManager()->persist($entity);
        if (true === $andFlush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param object $entity
     * @param bool   $andFlush
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     */
    public function merge($entity, $andFlush = false)
    {
        $this->getEntityManager()->merge($entity);
        if (true === $andFlush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param object|null $entity
     *
     * @throws OptimisticLockException
     */
    public function flush($entity = null)
    {
        $this->getEntityManager()->flush($entity);
    }

    /**
     * @param int      $id
     * @param int      $lockMode
     * @param int|null $lockVersion
     *
     * @return object|null
     */
    public function find($id, $lockMode = LockMode::NONE, $lockVersion = null)
    {
        return $this->getRepository()->find($id, $lockMode, $lockVersion);
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return $this->getRepository()->findAll();
    }

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     *
     * @return object|null
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return $this->getRepository()->findOneBy($criteria, $orderBy);
    }
}
