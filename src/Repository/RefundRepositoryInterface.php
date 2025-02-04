<?php

namespace VestiaireCollective\Repository;

use VestiaireCollective\Entity\Refund;

interface RefundRepositoryInterface 
{
    public function save(Refund $entity): bool;

    public function findByCaptureId(string $captureId): Refund;

}
