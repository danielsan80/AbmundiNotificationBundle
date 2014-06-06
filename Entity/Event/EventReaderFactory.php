<?php

namespace ABMundi\NotificationBundle\Entity\Event;

/**
 * The factory fo EventReader objects 
 */
class EventReaderFactory
{
    /**
     * create the EventReader binded to the given event key
     * 
     * @param type $eventKey
     * 
     * @return \ABMundi\NotificationBundle\Entity\Event\EventReader
     * @throws Exception 
     */
    public function createEventReader($eventKey)
    {
        $parts = explode('.', $eventKey);
        $class = '';
        foreach ($parts as $part) {
            $class .= ucfirst($part);
        }
        $class = 'ABMundi\\NotificationBundle\\Entity\\Event\\EventReader\\' .$class . 'EventReader';
        if (class_exists($class)) {

            return new $class;
        } else {

            throw new UndefinedEventKeyException('EventKey "' . $eventKey . '" is not defined');
        }
    }
}