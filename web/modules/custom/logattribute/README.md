# LogAttribute Module for Drupal 11

## Overview
The **LogAttribute** module introduces a custom PHP attribute `#[LogFunction]` that automatically logs the execution of methods in Drupal controllers. It uses Symfony's event system to intercept method calls and logs them without requiring explicit logging calls.

## Features
- Define `#[LogFunction]` on controller methods to log execution.
- Uses Drupal's logging system (`logger.factory`) to capture method execution details.
- Implements event subscriber to intercept method calls dynamically.

## Installation

### Step 1: Place Files in Drupal Core
To ensure the module runs correctly, you need to place the required files in the appropriate locations within Drupal's core:

1. Copy the **LogFunction** attribute class to `core/lib/Drupal/Core/Attribute/LogFunction.php`:
    ```php
    namespace Drupal\Core\Attribute;
    use Attribute;
    
    /**
     * Attribute to log function execution.
     */
    #[Attribute(Attribute::TARGET_METHOD)]
    class LogFunction {
        public function __construct() {}
    }
    ```
2. Copy the **LogFunctionHandler** to `core/lib/Drupal/Core/LogFunctionHandler.php`:
    ```php
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
    ```
3. Copy the **LogFunctionSubscriber** to `core/lib/Drupal/Core/EventSubscriber/LogFunctionSubscriber.php`:
    ```php
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
            return new static($container->get('logattribute.handler'));
        }
    
        public function onKernelController(ControllerEvent $event): void {
            $controller = $event->getController();
            if (is_array($controller)) {
                [$object, $method] = $controller;
                $this->handler->processAttributes($object, $method);
            }
        }
    
        public static function getSubscribedEvents(): array {
            return [KernelEvents::CONTROLLER => 'onKernelController'];
        }
    }
    ```
4. Add the following to `core/core.services.yml` to register the services:
    ```yaml
    services:
      logattribute.handler:
        class: 'Drupal\Core\LogFunctionHandler'
        arguments: ['@logger.factory']
    
      logattribute.subscriber:
        class: 'Drupal\Core\EventSubscriber\LogFunctionSubscriber'
        arguments: ['@logattribute.handler']
        tags:
          - { name: event_subscriber }
    ```

### Step 2: Enable the Module
Run the following command in the Drupal root directory:
```sh
 drush en logattribute
```
Or manually enable it via **Extend** in the Drupal admin panel.

### Step 3: Clear Cache
After enabling the module, clear the cache to ensure proper event registration:
```sh
 drush cr
```

## How It Works
### Attribute Definition
The module defines a custom PHP attribute `#[LogFunction]`:
```php
namespace Drupal\Core\Attribute;
use Attribute;

/**
 * Attribute to log function execution.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class LogFunction {
    public function __construct() {}
}
```

### Example Usage
To log a method execution, simply add `#[LogFunction]` to a controller method:
```php
namespace Drupal\logattribute\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Attribute\LogFunction;

class LogAttributeController extends ControllerBase {
    #[LogFunction]
    public function test() {
        return ['#markup' => 'Hello, world!'];
    }
}
```

### Route Definition
This method is exposed via `/logattribute`:
```yaml
logattribute.test:
  path: '/logattribute'
  defaults:
    _controller: 'Drupal\\logattribute\\Controller\\LogAttributeController::test'
    _title: 'Attribute Test'
  requirements:
    _permission: 'access content'
```

## Testing
### Step 1: Access the Route
Visit the following URL in your browser:
```
http://your-drupal-site.com/logattribute
```
You should see **"Hello, world!"** displayed on the screen.

### Step 2: Check Logs
Run the following command to verify logs:
```sh
 drush ws --type=logattribute
```
Or check logs in **Reports → Recent log messages** in the Drupal admin panel.

## Conclusion
The **LogAttribute** module simplifies logging in Drupal controllers by leveraging PHP attributes and event listeners. It allows seamless logging without explicit logging calls in every function, improving maintainability and readability of the code.

---

*Developed for Drupal 11.*

