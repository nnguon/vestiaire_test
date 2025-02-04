<?php

namespace VestiaireCollective\Repository;

Use VestiaireCollective\Entity\Authorization;
interface AuthorizationRepositoryInterface 
{
    public function save(Authorization $entity): bool;

    public function findByPublicId(string $publicId): Authorization;

}
