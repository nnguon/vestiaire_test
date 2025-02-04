<?php

namespace VestiaireCollective\Repository;

use Predis\Client;
use VestiaireCollective\Entity\Refund;
use VestiaireCollective\Repository\RefundRepositoryInterface;
use VestiaireCollective\Exception\EntityNotFoundException;
use VestiaireCollective\Entity\Builder\RefundBuilder;

class RefundRepository implements RefundRepositoryInterface
{
    private Client $client; 
    
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    
    public function findByCaptureId(string $captureId): Refund
    {
        #Since there is no where clause, parse every data manually
        $keys = $this->client->keys('*');

        foreach ($keys as $key) {
            $data = $this->client->get($key); 
            $productData = json_decode($data, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($productData['capture_id']) && $productData['capture_id'] === $captureId) {
                return RefundBuilder::builder()
                    ->withId($productData["id"])
                    ->withPublicId($productData["public_id"])
                    ->withProvider($productData["provider"])
                    ->withResult($productData["result"])
                    ->withCaptureId($productData["capture_id"])
                    
                    ->build();
            } 
        }

        throw new EntityNotFoundException("Capture not found");
    }


    public function save(Refund $entity): bool 
    {
        $publicId = 'ref_' . uniqid();
        $id = rand(1, 1000000);

        $productData = [
            'public_id' => $publicId,
            'id'=> $id,
            'result' => $entity->getResult(),
            'provider' => $entity->getProvider(),
            'capture_id' => $entity->getCaptureId()
        ];

        $data = json_encode($productData);

        if ($data === false) {
            return false;
        }
        try {
            $result = $this->client->set($publicId, $data);
        } catch (\Exception $e) {
            return false;
        }

        if (isset($result) && $result->getPayload() === 'OK') {
            $entity->setId($id);
            $entity->setPublicId( $publicId);

            return true;
        }

        return false;
    }

}