<?php

namespace ABMundi\NotificationBundle\Entity;
use ABMundi\NotificationBundle\Entity\Event\EventReaderFactory;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\TwigBundle\TwigEngine;

use ABMundi\NotificationBundle\Model\Faker;
use ABMundi\UserBundle\Entity\User;

/**
 * Event Manager
 */
class NotificationManager
{
    protected $eventReaderFactory;
    
    protected $pheanstalk;
    
    protected $em;
    protected $eventRepository;
    protected $notificationRepository;
    
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
        $this->notificationRepository = $em->getRepository('ABMundi\NotificationBundle\Entity\Notification');
    }
    
    public function setPheanstalk($pheanstalk)
    {
        $this->pheanstalk = $pheanstalk;
    }
    
    public function processMainChannelEvents($limit = null)
    {
        $processed = array();
        $i = 0;
        $this->pheanstalk->useTube('events_main');
        while (true) {
            $stats = $this->pheanstalk->statsTube('events_main');
            $n = (int)$stats['current-jobs-ready'];
            if (!$n || ($limit && $i++>$limit)) {
                break;
            }
            
            $job = $this->pheanstalk
            ->watch('events_main')
            ->ignore('default')
            ->reserve();
            
            $id = $job->getData();
            $this->pheanstalk->delete($job);

            $event = $this->eventRepository->findOneById($id);            
            if ($event) {
                $processed[] = $event;
                $this->generateMainNotifications($event);
            }
        }
        
        return $processed;
    }
  
    public function processEmailChannelEvents($limit = null)
    {
        $processed = array();
        $i = 0;
        $this->pheanstalk->useTube('events_email');
        while (true) {
            $stats = $this->pheanstalk->statsTube('events_email');
            $n = (int)$stats['current-jobs-ready'];
            if (!$n || ($limit && $i++>$limit)) {
                break;
            }
            
            $job = $this->pheanstalk
            ->watch('events_email')
            ->ignore('default')
            ->reserve();
            
            $id = $job->getData();
            $this->pheanstalk->delete($job);

            $event = $this->eventRepository->findOneById($id);
            if ($event) {
                $processed[] = $event;
                $this->generateEmailNotifications($event);
            }
        }

        return $processed;
    }

    public function processFacebookChannelEvents($limit = null)
    {
        $processed = array();
        $i = 0;
        $this->pheanstalk->useTube('events_facebook');
        while (true) {
            $stats = $this->pheanstalk->statsTube('events_facebook');
            $n = (int)$stats['current-jobs-ready'];
            if (!$n || ($limit && $i++>$limit)) {
                break;
            }
            
            $job = $this->pheanstalk
            ->watch('events_facebook')
            ->ignore('default')
            ->reserve();
            
            $id = $job->getData();
            $this->pheanstalk->delete($job);

            $event = $this->eventRepository->findOneById($id);
            if ($event) {
                $processed[] = $event;
                $this->generateFacebookNotification($event);
            }
        }
        
        return $processed;
    }
    

    
    /**
     * Generate a notification for the specified event
     * 
     * @param Event $event
     * 
     * @return \ABMundi\NotificationBundle\Entity\Notification 
     */
    public function generateMainNotifications(Event $event)
    {
        $eventReader = $this->getEventReader($event->getKey());
        $recipients = $eventReader->getNotificationRecipients($event, 'main');
        $notifications = new ArrayCollection();
        foreach ($recipients as $recipient) {
            $unreadNotifications = $this->notificationRepository->getUnreadNotifications($recipient, 'main');
            $notification = $eventReader->getJoinableNotification($event, $unreadNotifications);
            if (!$notification) {
                $notification = new Notification();
                $notification->setChannel('main');
                $notification->setUser($recipient);
            } else {
                $notification->setCreatedAt(new \DateTime());
            }
            
            $notification->addEvent($event);
            
            $this->em->persist($notification);
            $notifications->add($notification);
        }
        $this->em->persist($event);
        $this->em->flush();

        return $notifications;
    }

    /**
     * Generate a notification for the specified event
     * 
     * @param Event $event
     * 
     * @return \ABMundi\NotificationBundle\Entity\Notification 
     */
    public function generateEmailNotifications(Event $event)
    {
        $eventReader = $this->getEventReader($event->getKey());
        $recipients = $eventReader->getNotificationRecipients($event, 'email');
        $notifications = new ArrayCollection();
        foreach ($recipients as $recipient) {
            
            $unsentNotifications = $this->notificationRepository->getUnsentNotifications($recipient, 'email');
            $notification = $eventReader->getJoinableNotification($event, $unsentNotifications);
            if (!$notification) {
                $notification = new Notification();
                $notification->setChannel('email');
                $notification->setUser($recipient);
            } else {
                $notification->setCreatedAt(new \DateTime());
            }
            
            $notification->addEvent($event);
            
            $this->em->persist($notification);
            $this->em->persist($event);
            $this->em->flush();
            $notifications->add($notification);

            $this->pheanstalk
                ->useTube('notifications_email')
                ->put($notification->getId());
        }
       
        return $notifications;
    }
    
    /**
     * Generate a notification for the specified event
     * 
     * @param Event $event
     * 
     * @return \ABMundi\NotificationBundle\Entity\Notification 
     */
    public function generateFacebookNotification(Event $event)
    {
        $eventReader = $this->getEventReader($event->getKey());
        $recipients = $eventReader->getNotificationRecipients($event, 'facebook');
        if (!$recipients->count()) {
            return;
        }
        $user = $recipients->first();
        
        $lastNotifications = $this->notificationRepository->getLastNotifications($user, 'facebook', $event->getKey(), $eventReader->getAntifloodInterval());
        if ($eventReader->inAntifloodInterval($event, $lastNotifications)) {
            return;
        }
        
//        $unsentNotifications = $this->notificationRepository->getUnsentNotifications($user, 'facebook');
//        $notification = $eventReader->getJoinableNotification($event, $unsentNotifications);
        $notification = null;
        
        if (!$notification) {
            $notification = new Notification();
            $notification->setChannel('facebook');
            $notification->setUser($user);
        } else {
            $notification->setCreatedAt(new \DateTime());
        }
        
        $notification->addEvent($event);

        $this->em->persist($notification);
        $this->em->persist($event);
        $this->em->flush();

        $this->pheanstalk
            ->useTube('notifications_facebook')
            ->put($notification->getId());

        return $notification;
    }
    
//    private function antiFloodCheck($notification)
//    {
//        $minutes = 5;
//        $now = new \DateTime();
//        $lastNotificationSent = $this->notificationRepository->getLastSentNotification($notification->getUser(), 'main', $notification->getEventKey(), $minutes.' minutes');
//        if ($lastNotificationSent) {
//            $diff = $now->diff($lastNotificationSent->getSentAt());
//            if ($diff->format('%i') < $minutes) {
//        
//                return false;
//            }
//        }
//        
//        return true;
//    }

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
     * Get events by User
     * 
     * @param UserInterface $user
     * 
     * @return ArrayCollection
     */
    public function getNotifications(UserInterface $user)
    {
        $notifications = $this->notificationRepository->getNotifications($user);

        return $notifications;
    }

    /**
     * Get only the latest notifications of the specified user
     * 
     * @param UserInterface $user  User
     * @param integer       $limit Limit of results
     * 
     * @return ABMundiNotificationBundle:Notification 
     */
    public function getLatestNotifications(UserInterface $user, $limit)
    {
        $notifications = $this->notificationRepository->getLatestNotifications($user, $limit);

        return $notifications;
    }

    /**
     * Set all notification of the specified user as read
     *
     * @param UserInterface $user 
     */
    public function setNotificationsAsRead(UserInterface $user, $subject=null)
    {
        if (!$subject) {
            $this->notificationRepository->setNotificationsAsRead($user);
            
            return;
        }
        
        $notifications = $this->notificationRepository->getUnreadNotifications($user, 'main');
        foreach($notifications as $notification) {
            $eventReader = $this->getEventReader($notification->getEventKey());
            $notificationSubject = $eventReader->getMainSubjectByNotification($notification);
            if ($notificationSubject == $subject) {
                $notification->setAsRead();
                $this->em->persist($notification);
            }
        }
        $this->em->flush();
    }
    
    public function getUnreadNotifications($user, $channel='main')
    {
        return $this->notificationRepository->getUnreadNotifications($user, $channel);
    }

}