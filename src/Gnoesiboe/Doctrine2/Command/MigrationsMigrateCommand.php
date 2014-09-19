<?php

namespace Gnoesiboe\Doctrine2\Command;

use Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand;
use Gnoesiboe\Doctrine2\Migrations\RegisterConfiguration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrationsMigrateCommand
 */
class MigrationsMigrateCommand extends MigrateCommand
{

    use RegisterConfiguration;

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->registerConfiguration($output);

        parent::execute($input, $output);
    }

    /**
     * @return string
     */
    public static function getClass()
    {
        return get_called_class();
    }
}
