<?php

namespace VestiaireCollective\Repository;

use Predis\Client;
use VestiaireCollective\Entity\Capture;
use VestiaireCollective\Entity\Builder\CaptureBuilder;
use VestiaireCollective\Repository\CaptureRepositoryInterface;
use VestiaireCollective\Exception\EntityNotFoundException;

class CaptureRepository implements CaptureRepositoryInterface
{
    private Client $client; 
    
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    
    public function findByAuthorizationId(string $authorizationId): Capture
    {
        #Since there is no where clause, parse every data manually
        $keys = $this->client->keys('*');

        foreach ($keys as $key) {
            $data = $this->client->get($key); 
            $productData = json_decode($data, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($productData['authorization_id']) && $productData['authorization_id'] === $authorizationId) {
                return CaptureBuilder::builder()
                    ->withId($productData["id"])
                    ->withPublicId($productData["public_id"])
                    ->withProvider($productData["provider"])
                    ->withResult($productData["result"])
                    ->withAuthorizationId($productData["authorization_id"])
                    ->build();
            } 
        }

        throw new EntityNotFoundException("Capture not found");
    }

    public function findByPublicId(string $publicId): Capture
    {
        $data = $this->client->get($publicId);

        if ($data) {
            $productData = json_decode($data, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return CaptureBuilder::builder()
                    ->withId($productData["id"])
                    ->withPublicId($productData["public_id"])
                    ->withProvider($productData["provider"])
                    ->withResult($productData["result"])
                    ->withAuthorizationId($productData["authorization_id"])
                    ->build();
            } 
            else {
                #error_log("JSON decoding error: " . json_last_error_msg());
                throw new \Exception("Error while searching capture");
            }
        }

        throw new EntityNotFoundException("Capture not found");
    }
    
    
    public function save(Capture $entity): bool 
    {
        $publicId = 'cap_' . uniqid();
        $id = rand(1, 1000000);

        $productData = [
            'public_id' => $publicId,
            'id'=> $id,
            'result' => $entity->getResult(),
            'provider' => $entity->getProvider(),
            'authorization_id' => $entity->getAuthorizationId(),
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