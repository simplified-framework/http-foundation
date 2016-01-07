<?php
/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 03.01.2016
 * Time: 15:55
 */

namespace Simplified\Http;

use Psr\Http\Message\UploadedFileInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class UploadedFile implements UploadedFileInterface {
    private $data;
    public function __construct($filedata) {
        $this->data = $filedata;
    }

    public function getStream() {
        if (file_exists($this->getTempFilename())) {
            $fs = new FileStream($this->getTempFilename());
            return $fs;
        }
        return null;
    }

    public function moveTo($targetPath) {
        if (!file_exists($this->getTempFilename()))
            throw new FileNotFoundException('Unable to move uploaded file again.');

        move_uploaded_file($this->getTempFilename(), $targetPath);
    }

    public function copyTo($targetPath) {
        if (!file_exists($this->getTempFilename()))
            throw new FileNotFoundException('File doesn\'t exists');

        $fs = new Filesystem();
        return $fs->copy($this->getTempFilename(), $targetPath);
    }

    public function getSize() {
        if (!file_exists($this->getTempFilename()))
            throw new FileNotFoundException('File doesn\'t exists');

        return filesize($this->getTempFilename());
    }

    public function getError() {
        if (!file_exists($this->getTempFilename()))
            throw new FileNotFoundException('File doesn\'t exists');

        return $this->data['upload_error'];
    }

    public function getTempFilename() {
        if (!file_exists($this->data['tmp_filename']))
            throw new FileNotFoundException('File doesn\'t exists');

        return $this->data['tmp_filename'];
    }

    public function getClientFilename() {
        if (!file_exists($this->getTempFilename()))
            throw new FileNotFoundException('File doesn\'t exists');

        return $this->data['origin_filename'];
    }

    public function getClientMediaType() {
        if (!file_exists($this->getTempFilename()))
            throw new FileNotFoundException('File doesn\'t exists');

        if (!empty($this->data['file_type']))
            return $this->data['file_type'];

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            return finfo_file($finfo, $this->getTempFilename());
        }
    }
}