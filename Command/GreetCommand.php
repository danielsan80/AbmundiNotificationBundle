<?php
namespace ABMundi\NotificationBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use \Twig_Environment;
use Symfony\Component\HttpFoundation\Request;

class GreetCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('demo:greet')
            ->setDescription('Greet someone')
            ->addArgument('host', InputArgument::REQUIRED, 'Hostname?')
            ->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getApplication()->getKernel()->getContainer();
        $templateFile = "ABMundiUserBundle:Default:_iconuser.html.twig";
        
        $server = $_SERVER;
        $server['SERVER_NAME'] = $input->getArgument('host');
        $request = new Request($_GET, $_POST, array(), $_COOKIE, $_FILES, $server);
        
        $em = $container->get('doctrine.orm.entity_manager');
        $rep = $em->getRepository('ABMundiUserBundle:User');
        $user = $rep->findOneByUsername($input->getArgument('name'));
        
        if($user){
            $container->enterScope('request');
            $container->set('request', $request, 'request');
            $rendered = $container->get('templating')->render(
                        $templateFile, 
                        array( 
                            'user' => $user, 
                    )); 
            echo $rendered;
        }
        
    }
}