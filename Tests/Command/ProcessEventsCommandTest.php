<?php

namespace ABMundi\NotificationBundle\Tests\Command;

use ABMundi\Common\ABMundiTestCase;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use ABMundi\NotificationBundle\Command\ReminderCommand;

/**
 * Test Reminder Command
 */
class ProcessEventsCommandTest extends ABMundiTestCase
{
    
    /**
     * Notificiation list 
     */
    public function test_notificationCreationSending()
    {
        $app = $this->loadApp('mario', 'mario');
        $notificationManager = $app->container->get('abm_notification.notification.manager');

        $crawler = $app->client->request('GET', $app->router->generate('user_follow', array(
                    'username' => 'luigi'
                )));
        
        $container = $app->client->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        
        $notificationRepo = $em->getRepository('ABMundiNotificationBundle:Notification');
        $this->assertCount(0, $notificationRepo->findAll());
        
        $output = $this->runCommand($app->client, "abmundi:notification:process-events --limit=10 --host=abmundi/app_test.php");
        $output = $this->runCommand($app->client, "abmundi:notification:process-events-email --limit=10 --host=abmundi/app_test.php");
        $this->assertCount(1, $notificationRepo->findAll());
        $output = $this->runCommand($app->client, "abmundi:notification:process-notifications-email --limit=10 --host=abmundi/app_test.php");
        //$this->isSentAnEmail($app->client, 'luigi@abmundi.com', 'asdf');

        $this->assertRegExp('/done/', $output);
    }
    
    /**
     * Notificiation list 
     */
    public function test_commandsOutputSending()
    {
        $app = $this->loadApp('mario', 'mario');
        $crawler = $app->client->request('GET', $app->router->generate('user_follow', array(
                    'username' => 'luigi'
                )));
        
        $output = $this->runCommand($app->client, "abmundi:notification:process-events --limit=10 --host=abmundi/app_test.php");
        $this->assertEquals(". done\n", $output);
        $output = $this->runCommand($app->client, "abmundi:notification:process-events-email --limit=10 --host=abmundi/app_test.php");
        $this->assertEquals(". done\n", $output);
        $output = $this->runCommand($app->client, "abmundi:notification:process-events-main --limit=10 --host=abmundi/app_test.php");
        $this->assertEquals(". done\n", $output);
        $output = $this->runCommand($app->client, "abmundi:notification:process-notifications-email --limit=10 --host=abmundi/app_test.php");
        $this->assertEquals(". done\n", $output);
    }


}