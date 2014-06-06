<?php

namespace ABMundi\NotificationBundle\Entity\Event\EventReader;
use ABMundi\NotificationBundle\Entity\Event\EventReader;
use Doctrine\Common\Collections\ArrayCollection;
use ABMundi\NotificationBundle\Entity\Event;
use ABMundi\NotificationBundle\Entity\Notification;

use ABMundi\NotificationBundle\Model\Faker;

/**
 * EventReader for key goal.cheer 
 */
class GoalCheerEventReader extends EventReader
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
        return $event->getSubject()->getGoal();
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
        $recipients->add($event->getSubject()->getGoal()->getOwner());
        
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
        $world = $faker->generateGoal('Conquer the world', $mario);
        $cheer = $faker->userCheersGoal($luigi, $world);
        $e1 = $faker->createEvent('goal.cheer', $cheer, $luigi);
        $cheer = $faker->userCheersGoal($luigi, $world);
        $e2 = $faker->createEvent('goal.cheer', $cheer, $luigi);
        $notification = $faker->generateNotification($mario, array($e1, $e2));
        
        return $notification;
    }
    
    public function getAntifloodInterval()
    {
       return '5 minutes';
    }
    
    public function getFacebookData(Notification $notification)
    {
        $event = $notification->getLastEvent();
        $goal = $event->getSubject()->getGoal();
        return array(
            'message' => 'I cheered a '.$goal->getOwner()->getName().' goal:',
            'object' => 'goal',
            'url' => array(
                'route' => 'goal_show',
                'args' => array('username' => $goal->getOwner(),'slug' => $goal->getSlug())
            ),
            'action' => 'cheer',
        );
    }
}