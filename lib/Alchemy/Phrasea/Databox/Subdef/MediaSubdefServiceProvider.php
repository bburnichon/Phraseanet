<?php
/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2016 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Databox\Subdef;

use Alchemy\Phrasea\Databox\DataboxBoundRepositoryProvider;
use Alchemy\Phrasea\Databox\DataboxConnectionProvider;
use Alchemy\Phrasea\Record\RecordReference;
use Silex\Application;
use Silex\ServiceProviderInterface;

class MediaSubdefServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['provider.factory.media_subdef'] = $app->protect(function ($databoxId) use ($app) {
            /** @var \Alchemy\Phrasea\Application $app */
            $databox = $app->findDataboxById($databoxId);
            $recordRepository = $databox->getRecordRepository();

            return function (array $data) use ($app, $recordRepository) {
                $app['stopwatch']->start('media_subdef_factory#fetchRecord', 'phraseanet');
                $recordReference = $recordRepository->find($data['record_id']);
                $app['stopwatch']->stop('media_subdef_factory#fetchRecord');

                $app['stopwatch']->start('media_subdef_factory#build', 'phraseanet');
                $media_subdef = new \media_subdef($app, $recordReference, $data['name'], false, $data);
                $app['stopwatch']->stop('media_subdef_factory#build');

                return $media_subdef;
            };
        });

        $app['provider.repo.media_subdef'] = $app->share(function (Application $app) {
            $connectionProvider = new DataboxConnectionProvider($app['phraseanet.appbox']);
            $factoryProvider = $app['provider.factory.media_subdef'];

            $repositoryFactory = new MediaSubdefRepositoryFactory($connectionProvider, $app['cache'], $factoryProvider, isset($app['stopwatch']) ? $app['stopwatch'] : null);

            return new DataboxBoundRepositoryProvider($repositoryFactory);
        });

        $app['service.media_subdef'] = $app->share(function (Application $app) {
            return new MediaSubdefService($app['provider.repo.media_subdef'], isset($app['stopwatch']) ? $app['stopwatch'] : null);
        });
    }

    public function boot(Application $app)
    {
        // no-op
    }
}
