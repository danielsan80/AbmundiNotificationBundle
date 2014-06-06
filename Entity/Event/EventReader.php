<?php

namespace ABMundi\NotificationBundle\Entity\Event;
use ABMundi\NotificationBundle\Entity\Notification;
use ABMundi\NotificationBundle\Entity\Event;
use Doctrine\Common\Collections\ArrayCollection;
use \Exception;

/**
 * Abstract Event Reader
 * Must be implemented for each eventKey you want to use
 */
abstract class EventReader
{
    /**
     * Get the true main subject of the events of the specified notification
     * 
     * @param Notification $notification
     * 
     * @return Object or null 
     */
    public function getMainSubjectByNotification(Notification $notification)
    {
        foreach ($notification->getEvents() as $event) {

            return $this->getMainSubjectByEvent($event);
        }

        return null;
    }

    /**
     * Get the true main subject of the specified event
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
     * Get recipients for a generic notification of this event
     * 
     * @param Event $event
     * 
     * @return ArrayCollection 
     */
    protected function selectNotificationRecipients(Event $event)
    {
        throw new Exception('There is no implementation for this method');
    }

    /**
     * Get recipients for a notification of this event for a given channel
     * 
     * @param Event $event
     * 
     * @return ArrayCollection 
     */
    public function getNotificationRecipients(Event $event, $channel='main')
    {
        if ($channel=='facebook') {
            $actor = $event->getActor();
            $recipients = new ArrayCollection();
            if ($this->getUserPreference($actor, $event->getKey(), 'facebook')) {
                $recipients->add($actor);
            }

            return $recipients;
        }
        
        $recipients = $this->selectNotificationRecipients($event);
        
        if ($channel=='email') {
            $recipients = $this->filterEmailRecipients($recipients, $event->getKey());
        }

        return $recipients;
    }
    
    protected function filterEmailRecipients($recipients, $eventKey)
    {
        $result = new ArrayCollection();
        
        foreach ($recipients as $recipient) {
            if ($this->getUserPreference($recipient, $eventKey, 'email')) {
                $result->add($recipient);
            }
        }

        return $result;
    }
    
    private function getUserPreference($user, $eventKey, $channel)
    {
        $eventKey = strtr($eventKey, array('.' => '_'));
        $key = 'event_'.$eventKey.'_'.$channel;

        return $user->getPreference($key);
    }

    /**
     * If exists get the notification joinable with this event
     *
     * @param Event                  $event         Event
     * @param NotificationCollection $notifications Notifications
     * 
     * @return Notification or null 
     */
    public function getJoinableNotification(Event $event, $notifications)
    {
        $return = null;
        foreach ($notifications as $notification) {
            if ($this->isJoinable($event, $notification->getLastEvent())) {
                if ($return && ($return->getUpdatedAt() > $notification->getUpdatedAt())) {
                    continue;
                }
                $return = $notification;
            }
        }

        return $return;
    }
    
    public function isJoinable(Event $event1, Event $event2)
    {
        if ($event1->getKey() != $event2->getKey()) {
            return false;
        }
        
        $subject1 = $this->getMainSubjectByEvent($event1);
        $subject2 = $this->getMainSubjectByEvent($event2);

        return $subject1->getId() == $subject2->getId();
    }    
    
    public function inAntifloodInterval(Event $event, $notifications)
    {
        $antifloodTime = new \DateTime('- '.$this->getAntifloodInterval());
        
        foreach($notifications as $notification) {
            if (!$this->isJoinable($event, $notification->getLastEvent())) {
                continue;
            }
            if ( $notification->getCreatedAt() < $antifloodTime ) {
                continue;
            }

            return true;
        }
        
        return false;
    }
    
    public function getChannels(Event $event)
    {
        return array('main', 'email', 'facebook');
    }
    
    public function getFakeNotification()
    {
       return null; 
    }
    
    public function getAntifloodInterval()
    {
       return '0 seconds'; 
    }
    
    public function getFacebookData(Notification $notification)
    {
       return array(
            'message' => 'Main message of the post',
            'name' => 'Link label',
            'link' => array(
                    'route' => 'route',
                    'args' => array()
                ),
            'caption' => 'Text under the link',
            'description' => 'Description',
            'picture' => array(
                    'route' => 'route',
                    'args' => array()
                ),
        );
    }
}