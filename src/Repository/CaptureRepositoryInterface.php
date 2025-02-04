<?php

namespace VestiaireCollective\Repository;

use VestiaireCollective\Entity\Capture;
interface CaptureRepositoryInterface 
{
    public function save(Capture $entity): bool;

    public function findByPublicId(string $publicId): Capture;

    public function findByAuthorizationId(string $authorizationId): Capture;
}
