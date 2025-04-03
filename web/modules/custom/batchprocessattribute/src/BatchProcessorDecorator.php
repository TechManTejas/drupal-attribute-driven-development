<?php

namespace Drupal\batchprocessattribute;

use Psr\Log\LoggerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use ReflectionMethod;
use Drupal\batchprocessattribute\Attribute\BatchProcess;

/**
 * A decorator that manages batch processing for attributed functions.
 */
class BatchProcessorDecorator {
  private object $service;
  private LoggerInterface $logger;

  public function __construct(object $service, LoggerChannelFactoryInterface $loggerFactory) {
    $this->service = $service;
    $this->logger = $loggerFactory->get('batchprocessattribute');
  }

  public function __call(string $method, array $arguments) {
    $reflection = new ReflectionMethod($this->service, $method);
    $attributes = $reflection->getAttributes(BatchProcess::class);

    if (!empty($attributes)) {
      $batchSize = $attributes[0]->newInstance()->batchSize;
      $data = $arguments[0] ?? [];
      $chunks = array_chunk($data, $batchSize);

      $results = [];
      foreach ($chunks as $chunk) {
        $this->logger->info("Processing batch of {count} items in {method}.", [
          'count' => count($chunk),
          'method' => $method,
        ]);
        $results[] = $reflection->invokeArgs($this->service, [$chunk]);
      }
      return $results;
    }
    return $reflection->invokeArgs($this->service, $arguments);
  }
}
