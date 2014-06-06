<?php

namespace ABMundi\NotificationBundle\Entity\Event\EventReader;
use ABMundi\NotificationBundle\Entity\Event\EventReader;
use ABMundi\NotificationBundle\Entity\Event;
use ABMundi\NotificationBundle\Entity\Notification;
use Doctrine\Common\Collections\ArrayCollection;

use ABMundi\NotificationBundle\Model\Faker;

/**
 * EventReader for key goal.close 
 */
class GoalCloseEventReader extends EventReader
{
    /**
     * hook implementation
     * 
     * @param Event $event
     * 
     * @return Object 
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
        $goal = $event->getSubject();
        if ($goal->isPrivate()) {
            return new ArrayCollection;
        }

        return $goal->getOwner()->getFollowers();
    }

    /**
     * hook implementation
     * 
     * @param Event                 $event         Event
     * @param NotificationColection $notifications Notifications
     * 
     * @return null 
     */
    public function getJoinableNotification(Event $event, $notifications)
    {
        return null;
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
        $cheer = $faker->userFollowsUser($luigi, $mario);
        $event = $faker->createEvent('goal.close', $world, $mario);
        $notification = $faker->generateNotification($luigi, array($event));
        
        return $notification;
    }
    
    public function getFacebookData(Notification $notification)
    {
        $event = $notification->getLastEvent();
        $goal = $event->getSubject();
        return array(
            'message' => 'I closed my goal ('.$goal->getPercentage().'%): '. $goal->getLearning(),
            'object' => 'goal',
            'url' => array(
                'route' => 'goal_show',
                'args' => array('username' => $goal->getOwner(),'slug' => $goal->getSlug())
            ),
            'action' => 'close',
        );
    }

}