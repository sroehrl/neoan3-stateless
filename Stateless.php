<?php
/* neoan3 Stateless app
*
 */

namespace Neoan3\Apps;



use Exception;

/**
 * Class Stateless
 * @package Neoan3\Apps
 */
class Stateless
{

    /**
     * @var string|null
     */
    private static ?string $_secret = null;

    private static ?int $_expirationTime = null;
    /**
     * @var string|null
     */
    private static ?string $exception = null;

    private static ?string $_jwt = null;

    static function setAuthorization($jwt): void
    {
        self::$_jwt = $jwt;
    }

    /**
     * @throws Exception
     */
    static function getAuthorization(): ?string
    {
        if(self::$_jwt) {
            return self::$_jwt;
        }
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            self::throwRestricted(401);
        }
        $auth = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);

        if (count($auth) !== 2) {
            self::throwRestricted(401);
        }
        return $auth[1];
    }

    /**
     * @param ?string $exception
     */
    static function setCustomException(?string $exception): void
    {
        self::$exception = $exception;
    }

    static function setExpiration(int|string|null $expirationTime): void
    {
        self::$_expirationTime = $expirationTime;
    }

    /**
     * @param $secret
     */
    static function setSecret($secret): void
    {
        self::$_secret = $secret;
    }


    /**
     * @return mixed|string
     * @throws Exception
     */
    static function validate(): mixed
    {
        self::isKeySet();

        $decoded = Jwt::decode(self::getAuthorization(), self::$_secret);

        if ($decoded['error']) {
            self::throwRestricted(401);
        }
        return $decoded['decoded'];
    }

    /**
     * Restricts access and return (if valid) the decoded Jwt
     *
     * @param mixed $scope
     *
     * @return mixed
     *
     * @throws Exception
     */
    static function restrict($scope = false): mixed
    {
        self::isKeySet();
        $decoded = self::validate();


        if ($scope && isset($decoded['scope'])) {
            if (is_string($scope)) {
                $scope = [$scope];
            }

            if (!self::permissionCheck($scope, $decoded)) {
                self::throwRestricted(403);
            }
        }
        return $decoded;
    }

    static function permissionCheck($scope, $decrypted): bool
    {
        $allowed = false;
        foreach ($scope as $access) {
            if (in_array($access, $decrypted['scope'])) {
                $allowed = true;
            }
        }
        return $allowed;
    }

    /**
     * @param       $identifier
     * @param       $scope
     * @param array $payload
     *
     * @return string
     * @throws Exception
     */
    static function assign($identifier, $scope, array $payload = []): string
    {
        self::isKeySet();
        if(self::$_expirationTime){
            Jwt::expiresAt(self::$_expirationTime);
        }
        Jwt::identifier($identifier);
        $scope = is_string($scope) ? [$scope] : $scope;
        $payload['scope'] = $scope;
        Jwt::payLoad($payload);
        return Jwt::encode(self::$_secret);
    }


    /**
     * @param $code
     * @param string $msg
     * @throws Exception
     */
    private static function throwRestricted($code, string $msg = 'access denied')
    {
        if ($code == 401) {
            $msg = 'unauthorized';
        }
        if(self::$exception){
            throw new self::$exception($msg, $code);
        } else {
            throw new Exception($msg, $code);
        }

    }


    /**
     * @throws Exception
     */
    private static function isKeySet(): void
    {
        if (!self::$_secret) {
            self::throwRestricted(500, 'Setup: no secret key defined for Neoan3\Apps\Stateless');
        }
    }

}
