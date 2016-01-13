<?php

namespace articfox1986\phpparticle;

/**
 * Connector for Guzzle.
 */

use Exception;
use InvalidArgumentException;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\MultipartStream;

class GuzzleConnnector implements ConnnectorInterface
{
    /**
     * Check that guzzlehttp/psr7 is installed, which is optional for the package,
     * but necessary for this connector.
     */
    public function __construct()
    {
        if ( ! class_exists(Request::class)) {
            throw new Exception('guzzlehttp/psr7 is not installed; it is required for the Guzzle connector');
        }
    }

    public function createRequest(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    ) {
        return new Request($method, $uri, $headers, $body, $protocolVersion);
    }

    public function createUri($uri)
    {
        return new Uri($uri);
    }

    /**
     * @return GuzzleHttp\Psr7\Stream
     */
    public function createStream($body = null)
    {
        return new Stream($body);
    }

    /**
     * @return GuzzleHttp\Psr7\Stream
     */
    public function createStreamForString($body)
    {
        return \GuzzleHttp\Psr7\stream_for($body);

        $stream = fopen('php://temp', 'rw');
        fwrite($stream, $body);
        return $this->createStream($stream);
    }

    /**
     * Takes a simple array and returns as a URL encoded stream.
     *
     * @return GuzzleHttp\Psr7\Stream
     */
    public function createStreamForUrlEncoded(array $params = [])
    {
        return $this->createStreamForString(http_build_query($params));
    }

    /**
     * Takes an array of fields and returns as a multipart/form-data stream.
     * Each field is an array keyed on "name" and with "contents" elements, and optional
     * "filename" and "headers" elements, or a string.
     * e.g. [
     *  'file' => ['contents' => $fileStreamOrString, 'filename' => 'myfile.cpp'],
     *  'file_type' => 'binary,'
     * ]
     * Example here: https://gist.github.com/matthew-james/e6505f54fe6fd6117030
     *
     * @return GuzzleHttp\Psr7\MultipartStream
     */
    public function createStreamForMultipart(array $params, $boundary)
    {
        $content = [];

        foreach($params as $name => $param) {
            $field = ['name' => $name];

            if (is_string($param)) {
                $field['contents'] = $param;
            } elseif (is_array($param)) {
                // Check for the minimal required elements.
                if ( ! array_key_exists('contents', $param)) {
                    throw new InvalidArgumentException(sprintf('Missing "contents" for multipart message field "%s"', $name));
                }

                $field = array_merge($field, $param);
            } else {
                throw new InvalidArgumentException(sprintf('Unsupported contents type for multipart message field "%s"', $name));
            }

            $content[] = $field;
        }

        return new MultipartStream($content, $boundary);
    }
}
