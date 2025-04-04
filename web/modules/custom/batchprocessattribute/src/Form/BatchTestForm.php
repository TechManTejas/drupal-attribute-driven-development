<?php

namespace Drupal\batchprocessattribute\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\batchprocessattribute\BatchProcessorDecorator;

/**
 * Form for testing the BatchProcess attribute.
 */
class BatchTestForm extends FormBase {
  protected BatchProcessorDecorator $batchService;

  /**
   * BatchTestForm constructor.
   *
   * @param \Drupal\batchprocessattribute\BatchProcessorDecorator $batchService
   *   The decorated batch service.
   */
  public function __construct(BatchProcessorDecorator $batchService) {
    $this->batchService = $batchService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('batchprocessattribute.batch_service.decorated')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'batchprocessattribute_test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['num_expressions'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of expressions to generate'),
      '#description' => $this->t('Enter the number of random mathematical expressions to generate and evaluate.'),
      '#min' => 1,
      '#default_value' => 100,
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate & Process'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $num_expressions = (int) $form_state->getValue('num_expressions');
    $expressions = $this->generateExpressions($num_expressions);
    
    $results = $this->batchService->calculateResults($expressions);
  }

  /**
   * Generates random mathematical expressions.
   *
   * @param int $count
   *   The number of expressions to generate.
   *
   * @return array
   *   An array of mathematical expressions.
   */
  private function generateExpressions(int $count): array {
    $operators = ['+', '-', '*', '/'];
    $expressions = [];
    
    for ($i = 0; $i < $count; $i++) {
      $num1 = rand(1, 100);
      $num2 = rand(1, 100);
      $operator = $operators[array_rand($operators)];
      
      // Ensure division does not cause division by zero
      if ($operator === '/' && $num2 === 0) {
        $num2 = 1;
      }
      
      $expressions[] = "$num1 $operator $num2";
    }
    
    return $expressions;
  }
}