<?php

namespace Drupal\batchprocessattribute\Attribute;

use Attribute;

/**
 * Attribute to handle batch processing automatically.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class BatchProcess {
  public int $batchSize;

  public function __construct(int $batchSize) {
    $this->batchSize = $batchSize;
  }
}
