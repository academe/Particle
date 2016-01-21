Particle
========

PHP package for interacting with the [Particle Cloud](https://particle.io/),
and its cloud-connected devices ([Photon](https://store.particle.io/collections/photon), P0, P1, Electron)

## TODO

Work in progress, but main tasks are:

* [ ] Create tests.
* [x] ~~Remove old Spark code.~~
* [x] ~~Replace examples.~~
* [ ] Maybe remove the debug logging, given we have enough injected points to set debug.
* [ ] Full coverage of the API. Authentication as an application plus orgs are the main areas left.

## Quick Installation

Using composer:

    composer.phar require academe/particle

Add dependencies for the demo:

    composer.phar require guzzlehttp/guzzle

## Example Class

All the examples have been put into the `Academe\Particle\Example` class.
Please look into this class to see how the API works.

In summary, this package generates PSR-7 messages, which are sent using any suitable
HTTP client.

At the moment there is no interpretation of the results - you need to get the
results you need from the PSR-7 response, the format of which is documented
in the [Particle API documentation](https://docs.particle.io/reference/api/).

You can be run the examples like this:

~~~php
use Academe\Particle\Example;

$accessToken = 'your-account-access-token';
$deviceId = 'your-device-id';
$email = 'your-cloud-email-username';
$password = 'your-cloud-password';

// Instantiate the example class.
$example = new Example($access_token, $device, $email, $password);

// Flash the device with the Tinker application.
$response = $example->flashTinker();
//$response = $example->setDeviceName();
//$response = $example->setDeviceName('christmas_turkey');
//$response = $example->listDevices();
//$response = $example->getDevice();
//$response = $example->getDevice('20033307343c03805403a138');
//$response = $example->newAccessToken();
//$response = $example->listTokens();
//$response = $example->deleteAccessToken('176a67f0d31647beac429252af8663a5040a945c');
//$response = $example->callFunction();
//$response = $example->listOranizations();
//$response = $example->removeMember('my_organization', 'someone@example.com');

echo "Status=" . $response->getStatusCode() . "\n";
echo "Reason=" . $response->getReasonPhrase() . "\n";
echo "Detail=" . $response->getBody() . "\n";
~~~

Don't forget to install Guzzle (v6) to run these examples:

    composer require guzzlehttp/guzzle

Most of the other examples in this class require the Tinker application to be
installed as the first step.

## Implemented Features

TODO: this list is not complete. Some additional features are implemented, and the
API has further new features that are not yet implemented.

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
