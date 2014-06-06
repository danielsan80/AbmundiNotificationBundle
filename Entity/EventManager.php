<?php

namespace ABMundi\NotificationBundle\Entity;

use ABMundi\NotificationBundle\Entity\Event\EventReaderFactory;
use Doctrine\ORM\EntityManager;
use ABMundi\UserBundle\Entity\User;

/**
 * Event Manager
 */
class EventManager
{
    protected $eventReaderFactory;
    protected $em;
    protected $pheanstalk;
    
    protected $eventRepository;
    
    /**
     * Constructor
     * 
     * @param ContainerInterface $container Container
     */
    public function __construct()
    {
        $this->eventReaderFactory = new EventReaderFactory();
    }
    
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
        $this->eventRepository = $em->getRepository('ABMundi\NotificationBundle\Entity\Event');
    }
    
    public function setPheanstalk($pheanstalk)
    {
        $this->pheanstalk = $pheanstalk;
    }
    
    public function createEvent($key, $subject, User $actor = null, \DateTime $createdAt = null)
    {
        
        $event = new Event();
        $event->setKey($key);
        $event->setSubject($subject);
        $event->setActor($actor);
        if ($createdAt) {
            $event->setCreatedAt($createdAt);
        }
        $this->em->persist($event);
        $this->em->flush();
        $this->em->refresh($event);
        
        $this->pheanstalk
          ->useTube('events')
          ->put($event->getId());
        
        return $event;
    }
    
    public function processEvents($limit = null)
    {
        $processed = array();
        $i = 0;
        $this->pheanstalk->useTube('events');
        while (true) {
            $stats = $this->pheanstalk->statsTube('events');
            $n = (int)$stats['current-jobs-ready'];
            if (!$n || ($limit && $i++>$limit)) {
                break;
            }
            
            $job = $this->pheanstalk
            ->watch('events')
            ->ignore('default')
            ->reserve();
            
            $id = $job->getData();
            $this->pheanstalk->delete($job);

            $event = $this->eventRepository->findOneById($id);
            if ($event) {
                $processed[] = $event;
                $this->forwardEvent($event);
            }
        }

        return $processed;
    }
    
    public function forwardEvent(Event $event)
    {
        $eventReader = $this->getEventReader($event->getKey());
        $channels = $eventReader->getChannels($event);
        foreach($channels as $channel) {
            $this->pheanstalk
                ->useTube('events_'.$channel)
                ->put($event->getId());
        }
    }
    
    /**
     * Shortcut to EventReaderFactory::createEventReader()
     *
     * @param string $key
     * 
     * @return EventReader 
     */
    public function getEventReader($key)
    {
        return $this->eventReaderFactory->createEventReader($key);
    }
    
    /**
     * Update the subject object from class and ids of the specified event
     *
     * @param Event $event     The event to update
     * @param bool  $reference in reference mode?
     */
    public function updateSubjectObject(Event $event, $reference = true)
    {
        $this->eventRepository->updateSubjectObject($event, $reference);
    }

    /**
     * Update the subject class and ids from the subject object of the specified event
     * 
     * @param Event $event 
     */
    public function updateSubjectStrings(Event $event)
    {
        $this->eventRepository->updateSubjectStrings($event);
    }
    
}