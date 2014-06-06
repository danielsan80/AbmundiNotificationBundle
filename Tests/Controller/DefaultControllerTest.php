<?php

namespace ABMundi\NotificationBundle\Tests\Controller;

use ABMundi\Common\ABMundiTestCase;

/**
 * Test over Default controller 
 */
class DefaultControllerTest extends ABMundiTestCase
{
    /**
     * Notificiation list 
     */
    public function test_notificationAction()
    {
        $app = $this->loadApp('mario', 'mario');

        $crawler = $app->client->request('GET', $app->router->generate('notification_index'));
        $this->isSuccessful($app->client->getResponse(), true);
        $this->assertEquals(1, $crawler->filter('h1:contains("Notifications")')->count());

        $this->guestIsRedirectedToLogin('notification_index');

    }

    /**
     * I want to be notified when someone of my followings close a goal
     */
    public function test_when_I_close_a_goal_my_followers_will_be_notified()
    {
        $app = $this->loadApp('mario', 'mario');

        $eventManager = $app->container->get('abm_notification.event.manager');

        $goalRepo = $app->em->getRepository('ABMundiGoalBundle:Goal');
        $goal = $goalRepo->findOneByName('Save the princess');

        $crawler = $app->client->request('GET', $app->router->generate('goal_learn', array('username' => $goal->getOwner()->getUsername(), 'slug' => $goal->getSlug())));
        $this->isSuccessful($app->client->getResponse());

        $form = $crawler->selectButton('Set the learning of this Goal')->form(array(
            'goalLearnType[metricValue]'  => '100',
            'goalLearnType[learning]'  => 'Save princesses is difficult',
            'goalLearnType[completed]'  => '1',
        ));

        $app->client->submit($form);
        $this->processAll($app);
        

        
        $app = $this->loadApp('luigi', 'luigi');
        $crawler = $app->client->request('GET', $app->router->generate('ABMundiSplashBundle_homepage'));
        $this->isSuccessful($app->client->getResponse());

        $this->assertEquals(1, $crawler->filter('a.notifications>span.count:contains("1")')->count());

        $crawler = $app->client->request('GET', $app->router->generate('notification_index'));

        $this->assertEquals(0, $crawler->filter('a.notifications>span.count:contains("1")')->count());

        $content = $app->client->getResponse()->getContent();
        $this->assertRegExp('/mario/', $content);
        $this->assertRegExp('/closed/', $content);
        $this->assertRegExp('/Save the Princess/', $content);
    }
    
}