<?php
/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 28.12.2015
 * Time: 19:07
 */

namespace Simplified\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Simplified\Core\Collection;

class Request implements RequestInterface {
    private $method;
    private $uri;
    private $querystring;
    private $segments;
    private $clientAddress;
    private $isajax;
    private $headers;
    private $protocolVersion;
    private $body;
    private $files;
    private static $data;
    private static $instance;

    public static function createFromGlobals() {
        if (self::$instance == null)
            self::$instance = new self();

        return self::$instance;
    }

    public static function input($key, $default = null) {
        if (isset(self::$data[$key]))
            return self::$data[$key];

        return $default;
    }

    public function getProtocolVersion() {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version) {
        $this->protocolVersion = $version;
        return $this;
    }

    public function isSecure() {
        $isSecure = false;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $isSecure = true;
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) &&
            $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
        {
            $isSecure = true;
        }
        return $isSecure;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function hasHeader($name) {
        return $this->headers->has(name);
    }

    public function getHeader($name) {
        return $this->hasHeader($name) ? $this->headers[$name] : null;
    }

    public function getHeaderLine($name) {
    }

    public function withHeader($name, $value) {
        $this->headers[$name] = $value;
        return $this;
    }

    public function withAddedHeader($name, $value) {
        $this->headers[$name] = $value;
        return $this;
    }

    public function withoutHeader($name) {
        if ($this->hasHeader($name))
            unset($this->headers[$name]);
        return $this;
    }

    public function getBody() {
        return $this->body;
    }

    public function withBody(StreamInterface $body) {
        $this->body = $body;
    }

    public function getRequestTarget() {
        return $this->uri->getPath();
    }

    public function withRequestTarget($requestTarget) {
        $this->uri = Uri::fromString($requestTarget);
        return $this;
    }

    public function getMethod() {
        return $this->method;
    }

    public function withMethod($method) {
        $this->method = $method;
        return $this;
    }

    public function getUri() {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false) {
        $this->uri = $uri;
        return $this;
    }

    public function isAjax() {
        return $this->isajax;
    }

    public function getUploadedFiles() {
        $files = array();
        foreach ($this->files as $file) {
            $files[] = $file;
        }
        return $files;
    }

    public function getUploadedFile($fieldname) {
        return isset($this->files[$fieldname]) ? $this->files[$fieldname] : null;
    }

    private function __construct() {
        $domain   = $_SERVER['HTTP_HOST'];
        $port = $_SERVER['SERVER_PORT'];
        $protocol = $this->isSecure() ? "https" : "http";
        $uri = $protocol . "://". $domain . (intval($port) != 80 ? ":$port" : "") . $_SERVER['REQUEST_URI'];
        $this->uri = Uri::fromString($uri);

        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->querystring = $_SERVER['QUERY_STRING'];
        $this->clientAddress = $_SERVER['REMOTE_ADDR'];
        $this->protocolVersion = str_replace("HTTP/","",$_SERVER['SERVER_PROTOCOL']);

        $segments_data = explode("/", substr(isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI'], 1));
        if ($segments_data == null || !is_array($segments_data))
            $segments_data = array();
        $this->segments = $segments_data;

        $this->headers = new Collection(getallheaders());

        $isAjax = false;
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $isAjax = true;
        }
        $this->isajax = $isAjax;

        $data = array();
        $data = array_merge($data, $_GET);
        $data = array_merge($data, $_POST);
        self::$data = $data;

        $files = array();
        if (is_array($_FILES)) {
            foreach (array_keys($_FILES) as $key) {
                if (is_array($_FILES[$key]['tmp_name'])) {
                    $count = count($_FILES[$key]['tmp_name']);
                    $files[$key] = array();
                    for($i = 0; $i < $count; $i++) {
                        $files[$key][] = new UploadedFile(
                            array(
                                'fieldname' => $key,
                                'tmp_filename' => $_FILES[$key]['tmp_name'][$i],
                                'origin_filename' => $_FILES[$key]['name'][$i],
                                'file_type' => $_FILES[$key]['type'][$i],
                                'upload_error' => $_FILES[$key]['error'][$i]
                            )
                        );
                    }
                } else {
                    $files[$key] = new UploadedFile(
                        array(
                            'fieldname' => $key,
                            'tmp_filename' => $_FILES[$key]['tmp_name'],
                            'origin_filename' => $_FILES[$key]['name'],
                            'file_type' => $_FILES[$key]['type'],
                            'upload_error' => $_FILES[$key]['error']
                        )
                    );
                }
            }
        }
        $this->files = $files;
    }
}