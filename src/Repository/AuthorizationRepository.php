<?php

namespace VestiaireCollective\Repository;

use Predis\Client;
use VestiaireCollective\Entity\Authorization;
use VestiaireCollective\Entity\Builder\AuthorizationBuilder;
use VestiaireCollective\Repository\AuthorizationRepositoryInterface;
use VestiaireCollective\Exception\EntityNotFoundException;

class AuthorizationRepository implements AuthorizationRepositoryInterface
{
    private Client $client; 
    
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    
    public function findByPublicId(string $publicId): Authorization
    {
        $data = $this->client->get($publicId);

        if ($data) {
            $productData = json_decode($data, true);

            if (json_last_error() === JSON_ERROR_NONE) 
            {
                return AuthorizationBuilder::builder()
                    ->withAmount($productData["amount"])
                    ->withCardNumber($productData["card_number"])
                    ->withExpiryDate($productData["expiry_date"])
                    ->withId($productData["id"])
                    ->withPublicId($productData["public_id"])
                    ->withCvv($productData["cvv"])
                    ->withProvider($productData["provider"])
                    ->withResult($productData["result"])
                    ->build();
            } 
            else {
                #error_log("JSON decoding error: " . json_last_error_msg());
                throw new \Exception("Error while searching authorization");
            }
        }

        throw new EntityNotFoundException("Authorization not found");
    }
    
    
    public function save(Authorization $entity): bool 
    {
        $publicId = 'trx_' . uniqid();
        $id = rand(1, 1000000);;

        $productData = [
            'public_id' => $publicId,
            'id'=> $id,
            'amount' => $entity->getAmount(),
            'card_number' => $entity->getCreditCardNumber(),
            'expiry_date' => $entity->getExpiryDate(),
            'cvv' => $entity->getCvv(),
            'result' => $entity->getResult(),
            'provider' => $entity->getProvider()
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