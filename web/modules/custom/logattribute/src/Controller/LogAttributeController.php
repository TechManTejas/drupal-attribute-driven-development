<?php

namespace Drupal\logattribute\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Attribute\LogFunction;

class LogAttributeController extends ControllerBase {
    
    #[LogFunction]
    public function test() {
        return ['#markup' => 'Hello, world!'];
    }
}

