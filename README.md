phpParticle
========

PHP Class for interacting with the Particle Cloud (particle.io)

## TODO

Work in progress, but main tasks are:

* Create tests.
* Remove old Spark code.
* Replace examples.
* Maybe remove the debug logging, given we have enough injected points to set debug.

## Quick Dev Installation

This is a development only package at the moment.

First check out a clone:

    git clone git@github.com:academe/Particle.git

In the same directory, create composer.json

~~~json
{
    "require": {
        "psr/log": "^1.0",
        "psr/http-message": "^1.0",
        "guzzlehttp/psr7": "^1.2",
        "guzzlehttp/guzzle": "^6.1"
    },
    "autoload": {
        "psr-4": {
            "academe\\particle\\": "Particle/src"
        }
    }
}
~~~

Then pull in the dependencies:

    composer.phar update

index.php demo:

~~~php
<?php

require "vendor/autoload.php";

use Academe\Particle\ParticleApi;
use Academe\Particle\Log\EchoLogger;
use Academe\Particle\Psr7\GuzzleConnnector;

$logger = new EchoLogger('HTML');

$device = 'device-id';
$access_token = 'access-token';
$cloud_email = 'cloud-email';
$cloud_password = 'cloud-password';

$particle = new ParticleApi(new GuzzleConnnector());

$particle = $particle
    ->setLogger($logger) // optional
    ->setAccessToken($access_token)
    ->setDebug(false) // or true
    ->setAuth($cloud_email, $cloud_password);

// Try any of these messages

//$request = $particle->callFunction($device, 'function', 'function-parameters', true);
//$request = $particle->listDevices();
//$request = $particle->getDevice($device);
//$request = $particle->renameDevice($device, 'a-new-name');
//$request = $particle->getVariable($device, 'variable-name', true);
//$request = $particle->listAccessTokens();
//$request = $particle->newAccessToken();
//$request = $particle->deleteAccessToken('token-id');
//$request = $particle->claimDevice($device, true);
//$request = $particle->listWebhooks();
//$request = $particle->newWebhook('event', 'http://example.com/', []);
//$request = $particle->deleteWebhook('webhook-id');
//$request = $particle->signalDevice($device);
//$request = $particle->uploadFirmware($device, 'tinker.cpp', 'phpParticle/examples/tinker.cpp', false);

// Send the message.
$client = new \GuzzleHttp\Client(['defaults' => ['timeout' => 2]]);
$response = $client->send($request);

// See the result
echo "<pre>";
echo "Status=" . $response->getStatusCode() . "\n";
echo "Reason=" . $response->getReasonPhrase() . "\n";
var_dump(json_decode($response->getBody()));
~~~

## Implemented Features

### Device Management
- List Devices
- Get device info 
- Rename/Set device name
- Call Particle Function on a device
- Grab the value of a Particle Variable from a device
- Remote (Over the Air) Firmware Uploads
- Device signaling (make it flash a rainbow of colors)

### Access Token Management
- Generate a new access token
- List your access tokens
- Delete an access token

### Webhook Management

- List Webhooks
- Add Webhook
- Delete Webhook

### Account/Cloud Management
- Use a local particle cloud
- Claim core or photon
- Remove core or photon

## Not Yet Implemented Features
- OAuth Client Creation (/v1/clients)
- Advanced OAuth topics
