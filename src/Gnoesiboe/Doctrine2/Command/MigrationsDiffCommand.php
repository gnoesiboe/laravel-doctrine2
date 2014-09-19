<?php

namespace Gnoesiboe\Doctrine2\Command;

use Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand;
use Gnoesiboe\Doctrine2\Migrations\RegisterConfiguration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * MigrationsDiffCommand
 */
class MigrationsDiffCommand extends DiffCommand
{

    use RegisterConfiguration;

    /**
     * @param InputInterface $input
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
