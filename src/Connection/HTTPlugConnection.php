<?php

declare(strict_types=1);

/*
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core23\SetlistFm\Connection;

use Core23\SetlistFm\Exception\ApiException;
use Core23\SetlistFm\Exception\NotFoundException;
use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Psr\Http\Message\ResponseInterface;

final class HTTPlugConnection extends AbstractConnection
{
    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * Initialize client.
     *
     * @param HttpClient     $client
     * @param MessageFactory $messageFactory
     * @param string         $apiKey
     * @param string         $uri
     */
    public function __construct(HttpClient $client, MessageFactory $messageFactory, string $apiKey, string $uri = null)
    {
        parent::__construct($apiKey, $uri);

        $this->client         = $client;
        $this->messageFactory = $messageFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function call(string $method, array $params = [], string $requestMethod = 'GET'): array
    {
        $data = $this->buildParameter($params);

        $headers = [
            'Accept'    => 'application/json',
            'x-api-key' => $this->getApiKey(),
        ];

        if ('POST' === $requestMethod) {
            $request = $this->messageFactory->createRequest($requestMethod, $this->getUri().$method, $headers, $data);
        } else {
            $request = $this->messageFactory->createRequest($requestMethod, $this->getUri().$method.'?'.$data, $headers);
        }

        try {
            $response = $this->client->sendRequest($request);

            // Parse response
            return $this->parseResponse($response);
        } catch (ApiException $e) {
            throw $e;
        } catch (NotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ApiException('Technical error occurred.', 500, $e);
        }
    }

    /**
     * @param ResponseInterface $response
     *
     * @throws ApiException
     * @throws NotFoundException
     *
     * @return array
     */
    private function parseResponse(ResponseInterface $response): array
    {
        $content = $response->getBody()->getContents();
        $array   = json_decode($content, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new ApiException('Server did not reply with a valid response.', $response->getStatusCode());
        }

        if (404 === $response->getStatusCode()) {
            throw new NotFoundException('Server did not find any entity for the request.');
        }

        if ($response->getStatusCode() >= 400) {
            throw new ApiException('Technical error occurred.', $response->getStatusCode());
        }

        return $array;
    }

    /**
     * Builds request parameter.
     *
     * @param array $parameter
     *
     * @return string
     */
    private function buildParameter(array $parameter): string
    {
        return http_build_query($parameter);
    }
}
