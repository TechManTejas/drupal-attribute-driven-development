# Drupal Attribute-Driven Development

## Introduction
The **Attribute-Driven Development** project leverages PHP 8 attributes to enhance Drupal 11 development by providing reusable, declarative functionality through attributes. The project contains modules that demonstrate how to use attributes to simplify common Drupal tasks, reduce boilerplate code, and improve code maintainability.

## Modules
This project currently includes two modules:

### 1. LogAttribute Module
Introduces a custom `#[LogFunction]` attribute that automatically logs the execution of controller methods using Drupal's logging system.

### 2. Batch Process Attribute Module
Provides a `#[BatchProcess]` attribute that enables automatic batch processing for service methods, integrating with Drupal's Batch API.

## Requirements
- Drupal 11
- PHP 8.0 or higher
- Drush (for installation and cache clearing)

## Installation

### Option 1: Clone the Repository
```bash
git clone https://github.com/TechManTejas/drupal-attribute-driven-development.git
cd drupal-attribute-driven-development
```

### Option 2: Download as ZIP
Download the repository as a ZIP file from GitHub and extract it to your Drupal modules directory.

## Module-specific Installation

### LogAttribute Module
Follow these steps to install the LogAttribute module:

1. Place the required files in Drupal core:
   - Copy `LogFunction.php` to `core/lib/Drupal/Core/Attribute/`
   - Copy `LogFunctionHandler.php` to `core/lib/Drupal/Core/`
   - Copy `LogFunctionSubscriber.php` to `core/lib/Drupal/Core/EventSubscriber/`
   - Add the service definitions to `core/core.services.yml`

2. Enable the module:
   ```bash
   drush en logattribute -y
   ```

3. Clear the cache:
   ```bash
   drush cr
   ```

### Batch Process Attribute Module
1. Place the module in your Drupal custom modules directory (`modules/custom/`).
2. Enable the module:
   ```bash
   drush en batchprocessattribute -y
   ```
3. Clear the cache:
   ```bash
   drush cr
   ```

## Usage

### LogAttribute Module
Annotate controller methods with `#[LogFunction]` to automatically log their execution:

```php
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Attribute\LogFunction;

class LogAttributeController extends ControllerBase {
    #[LogFunction]
    public function test() {
        return ['#markup' => 'Hello, world!'];
    }
}
```

Access the test controller at `/logattribute` and check logs via:
```bash
drush ws --type=logattribute
```

### Batch Process Attribute Module
1. Annotate service methods with `#[BatchProcess]`:
   ```php
   use Drupal\batchprocessattribute\Attribute\BatchProcess;
   
   class BatchService {
       #[BatchProcess(10)]
       public function calculateResults(array $expressions) {
           // Method implementation
       }
   }
   ```

2. Access the test form at `/batchprocessattribute` to try batch processing functionality.

## How It Works

### LogAttribute Module
- Defines a custom PHP attribute `#[LogFunction]`
- Implements an event subscriber to intercept controller method calls
- Uses reflection to detect the attribute and log method execution

### Batch Process Attribute Module
- Defines a custom PHP attribute `#[BatchProcess]`
- Uses a decorator pattern to intercept calls to annotated methods
- Automatically splits processing into batches using Drupal's Batch API

## Testing
- LogAttribute: Visit `/logattribute` and check logs
- Batch Process: Use the form at `/batchprocessattribute` to test batch processing

---

*Developed for Drupal 11.*