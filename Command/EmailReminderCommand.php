<?php

namespace ABMundi\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EmailReminderCommand extends WorkerCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('abmundi:notification:email:reminder')
            ->setDescription('Check for lazy users and remind them to use ABMundi')
            ->addOption('days', null, InputOption::VALUE_REQUIRED, 'How long should I wait to send the reminder?', '7')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'How much users must to be processed?', 20)
        ;
    }
    
    protected function doExecute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $days = $input->getOption('days');
        $limit = $input->getOption('limit');
        $emailManager = $container->get('abm_notification.email.manager');
        $this->writeOutput($output, $emailManager->processEmailReminder($limit, $days));
    }

}