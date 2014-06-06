<?php

namespace ABMundi\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * This is the mailer controller 
 * 
 * @Route("/notification/email")
 */
class EmailController extends Controller
{
    
    /**
     * Send a fake notification for a given event
     * 
     * @return type 
     * 
     * @Route("/{key}", name="notification_email_show")
     */
    public function showAction($key)
    {
        $emailManager = $this->get('abm_notification.email.manager');
        if ($key=='reminder') {
            return new Response($emailManager->getFakeReminderEmail($key));
        }

        return new Response($emailManager->getFakeNotificationEmail($key));
    }
    
    /**
     * Send a fake notification for a given event
     * 
     * @return type 
     * 
     * @Route("/{key}/send", name="notification_email_send")
     */
    public function emailSendAction($key)
    {
        $emailManager = $this->get('abm_notification.email.manager');
        if ($key=='reminder') {
            $emailManager->sendFakeReminderEmail();

            return new Response($emailManager->getFakeReminderEmail($key));
        }
        
        $emailManager->sendFakeNotificationEmail($key);
        
        return new Response($emailManager->getFakeNotificationEmail($key));
    }
    
}
