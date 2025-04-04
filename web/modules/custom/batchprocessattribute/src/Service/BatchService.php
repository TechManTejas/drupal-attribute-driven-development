<?php

namespace Drupal\batchprocessattribute\Service;

use Drupal\batchprocessattribute\Attribute\BatchProcess;

/**
 * A test service to demonstrate batch processing.
 */
class BatchService {
  
  /**
   * Method to calculate results in batch.
   *
   * @param array $expressions
   *   An array of expressions to evaluate.
   * #[BatchProcess(50)]
   * @return array
   *   The results of evaluation.
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