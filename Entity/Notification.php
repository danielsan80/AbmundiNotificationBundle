<?php

namespace ABMundi\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use ABMundi\UserBundle\Entity\User;

/**
 * ABMundi\NotificationBundle\Entity\Notification
 *
 * @ORM\Entity(repositoryClass="ABMundi\NotificationBundle\Entity\NotificationRepository")
 * @ORM\Table(name="abm_notification")
 */
class Notification
{
    
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $title
     * 
     * @ORM\Column(name="channel", type="string", length=255)
     */
    private $channel='main';
    
    /**
     * @var \ABMundi\UserBundle\Entity\User
     * 
     * @ORM\ManyToOne(targetEntity="ABMundi\UserBundle\Entity\User")
     */
    protected $user;

    /**
     * @var Events
     * 
     * @ORM\ManyToMany(targetEntity="Event", mappedBy="notifications" )
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $events;

    /**
     * @var datetime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var datetime $updatedAt
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * Is this notification sendable by email?
     * 
     * @var boolean $sendable
     *
     * @ORM\Column(name="sendable", type="boolean")
     */
    private $sendable = false;
    
    /**
     * If the notification did not read by the user for a long time it sent by email.
     * Is it sent?
     * 
     * @var datetime $sentAt
     *
     * @ORM\Column(name="sent_at", type="datetime", nullable="true")
     */
    private $sentAt;

    /**
     * Is this notification read by the user?
     * 
     * @var datetime $readAt 
     *
     * @ORM\Column(name="read_at", type="datetime", nullable="true")
     */
    private $readAt;

    /**
     * Constructor 
     */
    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set channel
     *
     * @param string $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * Get channel
     *
     * @return string 
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set createdAt
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return datetime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get updatedAt
     *
     * @return datetime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set sendable
     *
     * @param boolean $bool
     */
    public function setSendable($bool=true)
    {
        $this->sendable = $bool;
    }

    /**
     * Get sentAt
     *
     * @return datetime 
     */
    public function isSendable()
    {
        $user = $this->getUser();
        $preferences = $user->getPreferences();
        $eventKey = strtr($this->getEventKey(), array('.' => '_'));
        $key = 'event_'.$eventKey.'_email';
        
        if (isset($preferences[$key])) {
            $this->setSendable($preferences[$key]);
        } else {
            $this->setSendable(false);
        }

        return $this->sendable;
    }
    
    /**
     * Set sentAt
     *
     * @param datetime $sentAt
     */
    public function setSentAt($sentAt)
    {
        $this->sentAt = $sentAt;
    }

    /**
     * Get sentAt
     *
     * @return datetime 
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * Set readAt
     *
     * @param datetime $readAt
     */
    public function setReadAt($readAt)
    {
        $this->readAt = $readAt;
    }

    /**
     * Get readAt
     *
     * @return datetime 
     */
    public function getReadAt()
    {
        return $this->readAt;
    }

    /**
     * Set Events
     *
     * @param ArrayCollection $events
     */
    public function setEvents(ArrayCollection $events)
    {
        $this->events = $events;
    }

    /**
     * Get related Event
     *
     * @return Event 
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Get last Event
     *
     * @return Event 
     */
    public function getLastEvent()
    {
        return $this->events->first();
    }

    /**
     * Set related Event
     *
     * @param Event $event
     */
    public function addEvent(Event $event)
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->addNotification($this);
        }
    }

    /**
     * Set recipient User
     *
     * @param \ABMundi\UserBundle\Entity\User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get recipient User
     *
     * @return \ABMundi\UserBundle\Entity\User $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set notification as read
     */
    public function setAsRead()
    {
        $this->readAt = new \DateTime();
    }

    /**
     * Set notification as sent
     */
    public function setAsSent()
    {
        $this->sentAt = new \DateTime();
    }

    /**
     * Is a read notification?
     * 
     * @return bool
     */
    public function isRead()
    {
        return (bool) $this->readAt;
    }

    /**
     * Is a sent notification?
     * 
     * @return bool
     */
    public function isSent()
    {
        return (bool) $this->sentAt;
    }

    /**
     * Get Actors of events
     * @return array 
     */
    public function getActors()
    {
        $actors = array();
        foreach ($this->events as $i => $event) {
            $actor = $event->getActor();
            $key = '';
            if ($actor) {
                $key = $actor->getUsername();
            }
            if (!isset($actors[$key])) {
                $actors[$key]['times'] = 0;
            }
            $actors[$key]['times'] += 1;
            $actors[$key]['user'] = $actor;
        }

        return $actors;
    }

    /**
     * get the key of the notification events
     * 
     * @return null 
     */
    public function getEventKey()
    {
        foreach ($this->events as $event) {
            
            return $event->getKey();
        }

        return null;
    }
}