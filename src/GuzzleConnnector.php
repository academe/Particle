<?php

namespace articfox1986\phpparticle;

/**
 *
 */

//use Psr\Http\Message\UriInterface;
//use Psr\Http\Message\RequestInterface;
//use Psr\Http\Message\StreamInterface;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Stream;

class GuzzleConnnector implements ConnnectorInterface
{
    /**
     * TODO: Check that guzzlehttp/psr7 is installed.
     */
    public function __construct()
    {
    }

    public function createRequest(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    )
    {
        return new Request($method, $uri, $headers, $body, $protocolVersion);
    }

    public function createUri($uri)
    {
        return new Uri($uri);
    }

    public function createStream($body = null)
    {
        return new Stream($body);
    }

    public function createStreamForString($body)
    {
        return \GuzzleHttp\Psr7\stream_for($body);

        $stream = fopen('php://temp', 'rw');
        fwrite($stream, $body);
        return $this->createStream($stream);
    }
}
