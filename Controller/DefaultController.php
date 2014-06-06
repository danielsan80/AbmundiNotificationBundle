<?php

namespace ABMundi\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ABMundi\NotificationBundle\Entity\Notification;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * This is the default controller 
 * 
 * @Route("/notification")
 */
class DefaultController extends Controller
{
    /**
     * List of current user notifications
     * 
     * @return type 
     * 
     * @Route("", name="notification_index")
     */
    public function indexAction()
    {
        $notifications = $this->readAllNotifications();

        return $this->render('ABMundiNotificationBundle:Default:index.html.twig', array(
                    'notifications' => $notifications,
                ));
    }
    
    private function readAllNotifications()
    {
        $eventManager = $this->get('abm_notification.notification.manager');
        $user = $this->getCurrentUser();
        $notifications = array();
        if ($user) {
            $notifications = $eventManager->getNotifications($user);
        }
        $eventManager->setNotificationsAsRead($user);
        return $notifications;
    }

    /**
     * List of current user notifications
     * 
     * @return type 
     * 
     * @Route("/readall", name="read_all_notifications")
     * @Method("POST")
     */
    public function readAllNotificationsAction()
    {
        $notifications = $this->readAllNotifications();

        return $this->render('ABMundiNotificationBundle:Default:index.html.twig', array(
                    'notifications' => $notifications,
                ));
    }

    /**
     * Render a single notification dispatching to correct template (partial)
     * 
     * @param Notification $notification
     * 
     * @return type 
     */
    public function notificationAction(Notification $notification)
    {
        return $this->getRenderedNotification($notification, 'notifications');
    }
    
    /**
     * Render a single topbar notification dispatching to correct template (partial)
     * 
     * @param Notification $notification
     * 
     * @return type 
     */
    public function topbarNotificationAction(Notification $notification)
    {
        return $this->getRenderedNotification($notification,'topbarNotifications');
    }
    
    /**
     * Render a single topbar notification dispatching to correct template (partial)
     * 
     * @param Notification $notification
     * 
     * @return type 
     */
    private function getRenderedNotification(Notification $notification, $tplDir)
    {
        $eventManager = $this->get('abm_notification.notification.manager');
        $eventReader = $eventManager->getEventReader($notification->getEventKey());

        try {
            $key = $notification->getEventKey();
            $data = array(
                'notification' => $notification,
                'mainSubject' => $eventReader->getMainSubjectByNotification($notification)
            );

            return $this->render('ABMundiNotificationBundle:Default:'.$tplDir.'/_' . $key . '.html.twig', $data);
        } catch (Exception $e) {

            return $this->render('ABMundiNotificationBundle:Default:'.$tplDir.'/_default.html.twig', $data);
        }
    }

    /**
     * Render the Topbar (partial)
     * 
     * @return type 
     */
    public function topbarAction()
    {
        $evm = $this->get('abm_notification.notification.manager');
        $user = $this->getCurrentUser();

        $latestNotifications = array();
        if ($user) {
            $latestNotifications = $evm->getLatestNotifications($this->getCurrentUser(), 5);
            $unreadNotifications = $evm->getUnreadNotifications($this->getCurrentUser());
        }

        return $this->render('ABMundiNotificationBundle:Default:topbar.html.twig', array(
                    'num_unread_notifications' => count($unreadNotifications),
                    'latest_notifications' => $latestNotifications,
                ));
    }

    /**
     * Return the current user.
     *
     * @return UserInterface or null
     */
    private function getCurrentUser()
    {
        return $this->get('abm_user.util')->getCurrentUser();
    }
}
