<?php

namespace Gnoesiboe\Doctrine2\Migrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\OutputWriter;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ConfigurationFactory
 */
class ConfigurationFactory
{

    /**
     * @param Connection $connection
     * @param OutputInterface $output
     *
     * @return Configuration
     */
    public function createConiguration(Connection $connection, OutputInterface $output)
    {
        $outputWriter = new OutputWriter(function($message) use ($output) {
            return $output->writeln($message);
        });

        $configuration = new Configuration($connection, $outputWriter);

        $migrationsDirectory = \Config::get('doctrine2::migrations.directory');

        $configuration->setName(\Config::get('doctrine2::migrations.name'));
        $configuration->setMigrationsNamespace(\Config::get('doctrine2::migrations.namespace'));
        $configuration->setMigrationsTableName(\Config::get('doctrine2::migrations.table_name'));
        $configuration->setMigrationsDirectory($migrationsDirectory);

        $configuration->validate();

        $configuration->registerMigrationsFromDirectory($migrationsDirectory);

        return $configuration;
    }

    /**
     * @return static
     */
    public static function createInstance()
    {
        return new static;
    }
}
