<?php

namespace ABMundi\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessEventsFacebookCommand extends WorkerCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('abmundi:notification:process-events-facebook')
            ->setDescription('Process pending facebook channel events')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'How much events must to be processed?', 20)
        ;
    }
    
    protected function doExecute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $nm = $container->get('abm_notification.notification.manager');
        $this->writeOutput($output, $nm->processFacebookChannelEvents($input->getOption('limit')));
    }
    
}