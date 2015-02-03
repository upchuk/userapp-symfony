With this library you can use [UserApp.io](https://www.userapp.io/) to manage your users that can authenticate
with your Symfony application. For more information about Symfony security, check the [docs](http://symfony.com/doc/current/book/security.html).

## Features

* All users stored in UserApp.io can login/logout but they must have at least one permission which preferably then needs to correlate with a Symfony role.
* User information (basic details, permissions and properties) are stored in the Symfony user session upon login to avoid calls to the API on each request. This means that this data is only refreshed in the Symfony application when the user logs back in.
* On each request, a heartbeat can be sent to the API to prolong the life of the UserApp.io token. This also means that the Symfony session gets destroyed if the UserApp.io token has expired in the meantime -> the user is forced to log back in.
* You can specify if the heartbeat request should be sent on every request or after a defined number of minutes have passed since the previous one.
* If you lock a user in UserApp.io, the locked status of the Symfony user gets updated in the next request through the heartbeat.

 
## Installation

You can install this library with composer:

```
{
  "require": {
    "upchuk/userapp-symfony": "dev-master"
  }
}
```
*(semver release tags will follow)*

Before using this package, make sure that your UserApp.io users have at least one permission enabled. These permissions are used as roles in Symfony and are cached in the user session.

Once the package is in your vendors folder, you'll need to import the `services.yml` file that comes with the library. Assuming that your own `services.yml` file is located in the default `app/config` folder, add this to it:

```
imports:
    - { resource: '../../vendor/upchuk/userapp-symfony/services.yml' }             
 ```
 
Make sure you adjust the path if your directory structure differs.

## Configuration

### Required

#### Security configuration

Edit the `security.yml` file and add the new user provider and firewall authentication provider:

**The user  provider:**

```
providers:
	user_app:
	   id: user_app_provider
```

**The firewall**

```
firewalls:
	demo_secured_area:
	    pattern: ^/secured/path
	    simple_form:
	        authenticator: user_app_authenticator
	        check_path: _demo_security_check
	        login_path: _demo_login
	    logout:
	        path:   _demo_logout
	        handlers: [user_app_logout]
	        target: _demo
```

The `authenticator` key specifies the authenticator class used within the `simple_form` type of authentication (more information [here](http://symfony.com/doc/current/cookbook/security/custom_password_authenticator.html) about this type of authentication).

The `handlers` key specifies the services that get instantiated and called when the user logs out (used in this case to log users out from the UserApp.io service as well).

The rest is Symfony demo setup of a custom firewall. Make sure you read the [documentation](http://symfony.com/doc/current/book/security.html) for more info.

#### App ID

You need to make sure you create a parameter inside your `parameters.yml` file called `userapp_id` that contains the App ID of your UserApp.io account

### Optional

You can create a parameter inside your `parameters.yml` file called `userapp_heartbeat_frequency` with which you can specify the number of minutes (in seconds) that need to pass after a heartbeat request for a new one to be made. Setting this to 0 will make it send a request on each authenticated page refresh. By default it is `2700` (45 minutes).

## Development

I plan to extend this functionality as needed to incorporate more of what UserApp provides (subscriptions, payment plan information, social account integration, etc).

If you find any bugs, problems or things that can be improved, please open an issue and I'll take a look. 
