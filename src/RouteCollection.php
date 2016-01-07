<?php
/**
 * Created by PhpStorm.
 * User: bratfisch
 * Date: 10.12.2015
 * Time: 13:07
 */

namespace Simplified\Http;


use Simplified\Core\Collection;

class RouteCollection extends Collection {
    private static $instance;

    public static function instance() {
        if (self::$instance == null)
            self::$instance = new parent();

        return self::$instance;
    }
}