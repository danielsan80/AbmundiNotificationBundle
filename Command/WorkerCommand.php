<?php

namespace ABMundi\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

abstract class WorkerCommand extends ContainerAwareCommand
{
    private $lastOutput;
    
    protected function configure()
    {
        $this->addOption('host', null, InputOption::VALUE_REQUIRED, 'Which is the host name? [localhost]', 'localhost');
    }
    
    protected function simulateRequest(InputInterface $input)
    {
        $host = $input->getOption('host');
        $container = $this->getApplication()->getKernel()->getContainer();
        $server = $_SERVER;
        $server['SERVER_NAME'] = $host;
        $container->get('router')->getContext()->setHost($host);
        $request = new Request($_GET, $_POST, array(), $_COOKIE, $_FILES, $server);
        
        $container->enterScope('request');
        $container->set('request', $request, 'request');
    }
    
    public function writeOutput(OutputInterface $output, $els)
    {
        $text = '';
        foreach($els as $el) {
            $text .= '.';
        }
        $text .= ' done';

        $this->setLastOutput($text);
        $output->writeln($text);
    }
    
    public function writeException(OutputInterface $output, \Exception $e)
    {
        $output->writeln($e->getMessage());
        $output->writeln($e->getTraceAsString());
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $kernel = $this->getApplication()->getKernel();
        $log = new Logger('workers' . date('Ymd'));
        $log->pushHandler(new StreamHandler($kernel->getLogDir().'/workers.log', Logger::INFO));
        
        $this->simulateRequest($input);
        try {
            $time = time();
            $this->doExecute($input, $output);
            $time = time() - $time;
            $log->addInfo($this->getName(), array('output' => $this->getLastOutput(), 'time' => $time, 'host'=> $input->getOption('host')));
        } catch (\Exception $e) {
            $this->writeException($output, $e);
        }
    }
    
    protected function setLastOutput($output)
    {
        $this->lastOutput = $output;
    }
    protected function getLastOutput()
    {
        return $this->lastOutput;
    }
    
}