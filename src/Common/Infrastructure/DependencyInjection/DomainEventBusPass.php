<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\DependencyInjection;

use App\Common\Domain\Event\DomainEventSubscriber;
use App\Common\Infrastructure\Symfony\Event\DomainEventBus;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DomainEventBusPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(DomainEventBus::class)) {
            return;
        }

        $handlerServices = $container->findTaggedServiceIds('domain.event.handler');

        foreach ($handlerServices as $id => $tags) {
            $handlerClass = $container->getDefinition($id)->getClass();
            if (!$handlerClass || !is_subclass_of($handlerClass, DomainEventSubscriber::class)) {
                continue;
            }
            
            $subscribedEvents = $handlerClass::subscribedTo();
            foreach ($subscribedEvents as $eventClass) {
                $container->getDefinition($id)->addTag('kernel.event_listener', ['event' => $eventClass::EVENT_NAME]);
            }
        }
    }
}