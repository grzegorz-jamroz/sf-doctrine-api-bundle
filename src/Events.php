<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle;

final class Events
{
    /**
     * Dispatched after results have been fetched from DB to allow override data
     * Hook into this event if you need extend default find results.
     *
     * @Event("Ifrost\DoctrineApiBundle\Event\AfterFindEvent")
     */
    public const AFTER_FIND = 'ifrost_doctrine_api.on_after_find';

    /**
     * Dispatched before entity create to allow override stored data
     * Hook into this event if you need extend default create behaviour.
     *
     * @Event("Ifrost\DoctrineApiBundle\Event\BeforeCreateEvent")
     */
    public const BEFORE_CREATE = 'ifrost_doctrine_api.on_before_create';

    /**
     * Dispatched before entity update to allow override stored data
     * Hook into this event if you need extend default update behaviour.
     *
     * @Event("Ifrost\DoctrineApiBundle\Event\BeforeUpdateEvent")
     */
    public const BEFORE_UPDATE = 'ifrost_doctrine_api.on_before_update';

    /**
     * Dispatched before entity modify to allow override stored data
     * Hook into this event if you need extend default modify behaviour.
     *
     * @Event("Ifrost\DoctrineApiBundle\Event\BeforeModifyEvent")
     */
    public const BEFORE_MODIFY = 'ifrost_doctrine_api.on_before_modify';

    /**
     * Dispatched before entity delete to allow make some additional action
     * Hook into this event if you need extend default delete behaviour.
     *
     * @Event("Ifrost\DoctrineApiBundle\Event\BeforeDeleteEvent")
     */
    public const BEFORE_DELETE = 'ifrost_doctrine_api.on_before_delete';
}
