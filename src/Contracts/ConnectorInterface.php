<?php

declare(strict_types=1);

namespace HyderKamran\VoipNow\Contracts;

interface ConnectorInterface
{
    /**
     * Connect and initialise the adapter using the given config.
     * Returns itself so methods can be chained.
     */
    public function connect(array $config): static;

    /**
     * Issue a GET request to the VoipNow API.
     *
     * @param  string  $endpoint      Relative path, e.g. 'organizations'
     * @param  array   $queryParams   URL query-string parameters
     */
    public function get(string $endpoint, array $queryParams = []): array;

    /**
     * Issue a POST request (create a resource).
     *
     * @param  string  $endpoint
     * @param  array   $data          JSON body payload
     */
    public function post(string $endpoint, array $data = []): array;

    /**
     * Issue a PUT request (replace / update a resource).
     */
    public function put(string $endpoint, array $data = []): array;

    /**
     * Issue a PATCH request (partial update of a resource).
     */
    public function patch(string $endpoint, array $data = []): array;

    /**
     * Issue a DELETE request.
     */
    public function delete(string $endpoint, array $data = []): array;
}
