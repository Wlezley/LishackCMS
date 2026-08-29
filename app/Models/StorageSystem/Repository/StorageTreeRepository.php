<?php

declare(strict_types=1);

namespace App\Models\StorageSystem\Repository;

use App\Entity\StorageTree\StorageTree as StorageTreeEntity;
use App\Entity\StorageTree\StorageTreeRepository as DoctrineRepository;
use App\Exception\StorageSystemException;
use App\Models\StorageSystem\Entity\StorageTree;

class StorageTreeRepository
{
    public const TABLE_NAME = 'storage_tree';

    public function __construct(
        private DoctrineRepository $doctrineRepository,
    ) {
    }

    public function findById(int $id): ?StorageTree
    {
        $entity = $this->doctrineRepository->findById($id);
        if (!$entity) {
            return null;
        }

        return $this->entityToModel($entity);
    }

    /** @return StorageTree[] */
    public function getFoldersInFolder(int $parentId = 0): array
    {
        $entities = $this->doctrineRepository->findBy(['parentId' => $parentId], ['position' => 'ASC']);

        $result = [];
        foreach ($entities as $entity) {
            $result[] = $this->entityToModel($entity);
        }

        return $result;
    }

    public function insert(StorageTree $tree): StorageTree
    {
        $tree->setId(null);
        $tree->validate();

        $entity = new StorageTreeEntity(
            (int)$tree->parentId,
            (int)$tree->ownerId,
            $tree->position,
            (string)$tree->name,
            (string)$tree->nameUrl,
            $tree->modifiedAt instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($tree->modifiedAt) : null,
            $tree->deletedAt instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($tree->deletedAt) : null
        );

        $this->doctrineRepository->save($entity);
        $tree->id = $entity->getId();
        return $tree;
    }

    public function update(StorageTree $tree): void
    {
        $tree->validate();

        if ($tree->id === null) {
            throw new StorageSystemException('Cannot update tree without ID.');
        }

        $entity = $this->doctrineRepository->findById($tree->id);
        if (!$entity) {
            throw new StorageSystemException("Tree ID '$tree->id' not found.");
        }

        $entity->setParentId((int)$tree->parentId);
        $entity->setOwnerId((int)$tree->ownerId);
        $entity->setPosition($tree->position);
        $entity->setName((string)$tree->name);
        $entity->setNameUrl((string)$tree->nameUrl);
        $entity->setModifiedAt($tree->modifiedAt ? \DateTimeImmutable::createFromInterface($tree->modifiedAt) : null);
        $entity->setDeletedAt($tree->deletedAt ? \DateTimeImmutable::createFromInterface($tree->deletedAt) : null);

        $this->doctrineRepository->save($entity);
    }

    public function delete(int $id): int
    {
        $entity = $this->doctrineRepository->findById($id);
        if ($entity) {
            $this->doctrineRepository->delete($entity);
            return 1;
        }
        return 0;
    }

    private function entityToModel(StorageTreeEntity $entity): StorageTree
    {
        $tree = new StorageTree();
        $tree->id = $entity->getId();
        $tree->parentId = $entity->getParentId();
        $tree->ownerId = $entity->getOwnerId();
        $tree->position = $entity->getPosition();
        $tree->name = $entity->getName();
        $tree->nameUrl = $entity->getNameUrl();
        $tree->createdAt = \Nette\Utils\DateTime::createFromInterface($entity->getCreatedAt());
        $tree->modifiedAt = $entity->getModifiedAt() ? \Nette\Utils\DateTime::createFromInterface($entity->getModifiedAt()) : null;
        $tree->deletedAt = $entity->getDeletedAt() ? \Nette\Utils\DateTime::createFromInterface($entity->getDeletedAt()) : null;
        return $tree;
    }

    public function setDeleted(int $id, bool $isDeleted): int
    {
        $entity = $this->doctrineRepository->findById($id);
        if ($entity) {
            $entity->setDeletedAt($isDeleted ? new \DateTimeImmutable() : null);
            $this->doctrineRepository->save($entity);
            return 1;
        }
        return 0;
    }

    public function moveToFolder(int $id, int $parentId): int
    {
        $entity = $this->doctrineRepository->findById($id);
        if ($entity) {
            $entity->setParentId($parentId);
            $this->doctrineRepository->save($entity);
            return 1;
        }
        return 0;
    }
}
