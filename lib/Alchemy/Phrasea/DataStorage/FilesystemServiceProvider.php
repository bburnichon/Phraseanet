<?php
/**
 * This file is part of Phraseanet
 *
 * (c) 2005-2016 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\DataStorage;

use Aws\S3\S3Client;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AwsS3v2\AwsS3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Silex\Application;
use Silex\ServiceProviderInterface;

class FilesystemServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['filesystems.adapter_factories'] = $this->createAdapterFactoriesContainer();
        $app['filesystems.adapter_provider'] = $app->share(function (Application $app) {
            return new FlysystemAdapterProvider($app['filesystems.adapter_factories']);
        });

        $app['filesystems'] = $filesystems = new \Pimple();
        $app['filesystems.factory'] = $app->protect(function ($type, array $configuration = []) use ($app) {
            $factory = $app['filesystems.adapter_factories'][$type];

            return new Filesystem($factory($configuration));
        });
        $filesystems['local'] = $filesystems->share(function () use ($app) {
            $factory = $app['filesystems.factory'];

            return $factory('local', ['path' => $app['cache.path'] . 'local_filesystem']);
        });

        $filesystems['substitute'] = $filesystems->share(function () use ($app) {
            $factory = $app['filesystems.factory'];

            return $factory('local', ['path' => $app['root.path'] . 'www/assets/common/images/icons/substitution']);
        });

        $app['filesystem.mount_manager'] = $app->share(function (Application $app) {
            $mountManager = new MountManager();

            $mountManager->mountFilesystem('local', $app['filesystems']['local']);

            return $mountManager;
        });
    }

    public function boot(Application $app)
    {
        // Nothing to do
    }

    /**
     * @return \Pimple
     */
    private function createAdapterFactoriesContainer()
    {
        $factories = new \Pimple();

        $factories['local'] = $factories->protect(function (array $configuration) {
            return new Local($configuration['path']);
        });

        $factories['aws-s3-v2'] = $factories->protect(function (array $configuration) {
            $configuration = array_replace(
                [
                    'key' => '',
                    'secret' => '',
                    'region' => '',
                    'bucket' => '',
                    'prefix' => null,
                    'options' => null,
                ],
                $configuration
            );

            $client = S3Client::factory([
                'key' => $configuration['key'],
                'secret' => $configuration['secret'],
                'region' => $configuration['region'],
            ]);

            return new AwsS3Adapter(
                $client,
                $configuration['bucket'],
                $configuration['prefix'] ?: null,
                $configuration['options'] ?: null
            );
        });

        return $factories;
    }
}
