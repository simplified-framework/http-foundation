<?php
/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 27.12.2015
 * Time: 13:54
 */

namespace Simplified\Http;
use Simplified\Core\Collection;

class UrlMatcher {
    const MATCH_FOUND = 1;
    const METHOD_MISMATCH = 2;
    const UNKNOWN_RESOURCE = 3;

    private $routes;
    private $request;
    private $route;
    private $matches = array();

    public function __construct(Collection $routes, Request $request) {
        $this->routes  = $routes;
        $this->request = $request;
        $this->route   = null;
    }

    public function matches($url) {

        foreach ($this->routes as $route) {
            $route_path = $route->path;
            $required = true;
            if ($route_path === $url) {
                if ($route->method != $this->request->getMethod())
                    return UrlMatcher::METHOD_MISMATCH;

                preg_match('/'.str_replace('/', '\\/', $route_path).'/', $url, $matches);
                array_shift($matches); // remove first element
                $this->route = $route;
                $this->matches = $matches;
                break;
            }

            $matches = array();
            $pattern = str_replace("/", "\\/", $route_path);
            if ( count($route->conditions) > 0 ) {
                foreach ($route->conditions as $key => $val) {
                    if (strstr($pattern, '{'.$key.'?}')) {
                        $required = false;
                        $pattern = str_replace('{'.$key.'?}', "($val)", $pattern);
                    }
                    else
                        $pattern = str_replace("{".$key."}", "($val)", $pattern);
                }
            } else {
                $pattern = preg_replace('/\{[a-zA-Z-_\?{,1}]+\}/', '([a-zA-Z]+)', $pattern);
            }

            // compile pattern
            $match = preg_match('/^'.$pattern.'$/', $url, $matches);

            if (0 === $match || false == $match) {
                if ( count($route->conditions) == 1 && $required == false) {
                    $key = '{' . array_keys($route->conditions)[0] . '?}';
                    $pattern = str_replace($key, '', $route_path);
					$pattern = rtrim($pattern, '/');
                    $pattern = str_replace("/", "\\/", $pattern);
                    $match = preg_match('/^'.$pattern.'$/', $url, $matches);

                    if (!$match) {
                        continue;
                    } else {
                        array_shift($matches); // remove first element
                        $this->route = $route;
                        $this->matches = $matches;
                        break;
                    }
                } else {
                    continue;
                }
            }

            // current route can be translated to regex pattern
            if (count($matches) >= 2 && !empty($matches[1])) {
                if ($route->method != $this->request->getMethod())
                    return UrlMatcher::METHOD_MISMATCH;

                array_shift($matches); // remove first element
                $this->route = $route;
                $this->matches = $matches;
                break;
            }
        }

        if ($this->route != null)
            return UrlMatcher::MATCH_FOUND;

        return UrlMatcher::UNKNOWN_RESOURCE;
    }

    public function getMatchedRoute() {
        return $this->route;
    }

    public function getMatches() {
        return $this->matches;
    }
}
