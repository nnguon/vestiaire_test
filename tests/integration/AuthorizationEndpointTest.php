<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class AuthorizationEndpointTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new Client([
            'base_uri' => 'http://localhost:8080'
        ]);
    }

    public function testAuthorizeSuccess()
    {
        $requestData = [
            'amount' => 155,
            'card_number' => '5111111111111111',
            'expiry_date' => '11/26',
            'cvv' => '414',
        ];

        $response = $this->client->post('/authorize', [
            'headers' => ['Content-type' => 'application/json','Accept' => 'application/json'], 
            'body' => json_encode($requestData, true),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        //$responseData = json_decode($response->getBody(), true);
        // $this->assertArrayHasKey('auth_token', $responseData);
        // $this->assertNotEmpty($responseData['auth_token']);
        // $this->assertArrayHasKey('status', $responseData);
        // $this->assertEquals('success', $responseData['status']);
}
}