<?php


namespace Imap\Components;

/**
 * Class AjaxResponse
 * @package Imap\Components
 */
class AjaxResponse extends Response
{
    /**
     * AjaxResponse constructor.
     * @param $result
     */
    public function __construct($result)
    {
        parent::__construct($result);

        if (!function_exists('json_encode')) {
            $responseData = '{"jsonrpc": "2.0","error": {"message": "No function `json_encode` in PHP. Look at <a target=_blank href=\'http://stackoverflow.com/questions/18239405/php-fatal-error-call-to-undefined-function-json-decode\'>link</a>"}}';
            echo $responseData;
            exit;
        }
    }


    /**
     * @return mixed
     */
    public function response()
    {
        if (function_exists('session_write_close') && session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=UTF-8');
        }

        echo json_encode($this->result, FALSE);
        exit();
    }
}
