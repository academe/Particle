<?php

namespace Academe\Particle;

/**
 * Example usages of [most of] the API functions.
 *
 * TODO: example:-
 * - claim device
 * - delete device
 * - list webhooks
 * - create webhook
 * - delete webhook
 * - get variable
 * - signal device
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

    /**
     * Give your device a random name, or speciify a new name.
     */
    public function setDeviceName($name = null)
    {
        if (empty($name)) {
            $name = uniqid('phpSpark_');
        }

        // The PSR-7 request.
        $request = $this->createApi()->renameDevice($this->deviceId, $name);

        // Send the request.
        $response = $this->sendMessage($request);

        return $response;
    }

    /**
     * Get a list of all devices accessible through this token.
     */
    public function listDevices()
    {
        // The PSR-7 request.
        $request = $this->createApi()->listDevices();

        // Send the request.
        $response = $this->sendMessage($request);

        return $response;
    }

    /**
     * Get a list of all tokens for this cloud account.
     */
    public function listTokens()
    {
        // The PSR-7 request.
        $request = $this->createApi()->listAccessTokens();

        // Send the request.
        $response = $this->sendMessage($request);

        return $response;
    }

    /**
     * Create a new OAuth access token for this cloud account.
     * Note, this is not an OAuth renewal, but a brand new token.
     */
    public function newAccessToken()
    {
        // The PSR-7 request.
        // The token will last one hour (3600 seconds).
        $request = $this->createApi()->newAccessToken(3600);

        // Send the request.
        $response = $this->sendMessage($request);

        return $response;
    }

    /**
     * Delete an OAuth access token for this cloud account.
     */
    public function deleteAccessToken($token)
    {
        // The PSR-7 request.
        $request = $this->createApi()->deleteAccessToken($token);

        // Send the request.
        $response = $this->sendMessage($request);

        return $response;
    }

    /**
     * Get details for the default (for the demo) or another specified device.
     */
    public function getDevice($deviceId = null)
    {
        if (empty($deviceId)) {
            $deviceId = $this->deviceId;
        }

        // The PSR-7 request.
        $request = $this->createApi()->getDevice($deviceId);

        // Send the request.
        $response = $this->sendMessage($request);

        return $response;
    }

    /**
     * Call a function.
     * Turn on digital outut D0, then turn if off again.
     * This will flash an LED connected to this pin.
     * The Tinker application must be flashed first for this to work.
     */
    public function callFunction()
    {
        // The PSR-7 request.
        $request_on = $this->createApi()->callFunction($this->deviceId, 'digitalwrite', 'D7,HIGH');
        $request_off = $this->createApi()->callFunction($this->deviceId, 'digitalwrite', 'D7,LOW');

        // Send the request to turn the LED on.
        $response = $this->sendMessage($request_on);

        // Leave the LED on for 500mS.
        sleep(0.5);

        // Send the request to turn the LED off.
        $response = $this->sendMessage($request_off);

        return $response;
    }

    public function listOranizations()
    {
        // The PSR-7 request.
        $request = $this->createApi()->listOranizations();

        // Send the request.
        $response = $this->sendMessage($request);

        return $response;
    }

    public function getOranization($slug)
    {
        // The PSR-7 request.
        $request = $this->createApi()->getOranization($slug);

        // Send the request.
        $response = $this->sendMessage($request);

        return $response;
    }

    public function removeMember($slug, $username)
    {
        // The PSR-7 request.
        $request = $this->createApi()->removeMember($slug, $username);

        // Send the request.
        $response = $this->sendMessage($request);

        return $response;
    }
}
