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
class QuestionCreateEventReader extends EventReader
{

    /**
     * Hook implementation
     * 
     * @param Event $event
     * 
     * @return ArrayCollection
     */
    public function selectNotificationRecipients(Event $event)
    {
        $question = $event->getSubject();
        $goal = $question->getGoal();
        if ($goal->isPrivate()) {
            return new ArrayCollection;
        }

        $recipients = $goal->getOwner()->getFollowers();
        if (!$recipients->contains($goal->getOwner())) {
            $recipients->add($goal->getOwner());
        }
        if (!$recipients->contains($question->getOwner())) {
            $recipients->add($question->getOwner());
        }
        if ($recipients->contains($event->getActor())) {
            $recipients->removeElement($event->getActor());
        }
        
        return $recipients;
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
        $question = $faker->generateQuestion('How can I conquer Japan?', $world);
        $e = $faker->createEvent('question.create', $question, $mario);
        $notification = $faker->generateNotification($luigi, array($e));
        
        return $notification;
    }
    
    public function getFacebookData(Notification $notification)
    {
        $event = $notification->getLastEvent();
        $suggestion = $event->getSubject();
        return array(
            'message' => 'I have a question for you:',
            'object' => 'abquestion',
            'url' => array(
                'route' => 'question_show',
                'args' => array('id' => $suggestion->getId())
            ),
            'action' => 'ask',
        );
    }

}