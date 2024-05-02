<?php

namespace Softonic\GraphQL;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use Softonic\GraphQL\DataObjects\Mutation\MutationObject;

class Client
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    private array $customHeaders = [];

    /**
     * @var ResponseBuilder
     */
    private $responseBuilder;

    public function __construct(ClientInterface $httpClient, ResponseBuilder $responseBuilder)
    {
        $this->httpClient      = $httpClient;
        $this->responseBuilder = $responseBuilder;
    }

    /**
     * @throws \UnexpectedValueException When response body is not a valid json
     * @throws \RuntimeException         When there are transfer errors
     */
    public function query(string $query, array $variables = null): Response
    {
        return $this->executeQuery($query, $variables);
    }

    /**
     * @throws \UnexpectedValueException When response body is not a valid json
     * @throws \RuntimeException         When there are transfer errors
     */
    public function mutate(string $query, MutationObject $mutation): Response
    {
        return $this->executeQuery($query, $mutation);
    }

    public function addHeaders(array $headers): void
    {
        $this->customHeaders = array_merge($this->customHeaders, $headers);
    }

    private function executeQuery(string $query, $variables): Response
    {
        $body = ['query' => $query];
        if (!is_null($variables)) {
            $body['variables'] = $variables;
        }

        $headers = [
                'Content-Type' => 'application/json',
        ];

        if ($this->customHeaders) {
            $headers = array_merge($this->customHeaders, $headers);
        }

        $options = [
            'body'    => json_encode($body, JSON_UNESCAPED_SLASHES),
            'headers' => $headers,
        ];

        try {
            $response = $this->httpClient->request('POST', '', $options);
        } catch (TransferException $e) {
            throw new \RuntimeException('Network Error.' . $e->getMessage(), 0, $e);
        }

        return $this->responseBuilder->build($response);
    }
}
