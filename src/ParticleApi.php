<?php namespace articfox1986\phpparticle;

use Psr\Log\LoggerInterface;


/*
 * @project phpParticle
 * @file    ParticleAPI.php
 * @authors Harrison Jones (harrison@hhj.me)
 *          Devin Pearson  (devin@blackhat.co.za)
 *          Jason Judge    (jason@academe.co.uk)
 * @date    March 12, 2015
 * @brief   PHP Class for interacting with the Particle Cloud (particle.io)
 */
class ParticleApi
{
    // Authentication details for authenticating with the API.
    protected $auth_email;
    protected $auth_password;

    // Access token  for the API.
    protected $accessToken;

    protected $disableSSL = true;

    protected $_error = 'No Error';
    protected $_errorSource = 'None';

    // Latest return response (the decoded body).
    protected $result;

    // Debug logging can be turned on or off.
    protected $debugFlag = false;

    // The logger object.
    protected $debugLogger;

    protected $endpoint = 'https://api.particle.io/';
    protected $curlTimeout = 10;

    protected $apiVersion = 'v1';

    /**
     * Sets the api endpoint used. Default is the particle.io api
     *
     * @param string $endpoint A url for the api you want to use (default: "https://api.particle.io/")
     *
     * @return self
     *
     */
    public function setEndpoint($endpoint)
    {
        $clone = clone $this;
        $clone->endpoint = $endpoint;
        return $clone;
    }

    /**
     * Gets the API endpoint used
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Gets the API version path component.
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * Sets the timeout used for calls against the api. 
     *
     * @param int $timeout The amount of time, in seconds, for a call to wait for data before returning with a TIMEOUT error
     *
     * @return void
     *
     */
    public function setTimeout($timeout)
    {
        if ( ! is_numeric($timeout)) {
            throw new Exception('Non numeric timeout');
        }

        $clone = clone $this;
        $clone->curlTimeout = intval($timeout);
        return $clone;
    }

    /**
     * Gets the CURL timeout
     * @return double
     */
    public function getTimeout()
    {
        return $this->curlTimeout;
    }

    /**
     * Sets the authentication details for authenticating with the API
     *
     * @param string $email The email to authenticate with
     * @param string $password The password to authenticate with
     *
     * @return self
     *
     */
    public function setAuth($email, $password)
    {
        $clone = clone $this;

        $clone->auth_email = $email;
        $clone->auth_password = $password;

        return $clone;
    }

    /**
     * Gets the auth email
     * @return string
     */
    public function getEmail()
    {
        return $this->auth_email;
    }

    /**
     * Gets the auth password
     * @return string
     */
    public function getPassword()
    {
        return $this->auth_password;
    }

    /**
     * Clears all the authentication info (email and password).
     * Internally set to null.
     * Subsequent calls which require a email/password will fail.
     *
     * @return self
     *
     */
    public function clearAuth()
    {
        return $this->setAuth(null, null);
    }

    /**
     * Sets the access token for authenticating with the API
     *
     * @param string $accessToken The access token to authenticate with
     *
     * @return $this
     *
     */
    public function setAccessToken($accessToken)
    {
        $clone = clone $this;
        $clone->accessToken = $accessToken;
        return $clone;
    }

    /**
     * Gets the Access Token
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Clears the access token info. Internally set to false. Subsequent calls which require an access token will fail
     *
     * @return self
     *
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
     *
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
     * Note, external calls made to debug() will always display regardless of this setting.
     *
     * @param boolean $debug true turns on internal debugging & false turns off internal debugging
     *
     * @return self
     *
     */
    public function setDebug($debug)
    {
        $clone = clone $this;
        $clone->debugFlag = (bool)$debug ? true : false;
        return $clone;
    }

    /**
     * Gets whether debug is on or off
     * @return boolean
     */
    public function getDebug()
    {
        return $this->debugFlag;
    }

    /**
     * Turn on or off SSL verification (it's a CURL thing).
     * For testing, before you get the certificates setup, you might need to disable SSL verificatioon.
     * Note this is a security concern
     *
     * @param boolean $disableSSL true communicates with api endpoints without validating security certificates
     *
     * @return self
     *
     */
    public function setDisableSSL($disableSSL)
    {
        $clone = clone $this;
        $clone->disableSSL = (bool)$disableSSL ? true : false;
        return $clone;
    }

    /**
     * Gets the whether ssls are disabled
     * @return boolean
     */
    public function getDisableSSL()
    {
        return $this->disableSSL;
    }

    /**
     * Sets the internal _error & _errorSource variables.
     * Allow for tracking which function resulted in an error and what that error was
     *
     * @param string $errorText The value to set _error to
     * @param string $errorSource The value to set _errorSource to
     *
     * @return void
     *
     */
    protected function _setError($errorText, $errorSource)
    {
        $this->_error = $errorText;
        $this->_errorSource = $errorSource;
    }

    /**
     * Sets the internal _errorSource. Allow for tracking which function resulted in an error
     *
     * @param string $errorSource The value to set _errorSource to
     *
     * @return void
     *
     */
    protected function _setErrorSource($errorSource)
    {
        $this->_errorSource = $errorSource;
    }

    /**
     * Outputs the desired debug text formatted if required
     *
     * @param string $debugText The debug string to output
     * @param boolean $override If set to true overrides the internal debug on/off state and always outputs the debugText. If set to false it follows the internal debug on/off state
     * @param null|array $context Substribution fields or other details to support the debug message.
     *
     * @return void
     *
     */
    protected function _debug($debugText, $override = false, array $context = [])
    {
        if (isset($this->debugLogger) && ($this->debugFlag || $override)) {
            $this->debugLogger->debug($debugText, $context);
        }
    }

    /**
     * Logs a debug message and optional substituyion variables/context details.
     *
     * @param string $debugText The debug string to output
     * @return void
     */
    public function debug($debugText, array $context = [])
    {
        $this->_debug($debugText, $override = true, $context);
    }

    /**
     * Logs the desired debug array. Use debug() instead.
     *
     * @deprecated
     * @param string $debugArray The debug array to log
     * @return void
     */
    public function debug_r(array $debugArray)
    {
        return $this->_debug('Data', $override = true, $debugArray);
    }

    /**
     * Runs a particle function on the device. Requires the accessToken to be set
     *
     * @param string $deviceID The device ID of the device to call the function on
     * @param string $deviceFunction The name function to call
     * @param string $params The parameters to send to the function (the 'args')
     *
     * @return boolean true if the call was successful, false otherwise. Use getResult to get the api result and use getError & getErrorSource to determine what happened in the event of an error
     */
    public function callFunction($deviceID, $deviceFunction, $params)
    {
            $url = $this->getUrl(['devices', $deviceID, $deviceFunction]);

            $result =  $this->_curlRequest($url, ['args' => $params], 'post');

            return $result;
    }

    /**
     * Gets the value of a particle variable. Requires the accessToken to be set
     * 
     * @param string $deviceID The device ID of the device to call the function on
     * @param string $variableName The name of the variable to retrieve
     *
     * @return boolean true if the call was successful, false otherwise. Use getResult to get the api result and use getError & getErrorSource to determine what happened in the event of an error
     */
    public function getVariable($deviceID, $variableName)
    {
        $url = $this->getUrl(['devices', $deviceID, $variableName]);

        $result = $this->_curlRequest($url, [], 'get');

        return $result;
    }

    /**
     * Lists all your cores assigned to your cloud account. Requires the accessToken to be set
     *
     * @return boolean true if the call was successful, false otherwise. Use getResult to get the api result and use getError & getErrorSource to determine what happened in the event of an error
     */
    public function listDevices()
    {
        $url = $this->getUrl(['devices']) . '/';

        $result = $this->_curlRequest($url, [], 'get');

        return $result;
    }

    /**
     * Gets your details from your core e.g. function and variables. Requires the accessToken to be set
     *
     * @param string $deviceID The device ID of the device
     *
     * @return boolean true if the call was successful, false otherwise. Use getResult to get the api result and use getError & getErrorSource to determine what happened in the event of an error
     */
    public function getAttributes($deviceID)
    {
        $url = $this->getUrl(['devices', $deviceID]);

        $result = $this->_curlRequest($url, [], 'get');

        return $result;
    }

    /**
     * Set the name/renames your core. Requires the accessToken to be set
     *
     * @param string $deviceID The device ID of the device to rename
     * @param string $name The new name of the device
     *
     * @return boolean true if the call was successful, false otherwise. Use getResult to get the api result and use getError & getErrorSource to determine what happened in the event of an error
     */
    public function renameDevice($deviceID,$name)
    {
        $url = $this->getUrl(['devices', $deviceID]);

        $result = $this->_curlRequest($url, ['name' => $name], 'put');

        return $result;
    }

    /**
     * Attempts to add a device to your cloud account. Requires the accessToken to be set.
     * Note, you may want to follow this up with a call to "setName" as new Core's names are blank.
     * Interestingly, if claiming an order core their name is retained across the unclaim/claim process
     *
     * @param string $deviceID The device ID of the device to claim. 
     * @param boolean $requestTransfer true to request the already-claimed device be transfered to your account. If false will try to claim but not automatically send a transfer request.
     *
     * @return boolean true if the call was successful, false otherwise. Use getResult to get the api result and use getError & getErrorSource to determine what happened in the event of an error
     */

    public function claimDevice($deviceID, $requestTransfer = false)
    {
        $url = $this->getUrl(['devices']);

        if ($requestTransfer) {
            $result = $this->_curlRequest($url, ['id' => $deviceID, 'request_transfer' => 'true'], 'post');
        } else {
            $result = $this->_curlRequest($url, ['id' => $deviceID, 'request_transfer' => 'false'], 'post');
        }

        return $result;
    }

    /**
     * Removes the core from your cloud account. Requires the accessToken to be set
     *
     * @param string $deviceID The device ID of the device to remove from your account. 
     *
     * @return boolean true if the call was successful, false otherwise. Use getResult to get the api result and use getError & getErrorSource to determine what happened in the event of an error
     */
    public function removeDevice($deviceID)
    {
        $url = $this->getUrl(['devices', $deviceID]) . '/';

        $result = $this->_curlRequest($url, [], 'delete');

        return $result;
    }

    /**
     * Uploads a sketch to the core. Requires the accessToken to be set
     *
     * @param string $deviceID The device ID of the device to upload the code to
     * @param string $filename The filename of the firmware file to upload to the device. Ex: tinker.cpp. Not yet implemented
     * @param string $filepath The path to the firmware file to upload (including the name). Ex: path/to/tinker.cpp
     * @param boolean $isBinary Set to true if uploading a .bin file or false otherwise.
     *
     * @return boolean true if the call was successful, false otherwise. Use getResult to get the api result and use getError & getErrorSource to determine what happened in the event of an error
     */
    public function uploadFirmware($deviceID, $filename, $filepath, $isBinary = false)
    {
        // Create a CURLFile object
        $cfile = new CURLFile($filepath, 'application/octet-stream', $filename);

        $url = $this->getUrl(['devices', $deviceID]);

        $params = ['file' => $cfile];

        if ($isBinary === true) {
            $params['file_type'] = 'binary';
        }

        $result = $this->_curlRequest($url, $params, 'put-file');

        return $result; 
    }

    /**
     * Gets a list of your tokens from the particle cloud. Requires the email/password auth to be set
     *
     * @return boolean true if the call was successful, false otherwise. Use getResult to get the api result and use getError & getErrorSource to determine what happened in the event of an error
     */
    public function listAccessTokens()
    {
        $url = $this->getUrl(['access_tokens']);

        $result = $this->_curlRequest($url, [], 'get', 'basic');

        return $result;
    }

    /**
     * Creates a new token on the particle cloud. Requires the email/password auth to be set
     *
     * @param int $expires_in When the token should expire (in seconds). Set to false to ignore and use the default. Set to 0 for a token that never expires
     * @param string $expires_at When the token should expire (at a date/time). Set to false to ignore and use the default. Set to 'null' for a token that never expires. Otherwise this should be a ISO8601 style date string
     * @param string $clientID The clientID. If you don't have one of these (only used in OAuth applications) set to false
     * @param string $clientSecret The clientSecret. If you don't have one of these (only used in OAuth applications) set to false
     *
     * @return boolean true if the call was successful, false otherwise. Use getResult to get the api result and use getError & getErrorSource to determine what happened in the event of an error
     */

    public function newAccessToken($expires_in = false, $expires_at = false, $clientID = null, $clientSecret = null)
    {
        $fields = [
            'grant_type' => 'password',
            'username' => $this->auth_email,
            'password' => $this->auth_password,
        ];

        if ($expires_in !== false) {
            $fields['expires_in'] = intval($expires_in);
        }

        if ($expires_at !== false) {
            $fields['expires_at'] = $expires_at;
        }

        if ($clientID && $clientSecret) {
            $fields['client_id'] = $clientID;
            $fields['client_secret'] = $clientSecret;
        }

        // This URL does not have the API version number in the path.
        $url = $this->endpoint . 'oauth/token';

        $result = $this->_curlRequest($url, $fields, 'post', 'basic-dummy');

        return $result;
    }

    /**
     * Removes the token from the particle cloud. Requires the email/password auth to be set
     *
     * @param string $token The access token to remove
     *
     * @return boolean true if the call was successful, false otherwise. Use getResult to get the api result and use getError & getErrorSource to determine what happened in the event of an error
     */
    public function deleteAccessToken($token)
    {
        $url = $this->getUrl(['access_tokens', $token]);

        $result = $this->_curlRequest($url, [], 'delete', 'basic');

        return $result;
    }

    /**
     * Gets a list of webhooks from the particle cloud. Requires the accessToken to be set
     *
     * @return boolean true if the call was successful, false otherwise. Use getResult to get the api result and use getError & getErrorSource to determine what happened in the event of an error
     */
    public function listWebhooks()
    {
        $fields = [];
        $url = $this->getUrl(['webhooks']);

        $result = $this->_curlRequest($url, $fields, 'get');

        return $result;
    }

    /**
     * Creates a new webhook on the particle cloud. Requires the accessToken to be set
     * @param string $event The event name used to trigger the webhook
     * @param string $webhookUrl The url to query once the event has occured
     * @param string $extras See http://docs.particle.io/webhooks/#webhook-options
     *
     * @return boolean true if the call was successful, false otherwise. Use getResult to get the api result and use getError & getErrorSource to determine what happened in the event of an error
     */
    public function newWebhook($event, $webhookUrl, array $extras = [])
    {
        $url = $this->getUrl(['webhooks']) . '/';

        $fields = array_merge(['event' => $event, 'url' => $webhookUrl], $extras);

        $result = $this->_curlRequest($url, $fields , 'post');

        return $result;
    }

    /**
     * Delete webhooks from the particle cloud. Requires the accessToken to be set
     *
     * @return boolean true if the call was successful, false otherwise. Use getResult to get the api result and use getError & getErrorSource to determine what happened in the event of an error
     */
    public function deleteWebhook($webhookID)
    {
        $fields = [];

        $url = $this->getUrl(['webhooks', $webhookID]) . '/';

        $result = $this->_curlRequest($url, $fields, 'delete');

        return $result;
    }

    /**
     * Sets the particle core signal mode state. Requires the accessToken to be set
     *
     * @param string $deviceID The device ID of the device to send the signal mode state change command to.
     * @param int $signalState The signal state: 0 returns the RGB led back to normmal & 1 makes it flash a rainbow of color
     *
     * @return boolean true if the call was successful, false otherwise. Use getResult to get the api result and use getError & getErrorSource to determine what happened in the event of an error
     */
    public function signalDevice($deviceID, $signalState = 0)
    {
        $fields = ['signal' => $signalState];

        $url = $this->getUrl(['devices', $deviceID]) . '/';

        $result = $this->_curlRequest($url, $fields, 'put');

        return $result;
    }

    /**
     * Returns the latest error
     *
     * @return string The latest error
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * Returns the latest error's source (which function cause the error)
     *
     * @return string The latest error's source
     */
    public function getErrorSource()
    {
        return $this->_errorSource;
    }

    /**
     * Returns the latest result
     *
     * @return string The latest result from calling a cloud function
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Returns the URL for the API, with additional path components.
     * TODO: support single array and varargs.
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
     * Performs a CURL Request with the given parameters
     *
     * @param string url The url to call
     * @param mixed[] params An array of parameters to pass to the url
     * @param string type The type of request ("GET", "POST", "PUT", etc)
     * @param string authType The type of authorization to use ('none' uses the access token, 'basic' uses basic auth with the email/password auth details, and 'basic-dummy' uses dummy basic auth details)
     *
     * @return boolean true on success, false on failure
     */
    protected function _curlRequest($url, $params = null, $type = 'post', $authType = 'none')
    {
        $fields_string = null;

        if ($authType == 'none') {
            if ($this->accessToken) {
               $params['access_token'] = $this->accessToken;
            } else {
                $errorText = 'No access token set';
                list(, $caller) = debug_backtrace(false);
                $this->_setError($errorText, $caller['function']);
                return false;
            }
        }

        // Is cURL installed?
        if ( ! function_exists('curl_init')) {
            throw new Exception('CURL is not installed or available');
        }

        // OK cool - then let's create a new cURL resource handle
        $ch = curl_init();

        // Set the number of POST vars, POST data
        switch ($type) {
            case 'get':
                $url .= ('?' . http_build_query($params));
                break;
            case 'post':
                curl_setopt($ch, CURLOPT_POST, count($params));
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                break;
            case 'put':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                break;
            case 'put-file':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                unset($params['access_token']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                // FIXME: what if the URL already has GET parameters?
                $url .= '?access_token=' . rawurlencode($this->accessToken);
                break;
            case 'delete':
                $url .= ('?' . http_build_query($params));
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            default:
                throw new Exception(sprintf('Unsupported method type (%s)', $type));
        }

        $this->_debug('Opening a {type} connection to {url}', false, ['type' => $type, 'url' => $url]);
        curl_setopt($ch, CURLOPT_URL, $url);

        if ($this->disableSSL) {
            // stop the verification of certificate
            $this->_debug('[WARN] Disabling SSL Verification for CURL');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        // Set a referer
        // curl_setopt($ch, CURLOPT_REFERER, "http://www.example.com/curl.htm");

        // User agent
        // curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");

        // Include header in result? (0 = yes, 1 = no)
        curl_setopt($ch, CURLOPT_HEADER, 0);

        // Should cURL return or print out the data? (true = return, false = print)
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->curlTimeout);

        $this->_debug('Auth Type: {type}', false, ['type' => $authType]);

        // basic auth
        if ($authType === 'basic') {
            if ($this->auth_email && $this->auth_password) {
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, $this->auth_email . ':' . $this->auth_password);
            } else {
                throw new Exception('No auth credentials (email/password) set');
            }
        }

        if ($authType === 'basic-dummy') {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, 'particle:particle');
        }

        // Download the given URL, and return output
        $this->_debug('Executing Curl Operation');
        $this->_debug('Url: {url}', false, ['url' => $url]);
        $this->_debug('Params', false, $params);
        $output = curl_exec($ch);

        $this->_debug(sprintf('Curl Result: {result}', false, ['result' => $output]));

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->_debug('Curl Response Code: {code}', false, ['code' => $httpCode]);

        // Close the cURL resource, and free system resources

        $curlError = curl_errno($ch);
        curl_close($ch);

        if ($curlError != CURLE_OK) {
            $this->_debug('CURL Request - There was a CURL error');
            list(, $caller) = debug_backtrace(false);

            $errorText = $this->_curlErrorCode($curlError);
            $this->_setError($errorText, $caller['function']);
            return false;
        } else {
            $retVal = json_decode($output, true);

            if (json_last_error() == 0) {
                if (isset($retVal['error']) && $retVal['error']) {
                    $this->_debug('CURL Request - API response contained \'error\' field');
                    $errorText = $retVal['error'];
                    $this->_setError($errorText, __FUNCTION__);
                    return false;
                } else {
                    $this->_debug('CURL Request - Returning True');
                    $this->result = $retVal;
                    return true;
                }
            } else {
                $this->_debug('CURL Request - Unable to parse JSON');
                $errorText = sprintf(
                    'Unable to parse JSON. Json error = %s. See %s for more information. Raw response from Spark Cloud = \'%s\'',
                    json_last_error(),
                    'http://php.net/manual/en/function.json-last-error.php',
                    $result
                );

                $this->_setError($errorText, __FUNCTION__);
                return false;
            }
        }
    }

    /**
     * Returns a human readable string for a given CURL Error Code
     *
     * @param int curlCode The CURL error code
     *
     * @return string A human-readable string version of the curlCode
     */
    protected function _curlErrorCode($curlCode)
    {
        switch ($curlCode) {
            case 26:
                return 'Curl Error. There was a problem reading a local file or an error returned by the read callback.';
            case 30:
                return 'Curl Error. Operation timeout. The specified time-out period was reached according to the conditions.';
            default:
                return "Curl Error. Error number = {$curlCode}. See http://curl.haxx.se/libcurl/c/libcurl-errors.html for more information";
        }
    }
}
