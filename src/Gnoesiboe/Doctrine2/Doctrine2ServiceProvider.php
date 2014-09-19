<?php

namespace Gnoesiboe\Doctrine2;

use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Persistence\Mapping\Driver\PHPDriver;
use Doctrine\DBAL\Migrations\MigrationsVersion;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Gnoesiboe\Doctrine2\Command\MigrationsDiffCommand;
use Gnoesiboe\Doctrine2\Command\MigrationsExecuteCommand;
use Gnoesiboe\Doctrine2\Command\MigrationsGenerateCommand;
use Gnoesiboe\Doctrine2\Command\MigrationsLatestCommand;
use Gnoesiboe\Doctrine2\Command\MigrationsMigrateCommand;
use Gnoesiboe\Doctrine2\Command\MigrationsStatusCommand;
use Gnoesiboe\Doctrine2\Command\MigrationsVersionCommand;
use Gnoesiboe\Domain\SqlLogger;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\HelperSet;

class Doctrine2ServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * @inheritdoc
     */
    public function boot()
    {
        // package to be able to use the configuration setup
        $this->package('gnoesiboe/doctrine2');

        $this->registerCommands();
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerOrmConfiguration();
        $this->registerEntityManager();
        $this->registerCache();
        $this->registerSqlLogger();
        $this->registerMappingDriver();
    }

    protected function registerCommands()
    {
        /**
         * Makes sure that the db helper is registered with artisan. It cannot be directly register
         * on the command as the helperset is overriden after initialization by artisan... Stupid....
         */
        $registerDbHelper = function() {

            /** @var EntityManager $em */
            $em = \App::make('doctrine2.entityManager');

            /** @var HelperSet $helperSet */
            $helperSet = \Artisan::getHelperSet();

            if ($helperSet->has('db') === false) {
                $helperSet->set(new ConnectionHelper($em->getConnection()), 'db');
            }
        };

        /**
         * Makes sure that the entity manager helper is registered with artisan. It cannot be directly registered
         * on the command as the helperset is overriden after initialization by artisan... Stupid....
         */
        $registerEmHelper = function() {

            /** @var EntityManager $em */
            $em = \App::make('doctrine2.entityManager');

            /** @var HelperSet $helperSet */
            $helperSet = \Artisan::getHelperSet();

            if ($helperSet->has('em') === false) {
                $helperSet->set(new EntityManagerHelper($em), 'em');
            }
        };

        $this->registerOrmCommands($registerEmHelper);
        $this->registerDbalCommands($registerDbHelper);
        $this->registerMigrationsCommands($registerEmHelper, $registerDbHelper);
    }

    /**
     * @param callable $registerEmHelper
     * @param callable $registerDbHelper
     */
    protected function registerMigrationsCommands(\Closure $registerEmHelper, \Closure $registerDbHelper)
    {
        $registerDialogHelper = function () {

            /** @var HelperSet $helperSet */
            $helperSet = \Artisan::getHelperSet();

            if ($helperSet->has('dialog') === false) {
                $helperSet->set(new DialogHelper(), 'dialog');
            }
        };

        $this->app['doctrine2.command.migrations-diff'] = $this->app->share(function (Application $app) use ($registerEmHelper) {
            $registerEmHelper();

            return $app->make(MigrationsDiffCommand::getClass());
        });

        $this->app['doctrine2.command.migrations-execute'] = $this->app->share(function (Application $app) use ($registerDialogHelper) {
            $registerDialogHelper();

            return $app->make(MigrationsExecuteCommand::getClass());
        });

        $this->app['doctrine2.command.migrations-status'] = $this->app->share(function (Application $app) {
            return $app->make(MigrationsStatusCommand::getClass());
        });

        $this->app['doctrine2.command.migrations-generate'] = $this->app->share(function (Application $app) {
            return $app->make(MigrationsGenerateCommand::getClass());
        });

        $this->app['doctrine2.command.migrations-latest'] = $this->app->share(function (Application $app) {
            return $app->make(MigrationsLatestCommand::getClass());
        });

        $this->app['doctrine2.command.migrations-migrate'] = $this->app->share(function (Application $app) {
            return $app->make(MigrationsMigrateCommand::getClass());
        });

        $this->app['doctrine2.command.migrations-version'] = $this->app->share(function (Application $app) {
            return $app->make(MigrationsVersionCommand::getClass());
        });

        $this->commands(array(
            'doctrine2.command.migrations-diff',
            'doctrine2.command.migrations-execute',
            'doctrine2.command.migrations-status',
            'doctrine2.command.migrations-generate',
            'doctrine2.command.migrations-latest',
            'doctrine2.command.migrations-migrate',
            'doctrine2.command.migrations-version',
        ));
    }

    /**
     * @param callable $registerDbHelper
     */
    protected function registerDbalCommands(\Closure $registerDbHelper)
    {
        $this->app['doctrine2.command.dbal-import'] = $this->app->share(function (Application $app) use ($registerDbHelper) {
            $registerDbHelper();
            return $app->make('Doctrine\DBAL\Tools\Console\Command\ImportCommand');
        });

        $this->app['doctrine2.command.dbal-run-sql'] = $this->app->share(function (Application $app) use ($registerDbHelper) {
            $registerDbHelper();
            return $app->make('Doctrine\DBAL\Tools\Console\Command\RunSqlCommand');
        });

        $this->commands(array(
            'doctrine2.command.dbal-import',
            'doctrine2.command.dbal-run-sql',
        ));
    }

    /**
     * @param callable $registerEmHelper
     */
    protected function registerOrmCommands(\Closure $registerEmHelper)
    {
        $this->app['doctrine2.command.orm-info'] = $this->app->share(function (Application $app) use ($registerEmHelper) {
            $registerEmHelper();
            return $app->make('Doctrine\ORM\Tools\Console\Command\InfoCommand');
        });

        $this->app['doctrine2.command.orm-clear-cache-metadata'] = $this->app->share(function (Application $app) use ($registerEmHelper) {
            $registerEmHelper();
            return $app->make('Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand');
        });

        $this->app['doctrine2.command.orm-clear-cache-query'] = $this->app->share(function (Application $app) use ($registerEmHelper) {
            $registerEmHelper();
            return $app->make('Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand');
        });

        $this->app['doctrine2.command.orm-clear-cache-result'] = $this->app->share(function (Application $app) use ($registerEmHelper) {
            $registerEmHelper();
            return $app->make('Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand');
        });

        $this->app['doctrine2.command.orm-ensure-production-settings'] = $this->app->share(function (Application $app) use ($registerEmHelper) {
            $registerEmHelper();
            return $app->make('Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand');
        });

        $this->app['doctrine2.command.orm-generate-entities'] = $this->app->share(function (Application $app) use ($registerEmHelper) {
            $registerEmHelper();
            return $app->make('Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand');
        });

        $this->app['doctrine2.command.orm-generate-proxies'] = $this->app->share(function (Application $app) use ($registerEmHelper) {
            $registerEmHelper();
            return $app->make('Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand');
        });

        $this->app['doctrine2.command.orm-generate-repositories'] = $this->app->share(function (Application $app) use ($registerEmHelper) {
            $registerEmHelper();
            return $app->make('Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand');
        });

        $this->app['doctrine2.command.orm-run-dql'] = $this->app->share(function (Application $app) use ($registerEmHelper) {
            $registerEmHelper();
            return $app->make('Doctrine\ORM\Tools\Console\Command\RunDqlCommand');
        });

        $this->app['doctrine2.command.orm-schema-tool-create'] = $this->app->share(function (Application $app) use ($registerEmHelper) {
            $registerEmHelper();
            return $app->make('Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand');
        });

        $this->app['doctrine2.command.orm-schema-tool-drop'] = $this->app->share(function (Application $app) use ($registerEmHelper) {
            $registerEmHelper();
            return $app->make('Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand');
        });

        $this->app['doctrine2.command.orm-schema-tool-update'] = $this->app->share(function (Application $app) use ($registerEmHelper) {
            $registerEmHelper();
            return $app->make('Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand');
        });

        $this->app['doctrine2.command.orm-validate-schema'] = $this->app->share(function (Application $app) use ($registerEmHelper) {
            $registerEmHelper();
            return $app->make('Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand');
        });

        $this->commands(array(
            'doctrine2.command.orm-info',
            'doctrine2.command.orm-clear-cache-metadata',
            'doctrine2.command.orm-clear-cache-query',
            'doctrine2.command.orm-clear-cache-result',
            'doctrine2.command.orm-ensure-production-settings',
            'doctrine2.command.orm-generate-entities',
            'doctrine2.command.orm-generate-proxies',
            'doctrine2.command.orm-generate-repositories',
            'doctrine2.command.orm-run-dql',
            'doctrine2.command.orm-schema-tool-create',
            'doctrine2.command.orm-schema-tool-drop',
            'doctrine2.command.orm-schema-tool-update',
            'doctrine2.command.orm-validate-schema'
        ));
    }

    protected function registerMappingDriver()
    {
        $this->app['doctrine2.mappingDriver'] = $this->app->share(function (Application $app) {
            $entityMappingPaths = \Config::get('doctrine2::paths.entity.mapping', array());

            return new PHPDriver($entityMappingPaths); //@todo add mapping extension
        });
    }

    protected function registerSqlLogger()
    {
        $this->app['doctrine2.sqlLogger'] = $this->app->share(function (Application $app) {
            return new SqlLogger();
        });
    }

    protected function registerOrmConfiguration()
    {
        $debug = \Config::get('app.debug');

        $this->app['doctrine2.ormConfiguration'] = $this->app->share(function (Application $app) use ($debug) {
            $configuration = Setup::createConfiguration($debug, \Config::get('paths.proxy'), $app->make('doctrine2.cache'));
            $configuration->setMetadataDriverImpl($app->make('doctrine2.mappingDriver'));
            $configuration->setSQLLogger($app->make('doctrine2.sqlLogger'));

            return $configuration;
        });
    }

    protected function registerCache()
    {
        $this->app['doctrine2.cache'] = $this->app->share(function (Application $app) {
            $memcache = new \Memcache();
            $memcache->connect('127.0.0.1');

            $cache = new MemcacheCache();
            $cache->setMemcache($memcache);

            return $cache;
        });
    }

    protected function registerEntityManager()
    {
        $this->app['doctrine2.entityManager'] = $this->app->share(function (Application $app) {
            // $connections = \Config::get('doctrine2::connections', array());
            //@todo multiple connections setup?

            $defaultConnection = \Config::get('doctrine2::connections.default');

            return EntityManager::create($defaultConnection, $app->make('doctrine2.ormConfiguration'));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array(
            'entityManager'
        );
    }
}
