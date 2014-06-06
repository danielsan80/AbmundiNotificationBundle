<?php

namespace ABMundi\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * This is the mailer controller 
 * 
 * @Route("/worker")
 */
class WorkerController extends Controller
{
    
    /**
     * Prosess pending events
     * 
     * @return Response 
     * 
     * @Route("/process-events", name="worker_processEvents")
     */
    public function processEventsAction()
    {
        $request = $this->getRequest();
        $host = $request->getHost().$request->getBaseUrl();
        return new Response($this->runCommand("abmundi:notification:process-events --host=".$host));
    }

    /**
     * Prosess pending events on main channel
     * 
     * @return Response 
     * 
     * @Route("/process-events-main", name="worker_processEventsMain")
     */
    public function processEventsMainAction()
    {
        $request = $this->getRequest();
        $host = $request->getHost().$request->getBaseUrl();
        return new Response($this->runCommand("abmundi:notification:process-events-main --host=".$host));
    }
 
    /**
     * Prosess pending events on email channel
     * 
     * @return Response 
     * 
     * @Route("/process-events-email", name="worker_processEventsEmail")
     */
    public function processEventsEmailAction()
    {
        $request = $this->getRequest();
        $host = $request->getHost().$request->getBaseUrl();
        return new Response($this->runCommand("abmundi:notification:process-events-email --host=".$host));
    }
    
    /**
     * Prosess pending events on facebook channel
     * 
     * @return Response 
     * 
     * @Route("/process-events-facebook", name="worker_processEventsFacebook")
     */
    public function processEventsFacebookAction()
    {
        $request = $this->getRequest();
        $host = $request->getHost().$request->getBaseUrl();
        return new Response($this->runCommand("abmundi:notification:process-events-facebook --host=".$host));
    }
    
    
    /**
     * Prosess pending events
     * 
     * @return Response 
     * 
     * @Route("/process-notifications-email", name="worker_processNotificationsEmail")
     */
    public function processNotificationsEmailAction()
    {
        $request = $this->getRequest();
        $host = $request->getHost().$request->getBaseUrl();
        return new Response($this->runCommand("abmundi:notification:process-notifications-email --host=".$host));
    }
    
    /**
     * Prosess pending events
     * 
     * @return Response 
     * 
     * @Route("/process-notifications-facebook", name="worker_processNotificationsFacebook")
     */
    public function processNotificationsFacebookAction()
    {
        $request = $this->getRequest();
        $host = $request->getHost().$request->getBaseUrl();
        return new Response($this->runCommand("abmundi:notification:process-notifications-facebook --host=".$host));
    }
    
    /**
     * Send a reminders
     * 
     * @return type 
     * 
     * @Route("/email-reminder", name="worker_emailReminder")
     */
    public function processEmailReminderAction()
    {
        $request = $this->getRequest();
        $host = $request->getHost().$request->getBaseUrl();
        
        return new Response($this->runCommand("abmundi:notification:email:reminder --host=".$host));
    }
 
    /**
     * Send a reminders
     * 
     * @return type 
     * 
     * @Route("/process-events-facebook-cmd", name="worker_processEventsFacebookCmd")
     */
    public function processEventsFacebookCmdAction()
    {
        $request = $this->getRequest();
        $host = $request->getHost().$request->getBaseUrl();
        return new Response($this->runCommand("abmundi:notification:process-events-facebook-cmd --host=".$host));
    }
    
    /**
     * Prosess all
     * 
     * @return Response 
     * 
     * @Route("/process-all", name="worker_processAll")
     */
    public function processAllAction()
    {
        $request = $this->getRequest();
        $host = $request->getHost().$request->getBaseUrl();
        $output = '';
        $output .= $this->runCommand("abmundi:notification:process-events --host=".$host);
        $output .= $this->runCommand("abmundi:notification:process-events-main --host=".$host);
        $output .= $this->runCommand("abmundi:notification:process-events-email --host=".$host);
        $output .= $this->runCommand("abmundi:notification:process-events-facebook --host=".$host);
        $output .= $this->runCommand("abmundi:notification:process-notifications-email --host=".$host);
        $output .= $this->runCommand("abmundi:notification:process-notifications-facebook --host=".$host);
        $output .= $this->runCommand("abmundi:notification:process-events-facebook-cmd --host=".$host);
        $output .= $this->runCommand("abmundi:notification:email:reminder --host=".$host);

        return new Response($output);
    }
    
    /**
     * Prosess all
     * 
     * @return Response 
     * 
     * @Route("/process-all-nofb", name="worker_processAllNoFb")
     */
    public function processAllNoFbAction()
    {
       $request = $this->getRequest();
        $host = $request->getHost().$request->getBaseUrl();
        $output = '';
        $output .= $this->runCommand("abmundi:notification:process-events --host=".$host);
        $output .= $this->runCommand("abmundi:notification:process-events-main --host=".$host);
        $output .= $this->runCommand("abmundi:notification:process-events-email --host=".$host);
        $output .= $this->runCommand("abmundi:notification:process-notifications-email --host=".$host);
        $output .= $this->runCommand("abmundi:notification:email:reminder --host=".$host);
//        $output .= $this->runCommand("abmundi:notification:process-events-facebook --host=".$host);
//        $output .= $this->runCommand("abmundi:notification:process-notifications-facebook --host=".$host);
//        $output .= $this->runCommand("abmundi:notification:process-events-facebook-cmd --host=".$host);

        return new Response($output);
    }
    
    /**
     * Runs a command and returns it output
     */
    private function runCommand($command)
    {
        $application = new Application($this->get('kernel'));
        $application->setAutoExit(false);

        $fp = tmpfile();
        $input = new StringInput($command);
        $output = new StreamOutput($fp);

        $application->run($input, $output);

        fseek($fp, 0);
        $output = '';
        while (!feof($fp)) {
            $output = fread($fp, 4096);
        }
        fclose($fp);

        return $output;
    }
}
