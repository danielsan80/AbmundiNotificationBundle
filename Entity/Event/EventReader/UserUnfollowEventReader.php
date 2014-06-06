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
class UserUnfollowEventReader extends EventReader
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
        return null;
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
        
        return $recipients;
    }
    
    public function getChannels(Event $event)
    {
        return array('facebook_cmd');
    }
    
}