<?php

namespace ABMundi\NotificationBundle\Model;

use ABMundi\UserBundle\Entity\User;
use ABMundi\GoalBundle\Entity\Goal;
use ABMundi\GoalBundle\Entity\Cheer;
use ABMundi\GoalBundle\Entity\Congrat;
use ABMundi\GoalBundle\Entity\Question;
use ABMundi\NotificationBundle\Entity\Event;
use ABMundi\NotificationBundle\Entity\Notification;

class Faker
{
    private $references;
    
    public function generateUser($username)
    {
        $user = new User;
        $user->setUsername($username);
        $user->setEmail($username . '@abmundi.com');
        $user->setPlainPassword($username);
        $user->setEnabled(true);

        return $user;
    }
    
    public function generateGoal($name,User $owner)
    {
        $now = new \DateTime();
        $goal = new Goal;
        $goal->setName($name);
        $goal->setDescription('Mauris et lacus orci, et facilisis quam. Phasellus porta sapien nec nibh pretium viverra. Morbi vitae metus velit. Curabitur sodales, tortor at lacinia varius, turpis nunc sollicitudin lectus, et ultricies orci eros eget justo. ');
        $date = clone $now;
        $goal->setExpireAt($date->modify('+1 month'));
        $goal->setVisibility(Goal::VISIBILITY_PUBLIC);
        $goal->setOwner($owner);
        $goal->setWhy('\'Cause I think it will make the World a better place!');
        $goal->setDoneCondition('To arrive to 100%');
        $goal->setHowToParty('I will drink a lot');
        
        $owner->addGoal($goal);

        return $goal;
    }
    
    public function generateQuestion($name, Goal $goal)
    {
        $question = new Question($goal);
        $question->setTitle($name);
        $question->setText('Mauris et lacus orci, et facilisis quam. Phasellus porta sapien nec nibh pretium viverra. Morbi vitae metus velit. Curabitur sodales, tortor at lacinia varius, turpis nunc sollicitudin lectus, et ultricies orci eros eget justo. ');

        return $question;
    }
    
    public function generateNote($goal, $percentage)
    {
        $now = new \DateTime();
        $note = new Note($goal);
        $note->setCreatedAt($now);
        $note->setPercentage($percentage);
        $notel->setDelta($goal->getPercentage() - $percentage);
        $note->setTitle('I\'m at '.$percentage.'%');
        $note->setText('Mauris et lacus orci, et facilisis quam. Phasellus porta sapien nec nibh pretium viverra. Morbi vitae metus velit. Curabitur sodales, tortor at lacinia varius, turpis nunc sollicitudin lectus, et ultricies orci eros eget justo. ');
        $goal->addNote($note);
        
        return $note;
    }
    
    public function userCheersGoal($user, $goal)
    {
        $cheer = new Cheer();
        $cheer->setGoal($goal);
        if ($user) {
            $cheer->setUser($user);
        }

        return $cheer;
    }
    
    public function userCongratsGoal($user,Goal $goal)
    {
        $congrat = new Congrat();
        $congrat->setGoal($goal);
        if ($user) {
            $congrat->setUser($user);
        }

        return $congrat;
    }
    
    public function userFollowsUser($user1, $user2)
    {
        $user1->follow($user2);
    }
    
    
    public function createEvent($key, $subject, User $actor = null)
    {
        $now = new \DateTime();
        $event = new Event();
        $event->setKey($key);
        $event->setSubject($subject);
        $event->setActor($actor);
        $event->setCreatedAt($now);


        return $event;
    }
    
    public function generateNotification(User $recipient, $events)
    {
        $now = new \DateTime();
        
        $notification = new Notification();
        $notification->setUser($recipient);
        $date = clone $now;
        $notification->setCreatedAt($date->modify('-5 minutes'));
        $notification->setSentAt(null);
        foreach($events as $event) {
            $notification->addEvent($event);
        }

        return $notification;
    }
    
    public function addReference($id, $value)
    {
        if (isset($this->references[$id])) {
            throw new \Exception('ID '.$id.' is used yet');

            return;
        }
        $this->references[$id] = $value;
    }
    
    public function getReference($id)
    {
        return $this->references[$id];
    }
}