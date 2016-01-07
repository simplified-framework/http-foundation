<?php
/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 03.01.2016
 * Time: 18:02
 */

namespace Simplified\Http;


use Psr\Http\Message\StreamInterface;

class FileStream implements StreamInterface {
    private $handle;
    private $file;

    public function __construct($file) {
        if (file_exists($file)) {
            $this->file = $file;
        }
    }

    public function open($mode = "r") {
        if (file_exists($this->file)) {
            $this->handle = fopen($this->file, $mode);
            $this->rewind();
        }

        return $this;
    }

    public function close() {
        if ($this->handle) {
            fclose($this->handle);
            $this->handle = null;
        }
    }

    public function detach() {
        if ($this->handle) {
            fclose($this->handle);
            $this->handle = null;
        }
    }

    public function getSize() {
        if (!file_exists($this->file))
            return 0;

        return filesize($this->file);
    }

    public function tell() {
        if ($this->handle)
            return ftell($this->handle);
        return 0;
    }

    public function eof() {
        if ($this->handle)
            return feof($this->handle);
        return false;
    }

    public function isSeekable() {
        return true;
    }

    public function seek($offset, $whence = SEEK_SET) {
        if ($this->handle)
            return fseek($this->handle, $offset, $whence);
        return false;
    }

    public function rewind() {
        return $this->seek(0);
    }

    public function isWritable() {
        return is_writable($this->file);
    }

    public function write($string) {
        if ($this->handle)
            return fwrite($this->handle, $string, strlen($string));
    }

    public function isReadable() {
        return is_readable($this->file);
    }

    public function read($length) {
        if ($this->handle) {
            return fread($this->handle, $length);
        }
        return null;
    }

    public function getContents() {
        if (file_exists($this->file))
            return file_get_contents($this->file);
        return null;
    }

    public function getMetadata($key = null) {
        if ($this->handle) {
            $meta = stream_get_meta_data($this->handle);
            if ($key == 0)
                return $meta;
            return isset($meta[$key]) ? $meta[$key] : null;
        }
        return $key == null ? null : array();
    }

    public function __toString() {
        return $this->getContents();
    }
}