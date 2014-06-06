<?php

namespace ABMundi\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use \DateTime;

/**
 * ABMundi\NotificationBundle\Entity\Event
 *
 * @ORM\Entity(repositoryClass="ABMundi\NotificationBundle\Entity\EventRepository")
 * @ORM\Table(name="abm_event")
 * @ORM\HasLifecycleCallbacks
 */
class Event
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
     * @var string $key
     *
     * @ORM\Column(name="event_key", type="string", length=255)
     */
    private $key;

    /**
     * @var date $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime", nullable="true")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var \ABMundi\UserBundle\Entity\User
     * 
     * @ORM\ManyToOne(targetEntity="ABMundi\UserBundle\Entity\User")
     */
    protected $actor;

    /**
     * A temporary subject object variable. Used by Doctrine ORM listener
     * to conver it into a FQCN/identifiers.
     *
     * @var mixed
     */
    protected $subject;

    /**
     * The fully qualified class of the subject object.
     *
     * @var string $subjectClass
     * 
     * @ORM\Column(name="subject_class", type="string", length=1000, nullable=true)
     */
    protected $subjectClass;

    /**
     * An array of identifiers used to identify the subject object.
     *
     * @var array
     * 
     * @ORM\Column(name="subject_identifiers", type="array", nullable=true)
     */
    protected $subjectIdentifiers;

    /**
     * @var string $verb
     * 
     * @ORM\Column(name="verb", type="string", length=255, nullable=true)
     */
    private $verb;

    /**
     * @var Notification $notification
     * 
     * @ORM\ManyToMany(targetEntity="Notification", inversedBy="events")
     * @ORM\JoinTable(name="abm_event_notification")
     */
    protected $notifications;


    /**
     * Constructor 
     */
    public function __construct()
    {
        $this->notifications = new ArrayCollection();
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
     * Set event key
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Get event key
     *
     * @return string 
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set created at
     *
     * @param \DateTime $now
     */
    public function setCreatedAt(DateTime $now)
    {
        $this->createdAt = $now;
    }

    /**
     * Return created at
     * 
     * @return boolean
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set actor
     *
     * @param object $actor
     */
    public function setActor($actor)
    {
        $this->actor = $actor;
    }

    /**
     * Get actor
     *
     * @return object 
     */
    public function getActor()
    {
        return $this->actor;
    }

    /**
     * Set subject
     *  
     * @param mixed $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Get subject
     *
     * @return object 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set Subject Class
     * 
     * @param string $subjectClass
     */
    public function setSubjectClass($subjectClass)
    {
        $this->subjectClass = $subjectClass;
    }

    /**
     * Get Subject Class
     * 
     * @return string
     */
    public function getSubjectClass()
    {
        return $this->subjectClass;
    }

    /**
     * Set Subject Identifires array
     * 
     * @param array $subjectIdentifiers
     */
    public function setSubjectIdentifiers(array $subjectIdentifiers = null)
    {
        $this->subjectIdentifiers = $subjectIdentifiers;
    }

    /**
     * Get Subject Identifiers array
     * 
     * @return array
     */
    public function getSubjectIdentifiers()
    {
        return $this->subjectIdentifiers;
    }

    /**
     * Set verb
     *
     * @param string $verb
     */
    public function setVerb($verb)
    {
        $this->verb = $verb;
    }

    /**
     * Get verb
     *
     * @return string 
     */
    public function getVerb()
    {
        return $this->verb;
    }

    /**
     *  Set Notifications
     *
     * @param ArrayCollection $notifications
     */
    public function setNotifications(ArrayCollection $notifications)
    {
        $this->notifications = $notifications;
    }

    /**
     * Get notifications
     *
     * @return ArrayCollection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Get first notification
     *
     * @return Notirfication
     */
    public function getNotification()
    {
        return $this->notifications[0];
    }

    /**
     * Add Notification
     *
     * @param Notification $notification
     */
    public function addNotification(Notification $notification)
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->addEvent($this);
        }
    }

}