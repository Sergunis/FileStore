<?php

/**
 * Created by PhpStorm.
 * User: Hett
 * Date: 24.09.2014
 * Time: 12:00
 */
abstract class FileStoreClient
{
    const LOG_LEVEL_TRACE = 'trace';
    const LOG_LEVEL_WARNING = 'warning';
    const LOG_LEVEL_ERROR = 'error';
    const LOG_LEVEL_INFO = 'info';
    const LOG_LEVEL_PROFILE = 'profile';

    protected $servers = [];
    protected $_ch;

    public function __construct($servers, $verbose = false)
    {
        $this->servers = $servers;
        $this->_ch = curl_init();
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, false);

        if ($verbose) {
            curl_setopt($this->_ch, CURLOPT_VERBOSE, true);
//            $vb = fopen('php://output', 'rw+');
//            curl_setopt($this->_ch, CURLOPT_STDERR, $vb);
        }

    }

    public function delete($container, $prefix, $name, $project)
    {
        $request = $this->_delete("file/{$container}/{$prefix}/{$name}/{$project}");
        return $this->handlingRequest($request);
    }

    protected function _delete($uri)
    {
        curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        return $this->_request($uri);
    }

    protected function _request($uri)
    {
        foreach ($this->servers as $server) {
            curl_setopt($this->_ch, CURLOPT_URL, $server . '/' . $uri);
            $response = curl_exec($this->_ch);
            $data = json_decode($response, true);

            return $data;
        }
        return false;
    }

    protected function handlingRequest($request)
    {
        if (!isset($request['status']))
            return false;

        switch ($request['status']) {
            case 'success':
                return isset($request['data']) ? $request['data'] : true;
            case 'exception':
                $this->log("FileStore exception: {$request['message']}", self::LOG_LEVEL_WARNING);
                return false;
            default:
                $this->log("FileStore unknown response", self::LOG_LEVEL_WARNING);
                return false;
        }
    }

    abstract function log($msg, $level = self::LOG_LEVEL_INFO, $category = 'FileStore');

    public function create($container, $prefix, $name, $project, $params)
    {
        $request = $this->_post("file/{$container}/{$prefix}/{$name}/{$project}", $params);
        return $this->handlingRequest($request);
    }

    public function find($md5)
    {
        $request = $this->_get("file/find/{$md5}");
        return $this->handlingRequest($request);
    }

    protected function _get($uri)
    {
        curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, "GET");
        return $this->_request($uri);
    }

    protected function _post($uri, $params = [])
    {
        curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($this->_ch, CURLOPT_POSTFIELDS, json_encode($params));

        return $this->_request($uri);
    }

    protected function _put($uri)
    {
        curl_setopt($this->_ch, CURLOPT_PUT, true);
        return $this->_request($uri);
    }

} 