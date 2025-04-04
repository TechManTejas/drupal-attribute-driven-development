<?php

namespace Drupal\batchprocessattribute;

use Psr\Log\LoggerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Batch\BatchBuilder;
use ReflectionMethod;

/**
 * A decorator that manages batch processing for attributed functions using Batch API.
 */
class BatchProcessorDecorator {
  private object $service;
  private LoggerInterface $logger;

  public function __construct(object $service, LoggerChannelFactoryInterface $loggerFactory) {
    $this->service = $service;
    $this->logger = $loggerFactory->get('batchprocessattribute');
  }

  public function __call(string $method, array $arguments) {
    $this->logger->info('Initializing batch processing for method {method}.', ['method' => $method]);
    
    $reflection = new ReflectionMethod($this->service, $method);

    $batch_builder = new BatchBuilder();
    $batch_builder->setTitle(t('Processing Batch'))
      ->setInitMessage(t('Initializing batch process...'))
      ->setProgressMessage(t('Processing batch...'))
      ->setErrorMessage(t('Batch processing encountered an error.'));
    
    $data = $arguments[0] ?? [];
    $this->logger->info('Batch contains {count} items.', ['count' => count($data)]);
    
    foreach ($data as $item) {
      $batch_builder->addOperation([
        self::class, 'processBatchOperation'
      ], [$this->service, $method, $item]);
    }
    
    batch_set($batch_builder->toArray());
  }

  /**
   * Callback function for batch processing.
   *
   * @param object $service
   *   The service instance.
   * @param string $method
   *   The method name to invoke.
   * @param mixed $item
   *   The item to process.
   * @param array $context
   *   The batch context array.
   */
  public static function processBatchOperation($service, $method, $item, &$context) {
    if (!isset($context['results'])) {
      $context['results'] = [];
    }
    
    
    $logger = \Drupal::logger('batchprocessattribute');
    $logger->info('Processing item in batch for method {method}: {item}', ['method' => $method, 'item' => json_encode($item)]);
    
    $reflection = new ReflectionMethod($service, $method);
    $context['results'][] = $reflection->invoke($service, [$item]);
    
    $logger->info('Completed processing item for method {method}', ['method' => $method]);
  }
}
