<?php

namespace ABMundi\NotificationBundle\Tests\Command;

use ABMundi\Common\ABMundiTestCase;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use ABMundi\NotificationBundle\Command\ReminderCommand;

/**
 * Test Reminder Command
 */
class ReminderCommandTest extends ABMundiTestCase
{
    
    /**
     * Notificiation list 
     */
    public function test_reminderEmailSending()
    {
        $client = self::createClient();
        $output = $this->runCommand($client, "abmundi:notification:email:reminder");

        $this->assertRegExp('/done/', $output);
    }


}