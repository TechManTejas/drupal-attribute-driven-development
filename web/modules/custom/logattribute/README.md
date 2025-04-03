<?php

namespace Drupal\Core\Attribute;

use Attribute;

/**
 * Attribute to log function execution.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class LogFunction {
    public function __construct() {}
}


<?php

namespace Drupal\Core\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\LogFunctionHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LogFunctionSubscriber implements EventSubscriberInterface {
    protected LogFunctionHandler $handler;

    public function __construct(LogFunctionHandler $handler) {
        $this->handler = $handler;
    }

    public static function create(ContainerInterface $container) {
        return new static($container->get('your_module.log_function_handler'));
    }

    public function onKernelController(ControllerEvent $event): void {
        $controller = $event->getController();
        if (is_array($controller)) {
            [$object, $method] = $controller;
            $this->handler->processAttributes($object, $method);
        }
    }

    public static function getSubscribedEvents(): array {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}

<?php

namespace Drupal\Core;

use ReflectionMethod;
use Drupal\Core\Attribute\LogFunction;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

class LogFunctionHandler {
    protected LoggerChannelFactoryInterface $loggerFactory;

    public function __construct(LoggerChannelFactoryInterface $loggerFactory) {
        $this->loggerFactory = $loggerFactory;
    }

    public function processAttributes(object $object, string $method): void {
        $reflection = new ReflectionMethod($object, $method);
        $attributes = $reflection->getAttributes(LogFunction::class);

        if (!empty($attributes)) {
            $this->loggerFactory->get('logattribute')->info(
                "Function executed: @name", 
                ['@name' => $reflection->getName()]
            );
        }
    }
}

services:
  logattribute.handler:
    class: 'Drupal\Core\LogFunctionHandler'
    arguments: ['@logger.factory']
  logattribute.subscriber:
    class: 'Drupal\Core\EventSubscriber\LogFunctionSubscriber'
    arguments: ['@logattribute.handler']
    tags:
      - { name: event_subscriber }