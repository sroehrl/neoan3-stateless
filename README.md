# neoan3 stateless JWT authentication app

## Installation 

`composer require neoan3-apps/stateless`

### Usage (in neoan3)

_Initialization_

```PHP
// you can place this in the construct of your frame or in the contruct of your API-component for convinience
Neoan3\Apps\Stateless::setSecret('My-super-secure-Key');

```

_API-login-endpoint_

```PHP
...

function postLogin($credentials){
  ... // verify credentials (and get roles?)

  $roles = ['user','administrator'];

  return ['token'=>Neoan3\Apps\Stateless::assign($userId,$roles)];

}

```

_Restricted API-endpoint_

```PHP
...

function deleteUser($user){
  $jwt = Neoan3\Apps\Stateless::restrict('administrator');
  ... // unless you catch a RouteException, this code only executes if authorized as administrator
  
  $loggedInUserId = $jwt['jti'];

}

```
