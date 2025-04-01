<?php
class ServerError {
    public static function throwError ($code, $reason) {
        http_response_code($code);
        die("HTTP Error " . $code . ": " . $reason);
    }
}
?>