<?php

namespace ABMundi\NotificationBundle\Tests\Pheanstalk;

class Pheanstalk
{
    private $cache = 'app/cache/test_pheanstalk';
    private $tube='';
    private $queue=array();
    
    public function __construct() {
        $this->load();
    }

    public function delete($job)
    {
        $this->load();
        unset($this->queue[$this->tube][$job->getId()]);

        $this->save();
        return $this;
    }

    public function ignore($tube)
    {
        return $this;
    }

    public function put($data)
    {
        $this->load();
        $job = new Job($data);
        $this->queue[$this->tube][$job->getId()] = $job;

        $this->save();
        return $this;
    }

    public function reserve($timeout = null)
    {
        $this->load();
        if (isset($this->queue[$this->tube])) {
            $job = array_shift($this->queue[$this->tube]);

            $this->save();
            return $job;
        }

        $this->save();
        return null;
    }
    
    public function stats()
    {
        $stats = array();
        return $stats;
    }

    public function useTube($tube)
    {
        $this->load();
        $this->tube = $tube;

        $this->save();
        return $this;
    }

    public function watch($tube)
    {
        $this->load();
        $this->tube = $tube;

        $this->save();
        return $this;
    }

    private function load()
    {
        if (file_exists($this->cache)) {
            $str = file_get_contents($this->cache);
            $data = unserialize($str);
            $this->tube = $data['tube'];
            $this->queue = $data['queue'];
        }
    }
    
    private function save()
    {
        $data = array(
            'tube' => $this->tube,
            'queue' => $this->queue
        );
        file_put_contents($this->cache, serialize($data));
    }


}

class Job
{
    private $id;
    private $data;
    
    public function __construct($data)
    {
        $now = new \DateTime();
        $this->id = $now->getTimestamp();
        $this->data = $data;
    }
    
    public function getId() {
        return $this->id;
    }
    public function getData() {
        return $this->data;
    }
}