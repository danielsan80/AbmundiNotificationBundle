<?php

namespace ABMundi\NotificationBundle\Entity\Event\EventReader;
use ABMundi\NotificationBundle\Entity\Event\EventReader;
use Doctrine\Common\Collections\ArrayCollection;
use ABMundi\NotificationBundle\Entity\Event;
use ABMundi\NotificationBundle\Entity\Notification;

use ABMundi\NotificationBundle\Model\Faker;
/**
 * EventReader for key user.follow 
 */
class UserFollowEventReader extends EventReader
{
    /**
     * Hook implementation
     * 
     * @param Event $event
     * 
     * @return Goal 
     */
    public function getMainSubjectByEvent(Event $event)
    {
        return $event->getSubject();
    }

    /**
     * Hook implementation
     * 
     * @param Event $event
     * 
     * @return UserInterface 
     */
    public function getNotificationRecipient(Event $event, $channel='main')
    {
        return $event->getSubject();
    }
    
    /**
     * Hook implementation
     * 
     * @param Event $event
     * 
     * @return ArrayCollection
     */
    public function selectNotificationRecipients(Event $event)
    {
        $recipients = new ArrayCollection();
        $recipients->add($event->getSubject());
        
        return $recipients;
    }
    
    /**
     * Hook implementation
     * 
     * @param Event $event
     * 
     * @return UserInterface 
     */
    public function getFakeNotification()
    {
        $faker = new Faker();
        
        $mario = $faker->generateUser('mario');
        $luigi = $faker->generateUser('luigi');
        $peach = $faker->generateUser('peach');
        $world = $faker->generateGoal('Conquer the world', $luigi);
        $country = $faker->generateGoal('Conquer my country', $luigi);
        $country->setAsClosed();
        $faker->userFollowsUser($peach, $luigi);
        $faker->userFollowsUser($luigi, $mario);
        $faker->userFollowsUser($peach, $mario);
        $e1 = $faker->createEvent('user.follow', $mario, $luigi);
        $e2 = $faker->createEvent('user.follow', $mario, $peach);
        $notification = $faker->generateNotification($mario, array($e1, $e2));
        
        return $notification;
    }
    
    public function getFacebookData(Notification $notification)
    {
        $event = $notification->getLastEvent();
        $user = $event->getSubject();
        return array(
            'message' => 'I started to follow ',
            'object' => 'profile',
            'url' => array(
                'route' => 'user_public_profile',
                'args' => array('username' => $user->getUsername())
            ),
            'action' => 'og.follows',
        );
    }
    
}