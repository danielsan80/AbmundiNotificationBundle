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
class GoalNoteEventReader extends EventReader
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
        $goal = $event->getSubject()->getGoal();
        if ($goal->isPrivate()) {
            return new ArrayCollection;
        }

        return $goal->getOwner()->getFollowers();
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
        $note = $faker->generateNote($world, 20);
        $e = $faker->createEvent('goal.note', $note, $mario);
        $notification = $faker->generateNotification($luigi, array($e));
        
        return $notification;
    }
    
    public function getFacebookData(Notification $notification)
    {
        $event = $notification->getLastEvent();
        $note = $event->getSubject();
        $goal = $note->getGoal();

        return array(
            'message' => 'I reached '.$note->getPercentage().'%: '. $note->getTitle(),
            'object' => 'goal',
            'url' => array(
                'route' => 'goal_show',
                'args' => array('username' => $goal->getOwner(),'slug' => $goal->getSlug())
            ),
            'action' => 'note',
        );
    }
}