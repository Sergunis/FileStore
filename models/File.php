<?php
/**
 * Created by PhpStorm.
 * User: Hett
 * Date: 16.09.14
 * Time: 15:07
 */

namespace models;


use components\Db;
use exceptions\FileStoreException;
use Monolog\Handler\Mongo;

/**
 * Class File
 * @package models
 *
 * @property string $_id
 * @property string $container
 * @property string $prefix
 * @property string $name
 * @property string $md5
 * @property int $size
 * @property string $storage
 * @property bool $is_deleted
 * @property string $date_created
 * @property string $date_updated
 * @property string $content_type
 * @property string[] $projects
 *
 */
class File
{
    protected static $_collection;
    protected $_attributes = [];

    public static function findOne(array $params)
    {
        $data = self::getCollection()->findOne($params);
        if (is_null($data))
            return null;

        $file = new self();
        $file->setAttributes($data);

        return $file;
    }

    public static function getCollection()
    {
        if (is_null(self::$_collection)) {
            $db = Db::getInstance();
            self::$_collection = $db->selectCollection('filestore', 'files');
        }

        return self::$_collection;
    }

    public function getAttributes()
    {
        return $this->_attributes;
    }

    public function setAttributes(array $attributes)
    {
        $this->_attributes = array_intersect_key(
            $this->_attributes + $attributes,
            array_flip($this->attributes())
        );
    }

    public function __get($attribute)
    {
        if (in_array($attribute, $this->attributes()))
            return isset($this->_attributes[$attribute]) ? $this->_attributes[$attribute] : null;
        else
            throw new FileStoreException('Model "' . __CLASS__ . '" no have attribute "' . $attribute . '"');

    }

    public function __set($attribute, $value)
    {
        if (in_array($attribute, $this->attributes())) {
            $this->_attributes[$attribute] = $value;
        } else {
            throw new FileStoreException('Model "' . __CLASS__ . '" no have attribute "' . $attribute . '"');
        }
    }

    public function attributes()
    {
        return [
            '_id',
            'container',
            'prefix',
            'name',
            'md5',
            'size',
            'storage',
            'is_deleted',
            'date_created',
            'date_updated',
            'content_type',
            'projects',
        ];
    }

    public function save()
    {
        if (!$this->date_created)
            $this->date_created = new \MongoDate();

        $this->date_updated = new \MongoDate();

        if (!($this->size instanceof \MongoInt64))
            $this->size = new \MongoInt64($this->size);

        $criteria = [
            'container' => $this->container,
            'prefix' => $this->prefix,
            'name' => $this->name,
        ];
        $result = self::getCollection()->update(
            $criteria,
            $this->_attributes, [
                'upsert' => true,
                'multiple' => false,
            ]);

        if ($result) {
            $this->setAttributes(self::getCollection()->findOne($criteria));

            return true;
        } else
            return false;
    }

    public function addProject($project)
    {
        $collection = self::getCollection();
        return $collection->update(['_id' => $this->_id], [
            '$addToSet' => [
                'projects' => $project,
            ]
        ]);
    }

    public function removeProject($project)
    {
        $collection = self::getCollection();
        return $collection->update(['_id' => $this->_id], [
            '$pull' => [
                'projects' => $project,
            ]
        ]);
    }


} 