<?php
/**
 * @author: Jackong
 * Date: 14-9-22
 * Time: 下午5:44
 */

namespace src\common;

class LogWriter {
    private $file = null;
    public function __construct() {
        $this->file = fopen('/tmp/' . DATE . '.log', 'a');
    }

    public function __destruct() {
        fclose($this->file);
    }

    public function write($message) {
        if (!is_string($message)) {
            $message = json_encode($message);
        }
        fwrite($this->file, $message);
    }
} 
