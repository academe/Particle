[![Build Status](https://travis-ci.org/articfox1986/phpParticle.svg?branch=master)](https://travis-ci.org/articfox1986/phpParticle)

phpParticle
========

PHP Class for interacting with the Particle Cloud (particle.io)

## Installation

This is a development only package at the moment.

First check out a clone:

    git clone git@github.com:academe/phpParticle.git

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
            "articfox1986\\phpparticle\\": "phpParticle/src"
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

use articfox1986\phpparticle\ParticleApi;
use articfox1986\phpparticle\Logger;
use articfox1986\phpparticle\GuzzleConnnector;

$logger = new Logger('HTML');

$device = 'device-id';
$access_token = 'access-token';
$cloud_email = 'cloud-email';
$cloud_password = 'cloud-password';

$particle = new ParticleAPI(new GuzzleConnnector());

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



------

Ignore the below, for now.

## Installation ##

- GIT clone or download a zip of the repo and unzip into your project director
- Rename `phpSpark.config.sample.php` to `phpSpark.config.php`
- Set your access token and device id in `phpSpark.config.php`
- (Optional) Copy and paste the code in `spark.firmware.cpp` into a new app in the Particle WebIDE & flash it to your core
- (Optional) Run the any of the examples in the `examples` folder

## Usage

- Check out the examples in the `examples` folder
- Try out the [phpSparkDashboard](https://github.com/harrisonhjones/phpSparkDashboard) project which uses this project ([demo](http://projects.harrisonhjones.com/phpSparkDashboard/))

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