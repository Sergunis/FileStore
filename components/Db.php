<?php
/**
 * Created by PhpStorm.
 * User: Hett
 * Date: 16.09.14
 * Time: 15:31
 */

namespace components;


class Db extends \MongoClient {
    /**
     * @var Db
     */
    protected static $_instance;
    protected static $_config;
    public static function setConfig(array $config) {
        self::$_config = $config;
    }

    private function __clone(){}

    /**
     * @return Db
     */
    public static function getInstance() {
        // проверяем актуальность экземпляра
        if (null === self::$_instance) {
            // создаем новый экземпляр
            $server = isset(self::$_config['server'])? self::$_config['server'] : "mongodb://localhost:27017";
            $options = isset(self::$_config['options'])? self::$_config['options'] :  array("connect" => TRUE);
            self::$_instance = new self($server, $options);
        }
        // возвращаем созданный или существующий экземпляр
        return self::$_instance;
    }
} 