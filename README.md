# Rocket Chat REST API client in PHP

Use this client if you need to connect to Rocket Chat with a software written
in PHP, such as WordPress or Drupal.

## How to use

This Rocket Chat client is installed via [Composer](http://getcomposer.org/). To install, simply add it
to your `composer.json` file:

```json
{
    "require": {
        "fab1en/rocket-chat-rest-client": "dev-master"
    }
}
```

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

Then, import the `autoload.php` from your `vendor` folder.

After this, you have to define some constants to point to your Rocket Chat instance

```php
define('REST_API_ROOT', '/api/v1/');
define('ROCKET_CHAT_INSTANCE', 'https://my-rocket-chat-instance.example.org');
```

Finally, instance the classes you need:
```php
$api = new \RocketChat\Client();
echo $api->version(); echo "\n";

// login as the main admin user
$admin = new \RocketChat\User('my-admin-name', 'my-admin-password');
if( $admin->login() ) {
	echo "admin user logged in\n";
};
$admin->info();
echo "I'm {$admin->nickname} ({$admin->id}) "; echo "\n";
```

## Manage user
```php
// create a new user
$newuser = new \RocketChat\User('new_user_name', 'new_user_password', array(
	'nickname' => 'New user nickname',
	'email' => 'newuser@example.org',
));
if( !$newuser->login(false) ) {
	// actually create the user if it does not exist yet
  $newuser->create();
}
echo "user {$newuser->nickname} created ({$newuser->id})\n";
```

## Post a message
```php
// create a new channel
$channel = new \RocketChat\Channel( 'my_new_channel', array($newuser, $admin) );
$channel->create();
// post a message
$channel->postMessage('Hello world');
```
## Credits
This REST client uses the excellent [Httpful](http://phphttpclient.com/) PHP library by [Nate Good](https://github.com/nategood) ([github repo is here](https://github.com/nategood/httpful)).

## WebHook
```
$WebHook= new \RocketChat\WebHook();

if ($WebHook->postData['user_name']!="rocket.cat")
	$WebHook->sendmessage("Echo: ".$WebHook->postData['text']);
```
