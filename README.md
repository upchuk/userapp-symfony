With this library you can use [UserApp.io](https://www.userapp.io/) to manage your users that can authenticate
with your Symfony application. For more information about Symfony security, check the [docs](http://symfony.com/doc/current/book/security.html).

## Features

* All users stored in UserApp.io can login/logout but they must have at least one permission which preferably then needs to correlate with a Symfony role.
* User information (basic details, permissions and properties) are stored in the Symfony user session upon login to avoid calls to the API on each request. This means that this data is only refreshed in the Symfony application when the user logs back in.
* On each request, a heartbeat is sent to the API to prolong the life of the UserApp.io token. This also means that the Symfony session gets destroyed if the UserApp.io token has expired in the meantime -> the user is forced to log back in.
* If you lock a user in UserApp.io, the locked status of the Symfony user gets updated in the next request through the heartbeat (so the user object contains the also the locked status).

 
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

## Usage

Before using this package, make sure that your UserApp.io users have at least one permission enabled. These permissions are used as roles in Symfony and are cached in the user session.

Once the package is in your vendors folder, you'll need to create a few services (you can copy and paste from below in YML format):

```
	  user_app_client:
             class: UserApp\API
             arguments: ["%userapp_id%"]
      user_app_authenticator:
             class: UserAppSymfony\UserAppAuthenticator
             arguments: ["@user_app_client"]
      user_app_provider:
             class: UserAppSymfony\UserAppProvider
             arguments: ["@user_app_client"]
      user_app_logout:
             class: UserAppSymfony\UserAppLogout
             arguments: ["@user_app_client"]	
             
 ```

The first is the user app library exposed as a service to be injected in the various classes. You can use it to make your own calls as well if you like. As an argument, a parameter (`userapp_id`) is automatically passed to it so **make sure your application contains this parameter with the value of the your UserApp.io token** (replace token with your own):

```
userapp_id: 556712n864bb5
```

The rest are services used in the Symfony authentication process.

The only thing left is your security configuration (e.g. `security.yml`):

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

The `authenticator` key makes the specifies the authenticator class used within the `simple_form` type of authentication (more information [here](http://symfony.com/doc/current/cookbook/security/custom_password_authenticator.html) about this type of authentication).

The `handlers` key specifies the services that get instantiated and called when the user logs out (used in this case to log users out from the UserApp.io service as well).

The rest is Symfony demo setup of a custom firewall. 

## Development

I plan to extend this functionality as needed to incorporate more of what UserApp provides (subscriptions, payment plan information, social account integration, etc).

If you find any bugs, problems or things that can be improved, please open an issue and I'll take a look. 
