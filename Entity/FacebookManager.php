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

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Facebook Manager
 */
class FacebookManager
{
    protected $eventReaderFactory;
    
    protected $pheanstalk;
    
    protected $facebookUserProvider;
    protected $namespace;
    
    protected $router;
    
    protected $em;
    protected $eventRepository;
    protected $notificationRepository;
    
    protected $kernel;
    
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
    
    public function setKernel($kernel)
    {
        $this->kernel = $kernel;
    }
    
    public function setFacebookUserProvider($facebookUserProvider)
    {
        $this->facebookUserProvider = $facebookUserProvider;
        $this->facebook = $facebookUserProvider->facebook;
    }
    
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    public function setRouter($router)
    {
        $this->router = $router;
    }

    public function processNotifications($limit = null)
    {
        $processed = array();
        $i = 0;
        $this->pheanstalk->useTube('notifications_facebook');
        while (true) {
            $stats = $this->pheanstalk->statsTube('notifications_facebook');
            $n = (int)$stats['current-jobs-ready'];
            if (!$n || ($limit && $i++>$limit)) {
                break;
            }
            
            $job = $this->pheanstalk
            ->watch('notifications_facebook')
            ->ignore('default')
            ->reserve();
            
            $id = $job->getData();
            $this->pheanstalk->delete($job);

            $notification = $this->notificationRepository->findOneById($id);
            if ($notification) {
                $processed[] = $notification;
                $this->postOnTimeline($notification);
            }
        }

        return $processed;
    }
    
    public function processCommands($limit = null)
    {
        $processed = array();
        $i = 0;
        $this->pheanstalk->useTube('events_facebook_cmd');
        while (true) {
            $stats = $this->pheanstalk->statsTube('events_facebook_cmd');
            $n = (int)$stats['current-jobs-ready'];
            if (!$n || ($limit && $i++>$limit)) {
                break;
            }
            
            $job = $this->pheanstalk
            ->watch('events_facebook_cmd')
            ->ignore('default')
            ->reserve();
            
            $id = $job->getData();
            $this->pheanstalk->delete($job);

            $event = $this->eventRepository->findOneById($id);
            if ($event) {
                $processed[] = $event;
                $this->dispatchCommand($event);
            }
        }

        return $processed;
    }
    
    private function postOnTimeline(Notification $notification)
    {
        $builtinActions = array('og.follows');
        
        if (!$notification->getUser()->getFacebookId())
            return;
        
        try {
            
            $eventReader = $this->getEventReader($notification->getEventKey());
            $data = $eventReader->getFacebookData($notification);
            $data['access_token'] = $this->facebook->getAccessToken();
            $action = $data['action'];
            
            $data[$data['object']] = $this->router->generate(
                    $data['url']['route'],
                    $data['url']['args'],
                    true
            );
            
            
            unset($data['object'], $data['url'], $data['action']);
            unset($data['message']);
            
            //$this->facebook->api('/'.$notification->getUser()->getFacebookid().'/feed', 'POST', $data);
            if ( in_array($action, $builtinActions) ) {
                $uri = '/'.$notification->getUser()->getFacebookid().'/'. $action;
            } else {
                $uri = '/'.$notification->getUser()->getFacebookid().'/'.$this->namespace.':'.$action;
            }
            
            $this->facebook->api($uri, 'POST', $data);
            
            $notification->setAsSent();
            $this->em->persist($notification);
            $this->em->flush();

        } catch (\FacebookApiException $e) {
            
            $log = new Logger('workers' . date('Ymd'));
            $log->pushHandler(new StreamHandler($this->kernel->getLogDir().'/workers.log', Logger::INFO));

            $log->addError($e->getMessage(), array('data' => $data));
  

        }
    }

    private function dispatchCommand(Event $event)
    {
        $key = $event->getKey();
        $parts = explode('.', $key);
        foreach($parts as $i => $part) {
            if ($i==0){
                continue;
            }                
            $parts[$i] = ucfirst($part);
        }
        $method = 'executeCommand_'.implode('', $parts);
        $this->$method($event);
    }
    
    
    private function executeCommand_userUnfollow(Event $event) {
        $currentUser = $event->getActor();
        $user = $event->getSubject();
        
        if ($currentUser->isFollowing($user)) {
            return;
        }
        
        if ($fid = $currentUser->getFacebookid()) {
            $output = $this->facebook->api('/'.($currentUser->getFacebookid()).'/og.follows', 'GET');
            $data = $output['data'];
            while (true) {
                foreach($data as $el) {

                    if ($el['from']['id']!=$currentUser->getFacebookid()) {
                        continue;
                    }
//                    echo "from.id = ".$el['from']['id']." ok; \n";
        //            if ($el['application']['id']!='app_id') {
        //                continue;
        //            }
                    if ($el['application']['namespace']!=$this->namespace) {
                        continue;
                    }
//                    echo "application.namespace = ".$el['application']['namespace']." ok; \n";
                    if ($el['type']!='og.follows') {
                        continue;
                    }
//                    echo "type = ".$el['type']." ok; \n";
                    if ($el['data']['profile']['type']!='profile') {
                        continue;
                    }
//                    echo "data.profile.type = ".$el['data']['profile']['type']." ok; \n";
                    if ($el['data']['profile']['title']!=$user->getName()) {
                        continue;
                    }
//                    echo "data.profile.title = ".$el['data']['profile']['title']." ok; \n";
                    if ($el['data']['profile']['url']!=$this->router->generate(
                            'user_public_profile',
                            array('username' => $user->getUsername()),
                            true
                        )) {
                        continue;
                    }
//                    echo "data.profile.title = ".$el['data']['profile']['url']." ok; \n";

                    $this->facebook->api('/'.($el['id']), 'DELETE');
//                    echo "DELETE = ".$el['id']." ok; \n";
                }

                if (isset($output['paging']['next'])) {
//                    echo "paging is set \n";
                    $next = explode('graph.facebook.com', $output['paging']['next']);
                    $next = $next[1];
                    $output = $this->facebook->api($next, 'GET');
                    $data = $output['data'];
                } else {
//                    echo "paging is NOT set \n";
                    break;
                }            
            }
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

}