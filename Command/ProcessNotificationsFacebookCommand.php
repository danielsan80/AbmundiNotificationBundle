<?php

namespace ABMundi\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessNotificationsFacebookCommand extends WorkerCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('abmundi:notification:process-notifications-facebook')
            ->setDescription('Process pending facebook notifications')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'How much notifications must to be processed?', 20)
        ;
    }
    
    protected function doExecute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $facebookManager = $container->get('abm_notification.facebook.manager');
        $this->writeOutput($output, $facebookManager->processNotifications($input->getOption('limit')));
    }
    
}