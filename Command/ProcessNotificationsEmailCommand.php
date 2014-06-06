<?php

namespace ABMundi\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessNotificationsEmailCommand extends WorkerCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('abmundi:notification:process-notifications-email')
            ->setDescription('Process pending email notifications')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'How much notifications must to be processed?', 20)
        ;
    }
    
    protected function doExecute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $emailManager = $container->get('abm_notification.email.manager');
        $this->writeOutput($output, $emailManager->processNotifications($input->getOption('limit')));
    }
    
}