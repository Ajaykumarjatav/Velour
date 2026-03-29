<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\HttpClient\ClientInterface;
use Stripe\HttpClient\StreamingClientInterface;
use Stripe\Stripe;
use Stripe\Util;
use Stripe\Util\RandomGenerator;

/**
 * Stripe SDK default client uses ext-curl. On Windows/XAMPP, Apache sometimes runs
 * PHP without curl loaded even when CLI has it. This client uses Laravel's HTTP
 * client (Guzzle + PHP streams) so billing/Stripe features work without ext-curl.
 */
class StripeLaravelHttpClient implements ClientInterface, StreamingClientInterface
{
    private const DEFAULT_TIMEOUT = 80;

    private const DEFAULT_CONNECT_TIMEOUT = 30;

    private RandomGenerator $randomGenerator;

    public function __construct()
    {
        $this->randomGenerator = new RandomGenerator();
    }

    public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null)
    {
        if ($hasFile) {
            throw new UnexpectedValueException(
                'File uploads require the PHP curl extension. Enable extension=curl in the php.ini used by your web server.'
            );
        }

        $method = strtolower((string) $method);
        $headers = $this->prepareHeaders($method, $headers, $apiMode);
        [$absUrl, $body, $contentType] = $this->buildUrlAndBody($method, $absUrl, $params, $apiMode);

        $verify = Stripe::getVerifySslCerts() ? Stripe::getCABundlePath() : false;

        $pending = Http::timeout(self::DEFAULT_TIMEOUT)
            ->connectTimeout(self::DEFAULT_CONNECT_TIMEOUT)
            ->withOptions(['verify' => $verify])
            ->withHeaders($this->headersToMap($headers));

        try {
            $response = match ($method) {
                'get' => $pending->get($absUrl),
                'delete' => $pending->delete($absUrl),
                'post' => $this->sendPost($pending, $absUrl, $body, $contentType),
                default => throw new UnexpectedValueException("Unrecognized HTTP method {$method}"),
            };
        } catch (ConnectionException $e) {
            throw new ApiConnectionException($e->getMessage());
        }

        return [
            $response->body(),
            $response->status(),
            $this->normalizeResponseHeaders($response),
        ];
    }

    public function requestStream($method, $absUrl, $headers, $params, $hasFile, $readBodyChunkCallable, $apiMode = 'v1', $maxNetworkRetries = null)
    {
        [$rbody, $rcode, $rheaders] = $this->request($method, $absUrl, $headers, $params, $hasFile, $apiMode, $maxNetworkRetries);

        if ($rcode < 300) {
            if ($rbody !== null && $rbody !== '') {
                \call_user_func($readBodyChunkCallable, $rbody);
            }

            return [null, $rcode, $rheaders];
        }

        return [$rbody, $rcode, $rheaders];
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<int, string>
     */
    private function prepareHeaders(string $method, array $headers, string $apiMode): array
    {
        $headers[] = 'Expect: ';

        if (! $this->hasHeader($headers, 'Idempotency-Key')) {
            if ($apiMode === 'v2') {
                if ($method === 'post' || $method === 'delete') {
                    $headers[] = 'Idempotency-Key: '.$this->randomGenerator->uuid();
                }
            } elseif ($method === 'post' && Stripe::$maxNetworkRetries > 0) {
                $headers[] = 'Idempotency-Key: '.$this->randomGenerator->uuid();
            }
        }

        return $headers;
    }

    /**
     * @param  array<int, string>  $headers
     */
    private function hasHeader(array $headers, string $name): bool
    {
        $prefix = $name.':';
        foreach ($headers as $h) {
            if (strncasecmp($h, $prefix, strlen($prefix)) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<string, string>
     */
    private function headersToMap(array $headers): array
    {
        $map = [];
        foreach ($headers as $h) {
            if (strpos($h, ':') === false) {
                continue;
            }
            [$k, $v] = explode(':', $h, 2);
            $map[trim($k)] = trim($v);
        }

        return $map;
    }

    /**
     * @return array{0: string, 1: ?string, 2: ?string} [url, body, contentType for POST]
     */
    private function buildUrlAndBody(string $method, string $absUrl, array $params, string $apiMode): array
    {
        $params = Util\Util::objectsToIds($params);

        if ($method === 'post') {
            $absUrl = Util\Util::utf8($absUrl);
            if ($apiMode === 'v2') {
                if (is_array($params) && count($params) === 0) {
                    return [$absUrl, null, 'application/json'];
                }

                return [$absUrl, json_encode($params), 'application/json'];
            }

            return [$absUrl, Util\Util::encodeParameters($params), 'application/x-www-form-urlencoded'];
        }

        if (count($params) === 0) {
            return [Util\Util::utf8($absUrl), null, null];
        }

        $encoded = Util\Util::encodeParameters($params, $apiMode);
        $absUrl = Util\Util::utf8($absUrl).'?'.$encoded;

        return [$absUrl, null, null];
    }

    private function sendPost($pending, string $absUrl, ?string $body, ?string $contentType)
    {
        if ($body === null) {
            return $pending->withBody('', $contentType ?? 'application/json')->post($absUrl);
        }

        return $pending
            ->withBody($body, $contentType ?? 'application/x-www-form-urlencoded')
            ->post($absUrl);
    }

    private function normalizeResponseHeaders(\Illuminate\Http\Client\Response $response): Util\CaseInsensitiveArray
    {
        $flat = [];
        foreach ($response->headers() as $key => $values) {
            $flat[$key] = is_array($values) ? implode(', ', $values) : (string) $values;
        }

        return new Util\CaseInsensitiveArray($flat);
    }
}
