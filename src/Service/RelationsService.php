<?php

declare(strict_types=1);

namespace Doomy\Repository\Service;

use Doomy\Repository\Model\Entity;
use Doomy\Repository\RepoFactory;

final readonly class RelationsService
{
    public function __construct(
        private RepoFactory $repoFactory,
    ) {
    }

    /**
     * @template T of Entity
     * @param class-string<T> $entityClass
     */
    public function get11Relation(string $entityClass, string|int $entityId, ?string $propertyName = null): ?Entity
    {
        $repository = $this->repoFactory->getRepository($entityClass);
        if (! $propertyName) {
            return $repository->findById($entityId);
        }
        return $repository->findOne([
            $propertyName => $entityId,
        ]);
    }

    /**
     * @template T of Entity
     * @param class-string<T> $entityClass
     * @param array<string, mixed> $where
     * @return Entity[]
     */
    public function get1NRelation(
        string $entityClass,
        string $propertyName,
        string|int|null $entityId = null,
        array $where = [],
        string|null $orderBy = null
    ): array {
        if ($entityId === null) {
            $entityId = $this->{$entityClass::IDENTITY_COLUMN};
        }
        $repository = $this->repoFactory->getRepository($entityClass);
        $where = array_merge($where, [
            $propertyName => $entityId,
        ]);
        return $repository->findAll($where, $orderBy);
    }
}
