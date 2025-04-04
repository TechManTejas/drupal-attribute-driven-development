# Batch Process Attribute Module

## Overview
The **Batch Process Attribute** module provides an automated way to handle batch processing in Drupal using PHP attributes. It enables developers to annotate service methods with `#[BatchProcess]` to automatically execute them as batch processes using the Drupal Batch API.

## Features
- Implements a custom `#[BatchProcess]` attribute for batch processing.
- Decorates the `BatchService` class using `BatchProcessorDecorator` to intercept and handle batch processing.
- Uses the Drupal Batch API for efficient execution of large data sets.
- Logs batch execution details for better debugging and monitoring.

## Installation
1. Place the `batchprocessattribute` module inside your Drupal custom modules directory (`modules/custom/`).
2. Enable the module using Drush:
   ```sh
   drush en batchprocessattribute -y
   ```
3. Clear the Drupal cache:
   ```sh
   drush cr
   ```

## Usage
### 1. Define a Batch-Enabled Service Method
Annotate a service method with `#[BatchProcess]` to enable batch processing. Example:

```php
use Drupal\batchprocessattribute\Attribute\BatchProcess;

class BatchService {
  /**
   * Processes expressions in batch.
   *
   * @param array $expressions
   *   An array of expressions to evaluate.
   *
   * @return array
   *   The processed results.
   */
  #[BatchProcess(10)]
  public function calculateResults(array $expressions) {
    $results = [];
    foreach ($expressions as $expression) {
      $results[] = "$expression = " . (@eval("return $expression;") ?: 'ERROR');
    }
    return $results;
  }
}
```

### 2. Ensure `BatchService` is Decorated
The `BatchService` must be decorated using `BatchProcessorDecorator` to enable batch handling. This is defined in `batchprocessattribute.services.yml`:

```yaml
services:
  batchprocessattribute.batch_service:
    class: 'Drupal\batchprocessattribute\Service\BatchService'
    public: true

  batchprocessattribute.batch_service.decorated:
    class: 'Drupal\batchprocessattribute\BatchProcessorDecorator'
    decorates: 'batchprocessattribute.batch_service'
    arguments: ['@batchprocessattribute.batch_service.decorated.inner', '@logger.factory']
    public: true

  batchprocessattribute.batch_service.decorated.inner:
    alias: batchprocessattribute.batch_service
    public: false
```

### 3. Using the Form to Test Batch Processing
The module includes a test form at `/batchprocessattribute` where users can generate mathematical expressions and process them in batches.

## Technical Details
### `BatchProcess` Attribute
Located in `src/Attribute/BatchProcess.php`, this attribute allows batch size configuration:

```php
#[Attribute(Attribute::TARGET_METHOD)]
class BatchProcess {
  public int $batchSize;

  public function __construct(int $batchSize) {
    $this->batchSize = $batchSize;
  }
}
```

### `BatchProcessorDecorator`
The decorator (`src/BatchProcessorDecorator.php`) intercepts calls to batch-annotated methods and processes them using the Batch API. It:
- Checks if the method has the `#[BatchProcess]` attribute.
- Splits data into chunks of the defined batch size.
- Uses Drupal's Batch API to process the data asynchronously.
- Logs batch execution details for debugging.

## Routing
The module provides a form accessible at:
```
/batchprocessattribute
```
Defined in `batchprocessattribute.routing.yml`:
```yaml
batchprocessattribute.test:
  path: '/batchprocessattribute'
  defaults:
    _form: 'Drupal\batchprocessattribute\Form\BatchTestForm'
    _title: 'Test Batch Process Attribute'
  requirements:
    _permission: 'access content'
```

## Logging
Batch processing logs can be found in Drupal's logs under the `batchprocessattribute` channel.
