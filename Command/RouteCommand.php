<?php

namespace ABMundi\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

abstract class RouteCommand extends ContainerAwareCommand
{
    private $route;
    
    protected function configure()
    {
        $this->addOption('host', null, InputOption::VALUE_REQUIRED, 'Which is the host name? [localhost]', 'localhost');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = time();
        $querystring = $this->getQuerystring();
        
        $container = $this->getContainer();
        
        $kernel = $container->get('kernel');
        $dir = $kernel->getCacheDir().'/wget';
        $now = new \DateTime();
        $file = $dir.'/'.md5($now->format('YmdHis').$this->getRoute());

        if (!file_exists($dir)) {
            mkdir($dir);
        }
        
        $log = new Logger('workers' . date('Ymd'));
        $log->pushHandler(new StreamHandler($kernel->getLogDir().'/workers.log', Logger::INFO));

        $cmd = 'wget -qO '.$file.' http://'.$input->getOption('host').$this->getRoute().($querystring?'?'.$querystring:'');
        
        exec($cmd, $out);
        $str = file_get_contents($file);
        $output->writeln($str);
        $time = time() - $time;
        $log->addInfo($this->getRoute().($querystring?'?'.$querystring:''), array('output' => $str, 'time' => $time));
    }
    
    protected function setRoute($route){
        $this->route = $route;
    }
    
    private function getRoute(){
        return $this->route;
    }
    
    protected function getQuerystringValue()
    {
        return array();
    }
    
    private function getQuerystring()
    {
        $values = $this->getQuerystringValue();
        foreach($values as $key => $value) {
            if (!$value) {
                unset($values[$key]);
            }
        }
        $querystring = http_build_query($values);
        
        return $querystring;
    }
    
}