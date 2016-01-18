<?php

namespace Academe\Particle;

/*
 * @project phpParticle
 * @file    ParticleAPI.php
 * @authors Harrison Jones (harrison@hhj.me)
 *          Devin Pearson  (devin@blackhat.co.za)
 *          Jason Judge    (jason@academe.co.uk)
 * @date    March 12, 2015
 * @brief   PHP Class for interacting with the Particle Cloud (particle.io)
 */

use Psr\Log\LoggerInterface;
use Psr\Http\Message\UriInterface;

use academe\particle\Psr7\ConnnectorInterface;

use Exception;
use InvalidArgumentException;

class ParticleApi
{
    // Authentication details for authenticating with the API.
    protected $auth_email;
    protected $auth_password;

    // Access token  for the API.
    protected $accessToken;

    // Latest return response (the decoded body).
    protected $result;

    // Debug logging can be turned on or off.
    protected $debugFlag = false;

    // The logger object.
    protected $debugLogger;

    protected $endpoint = 'https://api.particle.io/';

    protected $apiVersion = 'v1';

    // The \articfox1986\phpparticle\ConnnectorInterface object for supplying PSR-7 messages.
    protected $psr7;

    /**
     * @param articfox1986\phpparticle\ConnnectorInterface $psr7Connector Object that provides PSR-7 messages.
     */
    public function __construct(ConnnectorInterface $psr7Connector)
    {
        $this->psr7 = $psr7Connector;
    }

    /**
     * Sets the api endpoint used. Default is the particle.io api
     *
     * @param string $endpoint A url for the api you want to use (default: "https://api.particle.io/")
     *
     * @return self
     */
    public function setEndpoint($endpoint)
    {
        $clone = clone $this;
        $clone->endpoint = $endpoint;
        return $clone;
    }

    /**
     * Gets the API endpoint used
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Gets the API version path component.
     *
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * Sets the authentication details for the Particle cloud account.
     *
     * @param string $email The account email (the username is an email).
     * @param string $password The account password.
     *
     * @return self
     */
    public function setAuth($email, $password)
    {
        $clone = clone $this;

        $clone->auth_email = $email;
        $clone->auth_password = $password;

        return $clone;
    }

    /**
     * Gets the Particle cloud account auth email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->auth_email;
    }

    /**
     * Gets the Particle cloud account auth password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->auth_password;
    }

    /**
     * Clears the cloud account authentication info (email and password).
     *
     * @return self
     */
    public function clearAuth()
    {
        return $this->setAuth(null, null);
    }

    /**
     * Sets the access token for Basic authentication with the API.
     *
     * @param string $accessToken The access token to authenticate with.
     *
     * @return self
     */
    public function setAccessToken($accessToken)
    {
        $clone = clone $this;
        $clone->accessToken = $accessToken;
        return $clone;
    }

    /**
     * Gets the Access Token
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Clears the access token info. Internally set to null.
     *
     * @return self
     */
    public function clearAccessToken()
    {
        return $this->setAccessToken(null);
    }

    /**
     * Provide the logger for debug logging.
     *
     * @param Psr\Log\LoggerInterface $debugLogger Object to handle all logged messages.
     *
     * @return self
     */
    public function setLogger(LoggerInterface $debugLogger)
    {
        $clone = clone $this;
        $clone->debugLogger = $debugLogger;
        return $clone;
    }

    /**
     * Gets the debug logger object, or null if none set.
     * @return null|Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->debugLogger;
    }

    /**
     * Turn internal debugging on or off.
     * Note: calls made to debug() will always be logged regardless of this setting.
     *
     * @param boolean $debug true turns internal debugging on.
     *
     * @return self
     */
    public function setDebug($debug)
    {
        $clone = clone $this;
        $clone->debugFlag = (bool)$debug ? true : false;
        return $clone;
    }

    /**
     * Gets whether debug is on or off.
     *
     * @return boolean
     */
    public function getDebug()
    {
        return $this->debugFlag;
    }

    /**
     * Sends debug text and data to the debug logger.
     *
     * @param string $debugText The debug string to log.
     * @param boolean $override True to override the internal debug on/off state and always outputs the debugText. If set to false it follows the internal debug on/off state.
     * @param null|array $context Substitution fields and other details to support the debug message.
     *
     * @return void
     */
    protected function _debug($debugText, $override = false, array $context = [])
    {
        if (isset($this->debugLogger) && ($this->debugFlag || $override)) {
            $this->debugLogger->debug($debugText, $context);
        }
    }

    /**
     * Logs a debug message and optional substitution variables/context details.
     *
     * @param string $debugText The debug string to output
     *
     * @return void
     */
    public function debug($debugText, array $context = [])
    {
        return $this->_debug($debugText, true, $context);
    }

    /**
     * Runs a particle function on the device. Requires the accessToken to be set
     * Note: the raw format is documented, but does not seem to affect the returned result.
     *
     * @param string $deviceID The device ID of the device to call the function on
     * @param string $deviceFunction The name function to call
     * @param string $args Function argument with a maximum length of 63 characters
     * @param boolean $raw True if you want the just the function return value returned
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function callFunction($deviceID, $deviceFunction, $args, $raw = false)
    {
        $url = $this->getUrl(['devices', $deviceID, $deviceFunction]);

        $params = ['args' => $args];

        // Set the raw return format if required.
        if ((bool)$raw) {
            $params['format'] = 'raw';
        }

        $result = $this->makeRequest('post', $url, $params);
        return $result;
    }

    /**
     * Gets the value of a particle variable.
     * 
     * @param string $deviceID The device ID of the device to call the function on
     * @param string $variableName The name of the variable to retrieve
     * @param boolean $raw Set to true to get just the raw value, without device details.
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function getVariable($deviceID, $variableName, $raw = false)
    {
        $url = $this->psr7->createUri($this->getUrl(['devices', $deviceID, $variableName]));

        // Set the raw return format if required.
        if ((bool)$raw) {
            $url = $url->withQueryValue($url, 'format', 'raw');
        }

        $result = $this->makeRequest('get', $url);
        return $result;
    }

    /**
     * List devices the authenticated user has access to.
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function listDevices()
    {
        $url = $this->getUrl(['devices']);
        $result = $this->makeRequest('get', $url, []);
        return $result;
    }

    /**
     * Get basic information about the given device, including the custom variables and functions it has exposed.
     *
     * @param string $deviceID The device ID of the device
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function getDevice($deviceID)
    {
        $url = $this->getUrl(['devices', $deviceID]);
        $result = $this->makeRequest('get', $url, []);
        return $result;
    }

    /**
     * Set the name/renames your core.
     *
     * @param string $deviceID The device ID of the device to rename
     * @param string $name The new name of the device
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function renameDevice($deviceID, $name)
    {
        $url = $this->getUrl(['devices', $deviceID]);
        $result = $this->makeRequest('put', $url, ['name' => $name]);
        return $result;
    }

    /**
     * Generate a device claim code that allows the device to be successfully
     * claimed to a user's account during the SoftAP setup process.
     *
     * @param string|null $iccid ICCID number of the SIM you are generating a claim for. Used as the claim code.
     * @param string|null $imei IMEI number of the Electron you are generating a claim for. Used as the claim code if no ICCID.
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function claimCode($iccid = null, $imei = null)
    {
        $params = [];

        if (isset($iccid)) {
            $params['iccid'] = $iccid;
        } elseif (isset($imei)) {
            $params['imei'] = $imei;
        } else {
            throw new InvalidArgumentException('Neither ICCID nor IMEI were supplied; at least one is needed');
        }

        $url = $this->getUrl(['device_claims']);
        $result = $this->makeRequest('post', $url, $params);
        return $result;
    }

    /**
     * Attempts to add a device to your cloud account.
     * Note, you may want to follow this up with a call to "setName" as new Cores names are blank.
     * Interestingly, if claiming an order core their name is retained across the unclaim/claim process.
     *
     * @param string $deviceID The device ID of the device to claim. 
     * @param boolean $requestTransfer true to request the already-claimed device be transfered to your account. If false will try to claim but not automatically send a transfer request.
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function claimDevice($deviceID, $requestTransfer = false)
    {
        $url = $this->getUrl(['devices']);
        $params = ['id' => $deviceID];

        if ((bool)$requestTransfer) {
            $params['request_transfer'] = 'true';
        }

        $result = $this->makeRequest('post', $url, $params);
        return $result;
    }

    /**
     * Removes the core from your cloud account.
     *
     * @param string $deviceID The device ID of the device to remove from your account.
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function removeDevice($deviceID)
    {
        $url = $this->getUrl(['devices', $deviceID]);

        $result = $this->makeRequest('delete', $url, []);
        return $result;
    }

    /**
     * Uploads a source or compiled firmware file to the core.
     * A binary firmware is the complete system and application pre-compiled.
     * A source firmware is just the application source, which will be compiled
     * on the cloud account before being uploaded to the Paricle device.
     *
     * @param string $deviceID The device ID of the device to upload the code to.
     * @param string $filename The filename of the firmware file to upload to the device. e.g. tinker.cpp.
     * @param string|resource $file The pathname to the firmware file to upload or a resource.
     * @param boolean $isBinary Set to true if uploading a .bin file or false for non-binary source code.
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function uploadFirmware($deviceID, $filename, $file, $isBinary = false)
    {
        $url = $this->getUrl(['devices', $deviceID]);

        if (is_string($file)) {
            $resource = fopen($file, 'r');
        } elseif (is_resource($file)) {
            $resource = $file;
        } else {
            throw new InvalidArgumentException('Unexpected file data type; must be an open resource or a pathname');
        }

        $file_stream = $this->psr7->createStream($resource);

        $params = [
            'file' => [
                'contents' => $file_stream,
                'filename' => $filename,
                'headers' => ['Content-Type' => 'application/octet-stream']
            ]
        ];

        if ($isBinary === true) {
            $params['file_type'] = 'binary';
        }

        // The file will be encoded in multipart/form-data format
        $result = $this->makeRequest('put', $url, $params, 'token', true);
        return $result; 
    }

    /**
     * Gets a list of your tokens from the particle cloud.
     * Requires the email/password (account) auth to be set.
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function listAccessTokens()
    {
        $url = $this->getUrl(['access_tokens']);
        $result = $this->makeRequest('get', $url, [], 'basic');
        return $result;
    }

    /**
     * Creates an access token that gives you access to the Cloud API.
     * Requires the email/password auth to be set.
     * TODO: support full OAuth client login and authorisation using an OAuth library (League should work).
     *
     * @param int $expires_in How long the token will be valid for, in seconds. 0=forever.
     * @param string $expires_at When the token should expire. An ISO8601 format date string.
     * @param string $clientID The clientID. If you don't have one of these (only used in OAuth applications) set to null.
     * @param string $clientSecret The clientSecret. If you don't have one of these (only used in OAuth applications) set to null.
     *
     * @return Psr\Http\Message\RequestInterface
     */

    public function newAccessToken(
        $expires_in = null,
        $expires_at = null,
        $clientID = null,
        $clientSecret = null
    ) {
        // The Particle account username and password must be supplied as
        // parameters.
        // 'password' is the only grant type documented at present.
        $fields = [
            'grant_type' => 'password',
            'username' => $this->auth_email,
            'password' => $this->auth_password,
        ];

        if (isset($expires_in)) {
            $fields['expires_in'] = intval($expires_in);
        }

        if (isset($expires_at)) {
            $fields['expires_at'] = $expires_at;
        }

        // CHECKME: what are these for? Token renewal?
        if ($clientID && $clientSecret) {
            $fields['client_id'] = $clientID;
            $fields['client_secret'] = $clientSecret;
        }

        // This URL does not have the API version number in its path, so
        // presumably works for all API versions.
        $url = $this->endpoint . 'oauth/token';

        $result = $this->makeRequest('post', $url, $fields, 'basic-dummy');
        return $result;
    }

    /**
     * Removes the token from the particle cloud.
     * Requires the email/password auth to be set.
     *
     * @param string $token The access token to remove
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function deleteAccessToken($token)
    {
        $url = $this->getUrl(['access_tokens', $token]);
        $result = $this->makeRequest('delete', $url, [], 'basic');
        return $result;
    }

    /**
     * Gets a list of webhooks from the particle cloud. Requires the accessToken to be set
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function listWebhooks()
    {
        $fields = [];
        $url = $this->getUrl(['webhooks']);

        $result = $this->makeRequest('get', $url, $fields);
        return $result;
    }

    /**
     * Creates a new webhook on the particle cloud.
     * @param string $event The event name used to trigger the webhook
     * @param string $webhookUrl The url to query once the event has occured
     * @param string $extras See http://docs.particle.io/webhooks/#webhook-options
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function newWebhook($event, $webhookUrl, array $extras = [])
    {
        $url = $this->getUrl(['webhooks']);

        $fields = ['event' => $event, 'url' => $webhookUrl];

        if ( ! empty($extras)) {
            $fields = array_merge($fields, $extras);
        }

        $result = $this->makeRequest('post', $url, $fields);
        return $result;
    }

    /**
     * Delete webhooks from the particle cloud.
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function deleteWebhook($webhookID)
    {
        $fields = [];

        $url = $this->getUrl(['webhooks', $webhookID]);

        $result = $this->makeRequest('delete', $url, $fields);
        return $result;
    }

    /**
     * Sets the particle core signal mode state. Requires the accessToken to be set
     *
     * @param string $deviceID The device ID of the device to send the signal mode state change command to.
     * @param int $signalState The signal state: 0 returns the RGB led back to normmal & 1 makes it flash a rainbow of color
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function signalDevice($deviceID, $signalState = 0)
    {
        $fields = ['signal' => $signalState];

        $url = $this->getUrl(['devices', $deviceID]);

        $result = $this->makeRequest('put', $url, $fields);
        return $result;
    }

    /**
     * List Organizations the currently authenticated user has access to.
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function listOranizations()
    {
        $url = $this->getUrl(['orgs']);

        $result = $this->makeRequest('get', $url);
        return $result;
    }

    /**
     * Get details for an Organizations the currently authenticated user has access to.
     *
     * @param string $slug The Organization slug
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function getOranization($slug)
    {
        $url = $this->getUrl(['orgs', $slug]);

        $result = $this->makeRequest('get', $url);
        return $result;
    }

    /**
     * Remove a team member from an Organization.
     *
     * @param string $orgSlug The Organization slug.
     * @param string $username The username of the member to remove, normally an email addreess.
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function removeMember($orgSlug, $username)
    {
        $url = $this->getUrl(['orgs', $orgSlug, 'users', $username]);

        $result = $this->makeRequest('delete', $url);
        return $result;
    }

    /**
     * Get a product for an Organization.
     *
     * @param string $orgSlug The Organization slug.
     * @param string $productSlug The slug for the product.
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function getProduct($orgSlug, $productSlug)
    {
        $url = $this->getUrl(['orgs', $orgSlug, 'products', $productSlug]);

        $result = $this->makeRequest('get', $url);
        return $result;
    }

    /**
     * Generate a device claim code for a product.
     *
     * @param string $orgSlug The Organization slug.
     * @param string $productSlug The slug for the product.
     * @param null|string $activationCode Activation Code. Only required if product is in private beta.
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function createProductClaimCode($orgSlug, $productSlug, $activationCode = null)
    {
        $url = $this->getUrl(['orgs', $orgSlug, 'products', $productSlug, 'device_claims']);

        $result = $this->makeRequest('post', $url, ['activation_code' => $activationCode]);
        return $result;
    }

    /**
     * Remove a device from a organization product.
     *
     * @param string $orgSlug The Organization slug.
     * @param string $productSlug The slug for the product.
     * @param string $deviceId The device to remove.
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function removeProductDevice($orgSlug, $productSlug, $deviceId)
    {
        $url = $this->getUrl(['orgs', $orgSlug, 'products', $productSlug, 'devices', $deviceId]);

        $result = $this->makeRequest('delete', $url);
        return $result;
    }

    /**
     * Create a customer for an organization.
     *
     * @param string $orgSlug The Organization slug.
     * @param string $productSlug The slug for the product.
     * @param string $deviceId The device to remove.
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function createCustomer($orgSlug)
    {
        $url = $this->getUrl(['orgs', $orgSlug, 'customers']);

        $result = $this->makeRequest('post', $url, [], 'basic');
        return $result;
    }

    /**
     * Returns the URL for the API, with additional path components.
     *
     * @return string The URL without a trailing slash.
     */
    public function getUrl(array $path = [])
    {
        $url = $this->endpoint . $this->apiVersion;

        foreach($path as $level) {
            $url .= '/' . rawurlencode($level);
        }

        return $url;
    }

    /**
     * Create a PSR-7 Request message for an API function.
     *
     * @param string $type The REST verb (get, post, put, delete)
     * @param string|Psr\Http\Message\UriInterface $uri The endpoint URI
     * @param array $params Additional GET or POST parameters
     * @param string $authType Authentication type (token, basic, basic-dummy)
     * @param boolean $useMultipart true if $params contains a file to upload; forces a multipart/form-data message.
     */
    protected function makeRequest($type, $uri, $params = [], $authType = 'token', $useMultipart = false)
    {
        $type = strtoupper($type);
        $authType = strtolower($authType);

        if (is_string($uri)) {
            $uri = $this->psr7->createUri($uri);
        } elseif ( ! $uri instanceof UriInterface) {
            throw new InvalidArgumentException('Unexpected uri data type; must be a string or GuzzleHttp\Psr7\Uri');
        }

        // Add params as GET parameters to the URI if this is a GET or DELETE request.
        if (($type === 'GET' || $type === 'DELETE') && $params) {
            foreach($params as $key => $value) {
                $uri = $uri->withQueryValue($uri, $key, $value);
            }
        }

        // Create a PSR-7 Request message.
        $request = $this->psr7->createRequest($type, $uri);

        if ($type === 'POST' || $type === 'PUT') {
            // In some implememtations the PSR-7 message class may auto-detect the content
            // and generate the Content-Type header. We can't rely on that for portability,
            // as it is not a part of the PSR-7 spec, so we create our own Content-Type
            // headers here.

            if ($useMultipart) {
                // Add multipart form-date parameters (including files), to the body.
                // We create the boundary here so we are not locked into a specific multipart stream
                // implementation, as PSR-7 does not explicitly handle the boundary string.
                $boundary = uniqid();
                $body = $this->psr7->createStreamForMultipart($params, $boundary);
                $request = $request->withBody($body);
                $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . $boundary);
            } else {
                // Add simple URL encoded POST parameters to the message body.
                $body = $this->psr7->createStreamForUrlEncoded($params);
                $request = $request->withBody($body);
                $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
            }
        }

        // OAuth v1 token.
        if ($authType === 'token') {
            if ($this->accessToken) {
                // Add the access token to the parameters.
                $request = $request->withHeader('Authorization', 'Bearer ' . $this->accessToken);
            } else {
                throw new InvalidArgumentException('No access token set');
            }
        }

        // HTTP Basic authentication against the Particle cloud account.
        if ($authType === 'basic') {
            if ($this->auth_email && $this->auth_password) {
                $request = $request->withHeader(
                    'Authorization',
                    'Basic ' . base64_encode($this->auth_email . ':' . $this->auth_password)
                );
            } else {
                throw new InvalidArgumentException('No auth credentials (email/password) set');
            }
        }

        // Dummy basic authentication needed when creating new access tokens.
        if ($authType === 'basic-dummy') {
            $request = $request->withHeader(
                'Authorization',
                'Basic ' . base64_encode('particle:particle')
            );
        }

        return $request;
    }
}
