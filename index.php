<?php
// This is the first application
// Second comment
use exceptions\FileStoreException;

$config = require(__DIR__ . '/config.php');
if (is_file(__DIR__ . '/config-local.php')) {
    include(__DIR__ . '/config-local.php');
}

require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

\components\Db::setConfig($config['mongodb']);

$app = new \Slim\Slim([
    'debug' => true,
    'log.level' => \Slim\Log::DEBUG,
    'log.enabled' => true,
]);

// After instantiation
$log = $app->getLog();
$log->setEnabled(true);

/**
 * CREATE
 */
$app->post('/file/:container/:prefix/:name/:project',
    function ($container, $prefix, $name, $project) use ($app) {
        try {
            $requiredFields = [
                'md5' => true,
                'size' => true,
                'storage' => true,
                'content_type' => true,
            ];

            $postData = (array)json_decode(file_get_contents('php://input'), true);

            if (!isset($postData['attributes'])) {
                throw new FileStoreException('File attributes must be set');
            }

            foreach ($postData['attributes'] as $k => $v) {
                unset($requiredFields[$k]);
            }

            if (count($requiredFields))
                throw new FileStoreException('Not all required fields are included: '
                    . implode('|', array_keys($requiredFields)));

            $file = \models\File::findOne([
                'container' => $container,
                'prefix' => $prefix,
                'name' => $name,
            ]);

            if (!is_null($file)) {
                if (in_array($project, (array)$file->projects))
                    throw new FileStoreException('Specified file was exists');
                else {
                    $file->addProject($project);
                    echo json_encode([
                        'status' => 'success',
                        'data' => [
                            '_id' => $file->_id,
                        ],
                    ]);
                    return;
                }
            }

            $file = new \models\File();
            $file->container = $container;
            $file->prefix = $prefix;
            $file->name = $name;

            $file->setAttributes($postData['attributes']);
            $file->projects = [$project];

            if ($file->save()) {
                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        '_id' => $file->_id,
                    ],
                ]);
            } else
                throw new FileStoreException('Error save file in database');


        } catch (FileStoreException $e) {
            echo $e->convertToJsonResponse();
        }

    });

/**
 * Find by MD5
 */
$app->get('/file/find/:md5', function ($md5) use ($app) {
    try {
        $file = \models\File::findOne(['md5' => $md5]);
        if (is_null($file))
            throw new FileStoreException('File not found');

        echo json_encode([
            'status' => 'success',
            'data' => $file->getAttributes(),
        ]);

    } catch (FileStoreException $e) {
        echo $e->convertToJsonResponse();
    }

});

/**
 * DELETE
 */
$app->delete('/file/:container/:prefix/:name/:project', function ($container, $prefix, $name, $project) use ($app) {
    try {
        $file = \models\File::findOne([
            'container' => $container,
            'prefix' => $prefix,
            'name' => $name,
        ]);

        if (is_null($file)) {
            throw new FileStoreException('File not found');
        }

        if ($file->removeProject($project)) {
            echo json_encode([
                'status' => 'success',
            ]);
        } else
            throw new FileStoreException('Error remove project from database');

    } catch (FileStoreException $e) {
        echo $e->convertToJsonResponse();
    }
});

$app->run();