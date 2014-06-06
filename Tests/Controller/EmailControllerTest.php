<?php

namespace ABMundi\NotificationBundle\Tests\Controller;

use ABMundi\Common\ABMundiTestCase;

/**
 * Test over Default controller 
 */
class EmailControllerTest extends ABMundiTestCase
{
    /**
     * Notificiation list 
     */
    public function test_reminderExecAction()
    {
        $app = $this->loadApp();

        $crawler = $app->client->request('GET', $app->router->generate('worker_emailReminder'));
        $this->isSuccessful($app->client->getResponse(), true);
        
        $this->isSentAnEmail($app->client, 'pisolo@abmundi.com', 'Your journeys need you, pisolo');
    }
}