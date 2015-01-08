<?php

/**
 * Class DatabaseErrorHandlerImpl
 *
 * @author lbob created at 2015/1/8 20:21
 */
class DatabaseErrorHandlerImpl implements \Xaircraft\Database\DatabaseErrorHandler {

    public function onError($errorCode, $errorInfo, $queryString = null, array $params = null)
    {
        $message = $errorCode . ': ' . $errorInfo[2];
        if (isset($queryString)) {
            if (isset($params)) {
                foreach ($params as $item) {
                    $index = stripos($queryString, '?');
                    $queryString = substr($queryString, 0, $index) . "'" . $item . "'" . substr($queryString, $index + 1, strlen($queryString) - $index);
                }
            }
            $message .= ', QueryString is [' . $queryString . ']';
        }
        \Xaircraft\Log::error('Database', $message);
        throw new \Exception($message);
    }
}

 