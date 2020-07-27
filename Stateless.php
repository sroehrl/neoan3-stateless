<?php
/* neoan3 Stateless app
*
 */

namespace Neoan3\Apps;

use Neoan3\Core\RouteException;


/**
 * Class Stateless
 * @package Neoan3\Apps
 */
class Stateless
{

    /**
     * @var null
     */
    private static $_secret = null;

    /**
     * @param $secret
     */
    static function setSecret($secret)
    {
        self::$_secret = $secret;
    }

    /**
     * @return mixed
     * @throws RouteException
     */
    static function validate()
    {
        self::isKeySet();
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            self::throwRestricted(401);
        }
        $auth = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);

        if (count($auth) !== 2) {
            self::throwRestricted(401);
        }
        $decoded = Jwt::decode($auth['1'], self::$_secret);

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
     * @throws RouteException
     */
    static function restrict($scope = false)
    {
        self::isKeySet();
        $decoded = self::validate();

        if ($scope && !isset($decoded['scope'])) {

            self::throwRestricted(403);
        }

        if ($scope) {
            if (is_string($scope)) {
                $scope = [$scope];
            }

            $allowed = false;
            foreach ($scope as $access) {
                if (in_array($access, $decoded['scope'])) {
                    $allowed = true;
                }
            }
            if (!$allowed) {
                self::throwRestricted(403);
            }
        }
        return $decoded;
    }

    /**
     * @param       $identifier
     * @param       $scope
     * @param array $payload
     *
     * @return string
     */
    static function assign($identifier, $scope, $payload = [])
    {
        self::isKeySet();
        Jwt::identifier($identifier);
        $scope = is_string($scope) ? [$scope] : $scope;
        $payload['scope'] = $scope;
        JWT::payLoad($payload);
        return Jwt::encode(self::$_secret);
    }

    /**
     * @param $code
     *
     * @throws RouteException
     */
    private static function throwRestricted($code)
    {
        $msg = 'access denied';
        if ($code == 401) {
            $msg = 'unauthorized';
        }
        throw new RouteException($msg, $code);
    }

    /**
     * Ensures setup
     */
    private static function isKeySet()
    {
        if (!self::$_secret) {
            print('Setup: no secret key defined for Neoan3\Apps\Stateless');
            die();
        }
    }

}
