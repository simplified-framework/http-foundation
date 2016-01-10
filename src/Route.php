<?php

namespace Simplified\Http;
use Simplified\Core\IllegalArgumentException;

class Route {
    private $conditions;
    private $name;
    private $path;
    private $method;
    private $controller;
    private $closure;

    public function __get($key) {
        return $this->$key;
    }

    public static function get($uri, $arg2) {
        return self::registerRoute('get', $uri, $arg2);
    }

    public static function put($uri, $arg2) {
        return self::registerRoute('put', $uri, $arg2);
    }

    public static function post($uri, $arg2) {
        return self::registerRoute('post', $uri, $arg2);
    }

    public static function delete($uri, $arg2) {
        return self::registerRoute('delete', $uri, $arg2);
    }

    public function conditions() {
        if (func_num_args() == 1)
            $condition = func_get_arg(0);
        else
            $condition[func_get_arg(0)] = func_get_arg(1);

        $this->conditions = $condition;
    }

    public function getConditions() {
        return $this->conditions;
    }

    public static function getCollection() {
        RouteCollection::instance();
    }

    private static function registerRoute($type, $uri, $arg2) {
        $controller = null;
        $closure = null;
        $name = md5(microtime());

        if (is_array($arg2)) {
            if (isset($arg2['uses'])) {
                $controller = $arg2['uses'];
            }

            if (isset($arg2['as'])) {
                $name = $arg2['as'];
            }
        }
        else
            if (is_string($arg2)) {
                $controller = $arg2;
            }
            else
                if (gettype($arg2) == 'object' && $arg2 instanceof \Closure) {
                    $closure = $arg2;
                }

        if ($controller == null && !$arg2 instanceof \Closure)
            throw new IllegalArgumentException("Unable to set controller for route $uri.");

        if (!strstr($controller, "@") && !$arg2 instanceof \Closure)
            throw new IllegalArgumentException("Unable to set controller for route $uri: no controller method set.");

        $instance = new self();
        $instance->conditions = array();
        $instance->name = $name;
        $instance->path = $uri;
        $instance->method = strtoupper($type);
        $instance->controller = $controller;
        $instance->closure = $closure;

        $collection = RouteCollection::instance();
        $collection->add($name, $instance);

        return $instance;
    }
}