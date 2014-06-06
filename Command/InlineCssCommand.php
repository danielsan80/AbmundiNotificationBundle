<?php

namespace ABMundi\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InlineCssCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('abmundi:notification:email:compile')
            ->setDescription('Compile notification emails templates')
            ->addArgument('key', InputOption::VALUE_REQUIRED, 'EventKey?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        exec('./app/config/cssparser.rb '. $input->getArgument('key'));
        $output->writeln('done');
    }
}