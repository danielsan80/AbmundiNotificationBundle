<?php

namespace ABMundi\NotificationBundle\Entity;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Doctrine ORM listener updating the Event subject.
 */
class EventListener implements EventSubscriber
{
    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {

        $this->container = $container;
    }

    /**
     * Events to intercect
     *
     * @return array Array of events to intersect 
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist,
            Events::preUpdate,
            Events::postLoad,
        );
    }

    /**
     * Manage the prePersist hook
     * 
     * @param LifecycleEventArgs $args 
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->handlePersistEvents($args);
    }

    /**
     * Manage the preUpdate hook
     * 
     * @param PreUpdateEventArgs $args 
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $this->handlePersistEvents($args);
    }

    /**
     * Manage the postLoad hook
     * 
     * @param LifecycleEventArgs $args 
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Event) {
            if (null === $this->eventManager) {
                $this->eventManager = $this->container->get('abm_notification.event.manager');
            }

            $this->eventManager->updateSubjectObject($entity);
        }
    }

    /**
     * Handle Persists events
     *
     * @param LifecycleEventArgs $args 
     */
    private function handlePersistEvents(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Event) {
            if (null === $this->eventManager) {
                $this->eventManager = $this->container->get('abm_notification.event.manager');
            }

            $this->eventManager->updateSubjectStrings($entity);

            if ($args instanceof PreUpdateEventArgs) {
                // We are doing a update, so we must force Doctrine to update the
                // changeset in case we changed something above
                $em   = $args->getEntityManager();
                $uow  = $em->getUnitOfWork();
                $meta = $em->getClassMetadata(get_class($entity));
                $uow->recomputeSingleEntityChangeSet($meta, $entity);
            }
        }
    }
}