<?php

namespace Academe\Particle;

/**
 * Example usages of all the API functions.
 */

use Academe\Particle\ParticleApi;
use Academe\Particle\Psr7\GuzzleConnnector;
use GuzzleHttp\Client;
use Psr\Http\Message\RequestInterface;

class Example
{
    // Access token to the account.
    protected $accessToken;

    // The device we are going to be playing with.
    protected $deviceId;

    // Credentials for the Partical cloud account.
    protected $email;
    protected $password;

    // HTTP client.
    protected $httpClient;

    /**
     * Differenjt credetials are needed, depending on the function being
     * performed. This makes them all optional in the constructor.
     */
    public function __construct($accessToken = null, $deviceId = null, $email = null, $password = null)
    {
        $this->accessToken = $accessToken;
        $this->deviceId = $deviceId;
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * Create an API object.
     */
    public function createApi()
    {
        // This connector uses Guzzle 6 to generate the PSR-7 objects.
        $psr7 = new GuzzleConnnector();

        // Create the API object.
        $particleApi = new ParticleAPI($psr7);

        // Set the access token.
        if ($this->accessToken) {
            $particleApi = $particleApi->setAccessToken($this->accessToken);
        }

        // Set the cloud account credentials.
        if ($this->email) {
            $particleApi = $particleApi->setAuth($this->email, $this->password);
        }

        return $particleApi;
    }

    /**
     * Create a HTTP client.
     */
    public function createHttpClient()
    {
        if (empty($this->httpClient)) {
            $this->httpClient = new Client([
                'defaults' => [
                    'timeout' => 2,
                ],
            ]);
        }

        return $this->httpClient;
    }

    /**
     * Send a PSR-7 request message using the HTTP client.
     */
    public function sendMessage(RequestInterface $request)
    {
        $client = $this->createHttpClient();

        // TODO: catch HTTP exceptions here, and log them at least.
        $response = $client->send($request);

        return $response;
    }

    /**
     * Flash the Particle with the Tinker application.
     */
    public function flashTinker()
    {
        // Locate the application source.

        // Read the local copy from the filesystem.
        $firmwareFilename = 'application.cpp';
        $firmwarePathname = __DIR__ . '/../firmware/' . $firmwareFilename;

        // Alternatively, pull the latest source from the firmware repository.
        /*
        $firmwarePathname = fopen(
            'https://raw.githubusercontent.com/spark/firmware/develop/user/applications/tinker/application.cpp',
            'r'
        );
        */

        // Create a PSR-7 message to flash the source.
        $request = $this->createApi()->uploadFirmware(
            $this->deviceId,
            $firmwareFilename,
            $firmwarePathname,
            false
        );

        // Now send the message to the cloud.
        $response = $this->sendMessage($request);

        return $response;
    }
}
