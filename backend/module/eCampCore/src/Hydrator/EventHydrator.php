<?php

namespace eCamp\Core\Hydrator;

use eCamp\Api\Collection\EventInstanceCollection;
use eCamp\Core\Entity\Event;
use Zend\Hydrator\HydratorInterface;
use ZF\Hal\Link\Link;

class EventHydrator implements HydratorInterface {
    /**
     * @param object $object
     * @return array
     */
    public function extract($object) {
        /** @var Event $event */
        $event = $object;
        return [
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'camp' => $event->getCamp(),

            'event_category' => $event->getEventCategory(),
            'event_instances' => new EventInstanceCollection($event->getEventInstances()),

            'event_plugins' => Link::factory([
                'rel' => 'event_plugins',
                'route' => [
                    'name' => 'ecamp.api.event_plugin',
                    'options' => [ 'query' => [ 'event_id' => $event->getId() ] ]
                ]
            ]),
        ];
    }

    /**
     * @param array $data
     * @param object $object
     * @return object
     */
    public function hydrate(array $data, $object) {
        /** @var Event $event */
        $event = $object;

        $event->setTitle($data['title']);

        return $event;
    }
}
