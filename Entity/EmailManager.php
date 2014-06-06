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

use Pheanstalk\Pheanstalk;

/**
 * Email Manager
 */
class EmailManager
{
    protected $eventReaderFactory;
    
    protected $pheanstalk;
    
    protected $em;
    protected $eventRepository;
    protected $notificationRepository;
    
    protected $templating;
    protected $mailer;

    protected $fromEmail;
    protected $fromName;

    protected $debugEmail;
    
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

    public function setTemplating(TwigEngine $templating)
    {
        $this->templating = $templating;
    }
    
    public function setMailer(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function setFromEmail($email, $name)
    {
        $this->fromEmail = $email;
        $this->fromName = $name;
    }
    
    public function setDebugEmail($email, $name)
    {
        $this->debugEmail = $email;
        $this->debugName = $name;
    }
    
   
    public function processNotifications($limit = null)
    {
        $processed = array();
        $i = 0;
        $this->pheanstalk->useTube('notifications_email');
        while (true) {
            $stats = $this->pheanstalk->statsTube('notifications_email');
            $n = (int)$stats['current-jobs-ready'];
            if (!$n || ($limit && $i++>$limit)) {
                break;
            }
            
            $job = $this->pheanstalk
            ->watch('notifications_email')
            ->ignore('default')
            ->reserve();
            
            $id = $job->getData();
            $this->pheanstalk->delete($job);

            $notification = $this->notificationRepository->findOneById($id);
            if ($notification) {
                $processed[] = $notification;
                if (!$notification->isSent()) {
                    if (!$this->sendNotificationEmail($notification)) {
                    $this->pheanstalk
                        ->useTube('notifications_email')
                        ->put(
                                $notification->getId(),
                                Pheanstalk::DEFAULT_PRIORITY,
                                30 // seconds
                            );
                    }
                }
 
            }
        }

        return $processed;
    }
    
    public function processEmailReminder($limit = null, $days = null)
    {
        $processed = array();
        
        $userRepo = $this->em->getRepository('ABMundiUserBundle:User');
        $users = $userRepo->getNotRecentlyRemindedUsers($days?$days.' day':'1 week', $limit);
        
        foreach($users as $user) {
            $this->sendReminderEmail($user);
            $user->setLastReminder(new \DateTime());
            $this->em->persist($user);
            $this->em->flush();
            $processed[] = $user;
        }
        
        return $processed;
    }
    
    private function sendNotificationEmail(Notification $notification)
    {
        $eventKey = $notification->getEventKey();
        $eventReader = $this->getEventReader($eventKey);
        
        $lastNotifications = $this->notificationRepository->getLastNotifications($notification->getUser(), 'email', $eventKey, $eventReader->getAntifloodInterval());
        
        if ($eventReader->inAntifloodInterval($notification->getLastEvent(), $lastNotifications)) {
            return false;
        }
        
        $mailer = $this->mailer;
        $rendered = $this->getNotificationEmail($notification);
        preg_match('/<!--[ ]*subject:[ ]*"(?P<subject>[^"]*)"[ ]*-->/', $rendered, $matches);
        
        $subject = $matches['subject'];
        $body = $rendered;
        
        $message = $mailer->createMessage();
        $message->setSubject(trim($subject))
                ->setFrom(array($this->fromEmail => $this->fromName))
                ->setTo($notification->getUser()->getEmail())
                ->setBody(trim($body), 'text/html');
        
        $mailer->send($message);
        $notification->setAsSent();
        $this->em->persist($notification);
        $this->em->flush();
        
        return true;
    }
    
    public function getNotificationEmail(Notification $notification)
    {
        $eventReader = $this->getEventReader($notification->getEventKey());
        $mainSubject = $eventReader->getMainSubjectByNotification($notification);
        try {
            $template = 'ABMundiNotificationBundle:Email:compiled/' . $notification->getEventKey() . '.html.twig';
            return $this->templating->render($template, array(
                        'notification' => $notification,
                        'mainSubject' => $mainSubject
                    ));
        } catch (\Exception $e) {
            $template = 'ABMundiNotificationBundle:Email:' . $notification->getEventKey() . '.html.twig';
            return $this->templating->render($template, array(
                        'notification' => $notification,
                        'mainSubject' => $mainSubject
                    ));
        }
    }

    public function getFakeNotificationEmail($eventKey)
    {
        $eventReader = $this->getEventReader($eventKey);
        $notification = $eventReader->getFakeNotification();
        return $this->getNotificationEmail($notification);
    }
    
    public function sendFakeNotificationEmail($eventKey)
    {
        $eventReader = $this->getEventReader($eventKey);
        $notification = $eventReader->getFakeNotification();
        $notification->getUser()->setEmail($this->debugEmail);
        $this->sendNotificationEmail($notification);
    }
 
    
    
    
    public function sendReminderEmail(User $user = null)
    {
        if ($user) {
            $data = $this->getReminderData($user);
        } else {
            $data = $this->getFakeReminderData();
        }
        
        if (count($data['goals'])==0) {
            return false;
        }
        
        $rendered = $this->getReminderEmail($data);
        
        preg_match('/<!--[ ]*subject:[ ]*"(?P<subject>[^"]*)"[ ]*-->/', $rendered, $matches);
        
        $subject = $matches['subject'];
        $body = $rendered;
        
        $mailer = $this->mailer;
        $message = $mailer->createMessage();
        $message->setSubject(trim($subject))
                ->setFrom(array($this->fromEmail => $this->fromName))
                ->setTo($data['user']->getEmail())
                ->setBody(trim($body), 'text/html');
        
        $mailer->send($message);
        return true;
    }
    
    public function getReminderEmail($data)
    {
        try {
            $template = 'ABMundiNotificationBundle:Email:compiled/reminder.html.twig';
            return $this->templating->render($template, $data);
        } catch (\Exception $e) {
            $template = 'ABMundiNotificationBundle:Email:reminder.html.twig';
            return $this->templating->render($template, $data);
        }
    }
    
    public function getReminderData(User $user)
    {
        $preferences = $user->getPreferences();
        if (!isset($preferences['reminder_email'])
                || !$preferences['reminder_email']) {
            return false;
        }
        
        $goalRepo = $this->em->getRepository('ABMundiGoalBundle:Goal');
        $goals = $goalRepo->getSleepyJourneys($user);
        
        $data = array(
            'user' => $user,
            'goals' => $goals,
        );
        return $data;
    }
    
    public function getFakeReminderData()
    {
        $faker = new Faker();
        
        $mario = $faker->generateUser('mario');
        $country = $faker->generateGoal('Conquer the country', $mario);
        $world = $faker->generateGoal('Conquer the world', $mario);
        $universe = $faker->generateGoal('Conquer the universe', $mario);
        
        return array(
            'user' => $mario,
            'goals' => array($country, $world, $universe)
        );
    }
    
    
    public function getFakeReminderEmail()
    {
        return $this->getReminderEmail($this->getFakeReminderData());
    }
    
    public function sendFakeReminderEmail()
    {
        $this->sendReminderEmail();
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

}