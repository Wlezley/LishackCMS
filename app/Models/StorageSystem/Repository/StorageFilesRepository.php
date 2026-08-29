<?php

declare(strict_types=1);

namespace App\Models\StorageSystem\Repository;

use App\Entity\StorageFiles\StorageFiles as StorageFileEntity;
use App\Entity\StorageFiles\StorageFilesRepository as DoctrineRepository;
use App\Exception\StorageSystemException;
use App\Models\StorageSystem\Entity\StorageFile;

class StorageFilesRepository
{
    public const TABLE_NAME = 'storage_files';

    public function __construct(
        private DoctrineRepository $doctrineRepository,
    ) {
    }

    public function findById(int $id): ?StorageFile
    {
        $entity = $this->doctrineRepository->findById($id);
        if (!$entity) {
            return null;
        }

        return $this->entityToModel($entity);
    }

    /** @return StorageFile[] */
    public function getFilesInFolder(int $treeId = 0): array
    {
        $entities = $this->doctrineRepository->findBy(['treeId' => $treeId], ['position' => 'ASC']);

        $result = [];
        foreach ($entities as $entity) {
            $result[] = $this->entityToModel($entity);
        }

        return $result;
    }

    public function insert(StorageFile $file): StorageFile
    {
        $file->setId(null);
        $file->validate();

        $entity = new StorageFileEntity(
            (int)$file->treeId,
            (int)$file->ownerId,
            $file->position,
            (string)$file->name,
            (string)$file->nameUrl,
            (string)$file->contentType,
            (string)$file->icon,
            (int)$file->size,
            (string)$file->checksum,
            (string)$file->storageId,
            (string)$file->downloadId,
            $file->uploadedAt instanceof \DateTimeInterface
                ? \DateTimeImmutable::createFromInterface($file->uploadedAt)
                : new \DateTimeImmutable(),
            $file->modifiedAt instanceof \DateTimeInterface
                ? \DateTimeImmutable::createFromInterface($file->modifiedAt)
                : null,
            $file->deletedAt instanceof \DateTimeInterface
                ? \DateTimeImmutable::createFromInterface($file->deletedAt)
                : null
        );

        $this->doctrineRepository->save($entity);
        $file->id = $entity->getId();
        return $file;
    }

    public function update(StorageFile $file): void
    {
        $file->validate();

        if ($file->id === null) {
            throw new StorageSystemException('Cannot update file without ID.');
        }

        $entity = $this->doctrineRepository->findById($file->id);
        if (!$entity) {
            throw new StorageSystemException("File ID '$file->id' not found.");
        }

        $entity->setTreeId((int)$file->treeId);
        $entity->setOwnerId((int)$file->ownerId);
        $entity->setPosition($file->position);
        $entity->setName((string)$file->name);
        $entity->setNameUrl((string)$file->nameUrl);
        $entity->setType((string)$file->contentType);
        $entity->setIcon((string)$file->icon);
        $entity->setSize((int)$file->size);
        $entity->setChecksum((string)$file->checksum);
        $entity->setStorageId((string)$file->storageId);
        $entity->setDownloadId((string)$file->downloadId);
        if ($file->uploadedAt) {
            $entity->setUploadedAt(\DateTimeImmutable::createFromInterface($file->uploadedAt));
        }
        $entity->setModifiedAt($file->modifiedAt ? \DateTimeImmutable::createFromInterface($file->modifiedAt) : null);
        $entity->setDeletedAt($file->deletedAt ? \DateTimeImmutable::createFromInterface($file->deletedAt) : null);

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

    private function entityToModel(StorageFileEntity $entity): StorageFile
    {
        $file = new StorageFile();
        $file->id = $entity->getId();
        $file->treeId = $entity->getTreeId();
        $file->ownerId = $entity->getOwnerId();
        $file->position = $entity->getPosition();
        $file->name = $entity->getName();
        $file->nameUrl = $entity->getNameUrl();
        $file->contentType = $entity->getType();
        $file->icon = $entity->getIcon();
        $file->size = $entity->getSize();
        $file->checksum = $entity->getChecksum();
        $file->storageId = $entity->getStorageId();
        $file->downloadId = $entity->getDownloadId();
        $file->uploadedAt = \Nette\Utils\DateTime::createFromInterface($entity->getUploadedAt());
        $file->modifiedAt = $entity->getModifiedAt() ? \Nette\Utils\DateTime::createFromInterface($entity->getModifiedAt()) : null;
        $file->deletedAt = $entity->getDeletedAt() ? \Nette\Utils\DateTime::createFromInterface($entity->getDeletedAt()) : null;
        return $file;
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

    public function moveToFolder(int $id, int $treeId): int
    {
        $entity = $this->doctrineRepository->findById($id);
        if ($entity) {
            $entity->setTreeId($treeId);
            $this->doctrineRepository->save($entity);
            return 1;
        }
        return 0;
    }
}
