<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\EventManager;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use RuntimeException;
use Yiisoft\Injector\Injector;

use function sprintf;

final class EventManagerFactory
{
    public function __construct(
        private readonly Injector $injector,
    ) {
    }

    public function createForDbal(array $eventConfig): EventManager
    {
        $eventManager = new EventManager();

        // listener
        $this->configureListener($eventManager, $eventConfig['listeners'] ?? []);

        // subscribers
        $this->configureSubscribers($eventManager, $eventConfig['subscribers'] ?? []);

        return $eventManager;
    }

    private function configureListener(EventManager $eventManager, array $listeners = []): void
    {
        foreach ($listeners as $eventName => $classListeners) {
            foreach ($classListeners as $classListener) {
                $eventManager->addEventListener($eventName, $this->injector->make($classListener));
            }
        }
    }

    private function configureSubscribers(EventManager $eventManager, array $subscribers = []): void
    {
        foreach ($subscribers as $className) {
            $subscriber = $this->injector->make($className);

            if (!$subscriber instanceof EventSubscriber) {
                throw new RuntimeException(sprintf('Class %s not instanceof %s', $className, EventSubscriber::class));
            }

            $eventManager->addEventSubscriber($subscriber);
        }
    }

    public function createForOrm(EventManager $eventManager, array $eventConfig): void
    {
        $this->configureListener($eventManager, $eventConfig['listeners'] ?? []);

        $this->configureSubscribers($eventManager, $eventConfig['subscribers'] ?? []);
    }
}
