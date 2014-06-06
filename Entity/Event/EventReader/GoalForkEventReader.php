<?php

namespace ABMundi\NotificationBundle\Entity\Event\EventReader;
use ABMundi\NotificationBundle\Entity\Event\EventReader;
use Doctrine\Common\Collections\ArrayCollection;
use ABMundi\NotificationBundle\Entity\Event;

/**
 * EventReader for key goal.fork 
 */
class GoalForkEventReader extends EventReader
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
        return $event->getSubject()->getParent();
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
        $recipients->add($event->getSubject()->getParent()->getOwner());
        
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

}