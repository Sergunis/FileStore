<?php
/**
 * Created by PhpStorm.
 * User: Hett
 * Date: 24.09.2014
 * Time: 13:11
 */

require 'FileStoreClient.php';

define('VERBOSE', false);

class FileStore extends FileStoreClient
{

    function log($msg, $level = self::LOG_LEVEL_INFO, $category = 'FileStore')
    {
        echo strtoupper($level), ": ", $msg, "\n";
    }
}

//$fs = new FileStore(['78.140.175.125:82']);
$fs = new FileStore(['http://192.168.4.100/fo_manage/scripts/filestore'], VERBOSE);

echo "Test create: ";
$status = $fs->create('testContainer', 'testPrefix', 'testName', 'project1', [
    'attributes' => [
        'md5' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'size' => 100000,
        'storage' => 's1',
        'content_type' => 'text/plain',
    ],
]);

echo isset($status['_id'])? "SUCCESS\n" : "FAILED\n";


echo "Test find: ";
$status = $fs->find('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
echo isset($status['projects']) && in_array('project1', $status['projects'])? "SUCCESS\n" : "FAILED\n";


echo "Test delete: ";
$status = $fs->delete('testContainer', 'testPrefix', 'testName', 'project1');
echo $status === true? "SUCCESS\n" : "FAILED\n";


echo "Test find: ";
$status = $fs->find('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
echo isset($status['projects']) && !in_array('project1', $status['projects'])? "SUCCESS\n" : "FAILED\n";