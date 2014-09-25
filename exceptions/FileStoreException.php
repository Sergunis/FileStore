<?php
/**
 * Created by PhpStorm.
 * User: Hett
 * Date: 17.09.2014
 * Time: 13:22
 */

namespace exceptions;

class FileStoreException extends \Exception
{

    public function convertToJsonResponse()
    {
        return json_encode([
            'status' => 'exception',
            'message' => $this->getMessage(),
        ]);

    }
} 