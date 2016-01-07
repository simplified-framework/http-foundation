<?php
/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 28.12.2015
 * Time: 19:13
 */

namespace Simplified\Http;
use Psr\Http\Message\UriInterface;

class Uri implements UriInterface {
    private $data;
    private $path;
    private $fragment;
    private $scheme;
    private $host;
    private $port;
    private $user;
    private $pass;
    private $query;

    public static function fromString($str) {
        return new self($str);
    }

    public function getScheme() {
        return $this->scheme;
    }

    public function getAuthority() {
    }

    public function getUserInfo() {
        $user = $this->user;
        if (!empty($user) && !empty($this->pass))
            $user .= ":" . $this->pass;

        return $user;
    }

    public function getHost() {
        return $this->host;
    }

    public function getPort() {
        return $this->host;
    }

    public function getPath() {
        return $this->path;
    }

    public function getQuery() {
        return $this->query;
    }

    public function getFragment() {
        return $this->fragment;
    }

    public function withScheme($scheme) {
        $this->scheme = $scheme;
        return $this;
    }

    public function withUserInfo($user, $password = null) {
        $this->user = $user;
        $this->pass = $password;
        return $this;
    }

    public function withHost($host) {
        $this->host = $host;
        return $this;
    }

    public function withPort($port) {
        $this->port = intval($port);
        return $this;
    }

    public function withPath($path) {
        $this->path = $path;
        return $this;
    }

    public function withQuery($query) {
        $this->query = $query;
        return $this;
    }

    public function withFragment($fragment) {
        $this->fragment = $fragment;
        return $this;
    }

    public function __toString() {
        $url = $this->getScheme() . "://" . $this->getHost();
        if ($this->getUserInfo())
            $url .= "@" . $this->getUserInfo();

        $url .= $this->getPath();

        if ($this->getQuery())
            $url .= "?" . $this->getQuery();
        if ($this->getFragment())
            $url .= "#" . $this->getFragment();

        return $url;
    }

    private function __construct($str) {
        $this->data   = parse_url($str);
        $this->scheme = isset($this->data['scheme']) ? $this->data['scheme'] : "";
        $this->host = isset($this->data['host']) ? $this->data['host'] : "";
        $this->port = isset($this->data['port']) ? intval($this->data['port']) : 80;
        $this->user = isset($this->data['user']) ? $this->data['user'] : "";
        $this->pass = isset($this->data['pass']) ? $this->data['pass'] : "";
        $this->path = isset($this->data['path']) ? urldecode($this->data['path']) : "/";
        $this->query    = isset($this->data['query']) ? $this->data['query'] : "";
        $this->fragment = isset($this->data['fragment']) ? $this->data['fragment'] : "";
    }
}