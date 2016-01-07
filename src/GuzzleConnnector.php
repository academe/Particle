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
use GuzzleHttp\Psr7\MultipartStream;

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

    /**
     * @return StreamInterface
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
     * @return StreamInterface
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
     * @return StreamInterface
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
                    throw new Exception(sprintf('Missing "contents" for multipart message field "%s"', $name));
                }

                $field = array_merge($field, $param);
            } else {
                throw new Exception(sprintf('Unsupported contents type for multipart message field "%s"', $name));
            }

            $content[] = $field;
        }

        return new MultipartStream($content, $boundary);
    }
}
