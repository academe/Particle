<?php

namespace articfox1986\phpparticle\Psr7;

/**
 * Links this package to the PSR-7 implementation.
 */

interface ConnnectorInterface
{
    /**
     * Creates a new PSR-7 request.
     *
     * @param string                               $method
     * @param string|UriInterface                  $uri
     * @param array                                $headers
     * @param resource|string|StreamInterface|null $body
     * @param string                               $protocolVersion
     *
     * @return RequestInterface
     */
    public function createRequest(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    );

    /**
     * Creates an PSR-7 URI.
     *
     * @param string|UriInterface $uri
     *
     * @return UriInterface
     *
     * @throws \InvalidArgumentException If the $uri argument can not be converted into a valid URI.
     */
    public function createUri($uri);

    /**
     * Creates a new PSR-7 stream.
     *
     * @param string|resource|StreamInterface|null $body
     *
     * @return StreamInterface
     *
     * @throws \InvalidArgumentException If the stream body is invalid.
     * @throws \RuntimeException         If creating the stream from $body fails. 
     */
    public function createStream($body = null);

    /**
     * @return StreamInterface
     */
    public function createStreamForUrlEncoded(array $params = []);

    /**
     * Takes an array of fields and returns as a multipart/form-data stream.
     * Each field is an array keyed on "name" and with "contents" elements, and optional
     * "filename" and "headers" elements, or a string.
     * e.g. [
     *  'file' => ['contents' => $fileStreamOrString, 'filename' => 'myfile.cpp'],
     *  'file_type' => 'binary,'
     * ]
     * @return StreamInterface
     */
    public function createStreamForMultipart(array $params, $boundary);
}
