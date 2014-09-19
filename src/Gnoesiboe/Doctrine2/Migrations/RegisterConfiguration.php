<?php

namespace Gnoesiboe\Doctrine2\Migrations;

use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ConfigurationTrait
 */
trait RegisterConfiguration
{

    /**
     * Registers the laravel specific configuration we use for
     * migrations.
     *
     * @param OutputInterface $output
     */
    public function registerConfiguration(OutputInterface $output)
    {
        /** @var ConnectionHelper $connectionHelper */
        $connectionHelper = $this->getHelper('connection');

        // set our custom configuration from our config file
        $configuration = ConfigurationFactory::createInstance()
            ->createConiguration($connectionHelper->getConnection(), $output);

        $this->setMigrationConfiguration($configuration);
    }
}
