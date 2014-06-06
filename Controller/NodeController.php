<?php

namespace ABMundi\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ABMundi\NotificationBundle\Entity\Notification;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * This is the default controller 
 */
class NodeController extends Controller
{
    /**
     * List of current user notifications
     * 
     * @return type 
     * 
     * @Route("/node", name="test_node")
     */
    public function testNodeAction() {
        $pheanstalk = $this->get("leezy.pheanstalk");

        $data = array(
            'paperino'=>'asdad',
            'adasd' => 'assdnald',
        );
        
        $pheanstalk
          ->useTube('testtube'.rand(1,3))
          ->put(json_encode($data));

        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->setContent('done');
        return $response;
    }
}
