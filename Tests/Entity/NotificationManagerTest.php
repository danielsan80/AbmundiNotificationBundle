<?php

namespace ABMundi\NotificationBundle\Tests\Entity;

use ABMundi\Common\ABMundiTestCase;
use ABMundi\UserBundle\Entity\User;
use ABMundi\GoalBundle\Entity\Goal;
use ABMundi\NotificationBundle\Tests\Pheanstalk\Pheanstalk;

/**
 * Test over EventManager
 */
class NotificationManagerTest extends ABMundiTestCase
{
    /**
     * SetUp for all tests
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Test of generation of event
     */
    public function test_createEvent()
    {
        $app = $this->loadApp('mario', 'mario');

        $goalRepo = $app->em->getRepository('ABMundiGoalBundle:Goal');
        $userRepo = $app->em->getRepository('ABMundiUserBundle:User');

        $eventManager = $app->container->get('abm_notification.event.manager');
        $notificationManager = $app->container->get('abm_notification.notification.manager');

        $mario = $userRepo->findOneByUsername('mario');
        $luigi = $userRepo->findOneByUsername('luigi');
        $peach = $userRepo->findOneByUsername('peach');

        $marioSaveThePrincess = $goalRepo->findOneByName('Save the Princess');

        $luigiSaveThePrincess = $goalRepo->fork($marioSaveThePrincess);

        $now = new \DateTime;

        $luigiSaveThePrincess->setName('Help Mario to save the princess');
        $luigiSaveThePrincess->setOwner($luigi);
        $luigiSaveThePrincess->setExpireAt($now->modify('+1 month'));
        $luigiSaveThePrincess->setDescription('I want to save the princess too');
        $luigiSaveThePrincess->setWhy('Cause I don\'t like Bowser');
        $app->em->persist($luigiSaveThePrincess);
        $app->em->flush();

        $eventManager->createEvent('goal.fork', $luigiSaveThePrincess, $luigi, $now);
        $this->processAll($app);

        $notifications = $notificationManager->getUnreadNotifications($mario);
        $found = false;
        $this->assertCount(1, $notifications);

        foreach ($notifications as $notification) {
            break;
        }
        $events = $notification->getEvents();
        $event = $events[0];
        $subject = $event->getSubject();
        $this->assertTrue($subject instanceof \ABMundi\GoalBundle\Entity\Goal);
        $this->assertEquals($luigiSaveThePrincess->getName(), $subject->getName());
        $this->assertEquals($luigi->getUsername(), $event->getActor()->getUsername());
        $this->assertFalse($notification->isRead());

        $peachSaveThePrincess = $goalRepo->fork($marioSaveThePrincess);

        $now = new \DateTime;

        $peachSaveThePrincess->setName('Help Mario to save me');
        $peachSaveThePrincess->setExpireAt($now->modify('+2 week'));
        $peachSaveThePrincess->setOwner($peach);
        $peachSaveThePrincess->setDescription('Using a very big army he will came to save me');
        $peachSaveThePrincess->setWhy('Cause I will be the best dictator');
        $app->em->persist($peachSaveThePrincess);
        $app->em->flush();

        $eventManager->createEvent('goal.fork', $peachSaveThePrincess, $peach, $now);
        $this->processAll($app);

        $notifications = $notificationManager->getUnreadNotifications($mario);
        $found = false;
        $this->assertCount(2, $notifications);
        $notificationManager->setNotificationsAsRead($mario);
        $notifications = $notificationManager->getUnreadNotifications($mario);
        $this->assertCount(0, $notifications);

        $mario->setPreference('event_goal_cheer_email', false);
        
        $cheer1 = $goalRepo->cheer($marioSaveThePrincess, $luigi);
        $eventManager->createEvent('goal.cheer', $cheer1, $luigi);
        $this->processAll($app);

        $cheer2 = $goalRepo->cheer($marioSaveThePrincess, $peach);
        $eventManager->createEvent('goal.cheer', $cheer2, $peach);
        $this->processAll($app);

        $notifications = $notificationManager->getUnreadNotifications($mario);
        
        $this->assertCount(1, $notifications);

        foreach ($notifications as $notification) {
            break;
        }

        $events = $notification->getEvents();
        $event = $events[0];
        $subject = $event->getSubject();
        $this->assertTrue($subject instanceof \ABMundi\GoalBundle\Entity\Cheer);
        $this->assertEquals($marioSaveThePrincess->getName(), $subject->getGoal()->getName());
        $this->assertRegExp('/luigi|peach/', $event->getActor()->getUsername());
        $this->assertFalse($notification->isRead());

        $event = $events[1];
        $subject = $event->getSubject();
        $this->assertTrue($subject instanceof \ABMundi\GoalBundle\Entity\Cheer);
        $this->assertEquals($marioSaveThePrincess->getName(), $subject->getGoal()->getName());
        $this->assertRegExp('/luigi|peach/', $event->getActor()->getUsername());
        $this->assertFalse($notification->isRead());

        $notifications = $notificationManager->getNotifications($mario);
        $this->assertCount(3, $notifications);
    }
    
    /**
     * Test of generation of event
     */
    public function test_setNotificationsAsRead()
    {
        $app = $this->loadApp('mario', 'mario');

        $goalRepo = $app->em->getRepository('ABMundiGoalBundle:Goal');
        $userRepo = $app->em->getRepository('ABMundiUserBundle:User');

        $eventManager = $app->container->get('abm_notification.event.manager');
        $notificationManager = $app->container->get('abm_notification.notification.manager');

        $mario = $userRepo->findOneByUsername('mario');
        $luigi = $userRepo->findOneByUsername('luigi');
        $peach = $userRepo->findOneByUsername('peach');

        $marioSaveThePrincess = $goalRepo->findOneByName('Save the Princess');

        $luigiSaveThePrincess = $goalRepo->fork($marioSaveThePrincess);

        $now = new \DateTime;

        $luigiSaveThePrincess->setName('Help Mario to save the princess');
        $luigiSaveThePrincess->setOwner($luigi);
        $luigiSaveThePrincess->setExpireAt($now->modify('+1 month'));
        $luigiSaveThePrincess->setDescription('I want to save the princess too');
        $luigiSaveThePrincess->setWhy('Cause I don\'t like Bowser');
        $app->em->persist($luigiSaveThePrincess);
        $app->em->flush();

        $eventManager->createEvent('goal.fork', $luigiSaveThePrincess, $luigi, $now);
        $this->processAll($app);
        
        $cheer1 = $goalRepo->cheer($marioSaveThePrincess, $luigi);
        $eventManager->createEvent('goal.cheer', $cheer1, $luigi);
        $this->processAll($app);

        $cheer2 = $goalRepo->cheer($marioSaveThePrincess, $peach);
        $eventManager->createEvent('goal.cheer', $cheer2, $peach);
        $this->processAll($app);
        
        $luigi->follow($mario);
        $eventManager->createEvent('user.follow', $mario, $luigi);
        $this->processAll($app);

        $notifications = $notificationManager->getUnreadNotifications($mario);
        $this->assertCount(3, $notifications);
        
        $notificationManager->setNotificationsAsRead($mario, $marioSaveThePrincess);
        
        $notifications = $notificationManager->getUnreadNotifications($mario);
        $this->assertCount(1, $notifications);
    }

    /**
     * Test of SendReminderEmail
     */
    public function test_sendReminderEmail()
    {
        $app = $this->loadApp();
        
        $crawler = $app->client->request('GET', $app->router->generate('homepage'));
        $this->isSuccessful($app->client->getResponse(), true);

        $userRepo = $app->em->getRepository('ABMundiUserBundle:User');
        $nm = $app->container->get('abm_notification.notification.manager');

        $pisolo = $userRepo->findOneByUsername('pisolo');
        
        $this->markTestIncomplete(
          'You cant use some Twig features in shell'
        );
 
        $nm->sendReminderEmail($pisolo);
        $this->isSentAnEmail($app->client, 'pisolo@abmundi.com', 'You ar late on some goals');
    }

    /**
     * When it is generated an event with no entity subject it throws an exception 
     * 
     * @expectedException        Doctrine\ORM\Mapping\MappingException
     * @expectedExceptionMessage Class stdClass is not a valid entity or mapped super class.
     */
    public function test_subject_must_be_an_entity()
    {
        $app = $this->loadApp();

        $eventManager = $app->container->get('abm_notification.event.manager');
        $app->em = $app->container->get('doctrine.orm.entity_manager');
        $userRepo = $app->em->getRepository('ABMundiUserBundle:User');
        $mario = $userRepo->findOneByUsername('mario');

        $subject = new \stdClass();

        $eventManager->createEvent('undefined.event_key', $subject, $mario);
    }

    /**
     * When it is generated an event with a not defined event_key it throw an exception 
     * 
     * @expectedException        ABMundi\NotificationBundle\Entity\Event\UndefinedEventKeyException
     * @expectedExceptionMessage EventKey "undefined.event_key" is not defined
     */
    public function test_event_key_not_defined_exception()
    {
        $app = $this->loadApp();

        $eventManager = $app->container->get('abm_notification.event.manager');
        $notificationManager = $app->container->get('abm_notification.notification.manager');
        $app->em = $app->container->get('doctrine.orm.entity_manager');
        $userRepo = $app->em->getRepository('ABMundiUserBundle:User');
        $mario = $userRepo->findOneByUsername('mario');

        $eventManager->createEvent('undefined.event_key', $mario, $mario);
        $eventManager->processEvents();
    }


}
