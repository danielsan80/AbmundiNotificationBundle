<?php

namespace ABMundi\NotificationBundle\Entity\Event\EventReader;
use ABMundi\NotificationBundle\Entity\Event\EventReader;
use Doctrine\Common\Collections\ArrayCollection;
use ABMundi\NotificationBundle\Entity\Notification;
use ABMundi\NotificationBundle\Entity\Event;

use ABMundi\NotificationBundle\Model\Faker;

/**
 * EventReader for key goal.congrats 
 */
class GoalCongratsEventReader extends EventReader
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
     * @return ArrayCollection
     */
    public function selectNotificationRecipients(Event $event)
    {
        $recipients = new ArrayCollection();
        $recipients->add($event->getSubject()->getOwner());
        
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
        $world = $faker->generateGoal('Conquer the world', $mario);
        $congrats1 = $faker->userCongratsGoal($luigi, $world);
        $e1 = $faker->createEvent('goal.congrats', $world, $luigi);
        $congrats1 = $faker->userCongratsGoal($peach, $world);
        $notification = $faker->generateNotification($mario, array($e1));
        
        return $notification;
    }
    
    public function getFacebookData(Notification $notification)
    {
        $event = $notification->getLastEvent();
        $goal = $event->getSubject();
        return array(
            'message' => 'I congratulated '.$goal->getOwner()->getName().' for his goal:',
            'object' => 'goal',
            'url' => array(
                'route' => 'goal_show',
                'args' => array('username' => $goal->getOwner(),'slug' => $goal->getSlug())
            ),
            'action' => 'congratulate',
        );
    }
    
}