<?php

namespace Drupal\custom_migrate\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MongoDBCredentialsForm.
 */
class MongoDBCredentialsForm extends ConfigFormBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

    /**
   * Drupal\Core\Entity\EntityFieldManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->entityFieldManager = $container->get('entity_field.manager');
    $instance->database = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'custom_migrate.mongodbcredentials',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mongo_db_credentials_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $disabled = FALSE;
    if (!extension_loaded('mongodb')) { 
      $this->messenger()->addError("PHP Mongodb extension is not loaded.");
      $disabled = TRUE;
    }
    $config = $this->config('custom_migrate.mongodbcredentials');
    $form['database_host'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Database Host'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('database_host') ?? 'localhost',
      '#disabled' => $disabled,
    ];
    $form['port'] = [
      '#type' => 'number',
      '#title' => $this->t('Port'),
      '#maxlength' => 64,
      '#size' => 64,
      '#min' => 0,
      '#default_value' => $config->get('port') ?? '27017',
      '#disabled' => $disabled,
    ];
    $form['database_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Database Name'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('database_name'),
      '#disabled' => $disabled,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('custom_migrate.mongodbcredentials')
      ->set('database_host', $form_state->getValue('database_host'))
      ->set('port', $form_state->getValue('port'))
      ->set('database_name', $form_state->getValue('database_name'))
      ->save();
  }

}
