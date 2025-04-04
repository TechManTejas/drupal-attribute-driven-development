<?php

namespace Drupal\batchprocessattribute\Service;

use Drupal\Core\Batch\BatchBuilder;

/**
 * A test service to demonstrate batch processing without attributes.
 */
class BatchService {
  
  /**
   * Method to calculate results in batch.
   *
   * @param array $expressions
   *   An array of expressions to evaluate.
   *
   * @return array
   *   The results of evaluation.
   */
  public function calculateResults(array $expressions) {
    $batch = new BatchBuilder();
    $batch->setTitle(t('Processing Expressions'));
    
    foreach (array_chunk($expressions, 10) as $chunk) {
      $batch->addOperation([
        static::class, 'processBatch'
      ], [$chunk]);
    }
    
    batch_set($batch->toArray());
  }
  
  /**
   * Batch processing callback.
   *
   * @param array $expressions
   *   A chunk of expressions to process.
   * @param array $context
   *   The batch processing context.
   */
  public static function processBatch(array $expressions, array &$context) {
    foreach ($expressions as $expression) {
      $result = @eval("return $expression;") ?: 'ERROR';
      $context['results'][] = "$expression = $result";
    }
  }
}
