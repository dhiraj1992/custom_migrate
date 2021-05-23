<?php

namespace Drupal\custom_migrate\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class MongoToEntityMap.
 */
class MongoToEntityMapForm extends ConfigFormBase {

  /**
   * Drupal\Core\Entity\EntityFieldManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityFieldManager = $container->get('entity_field.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'custom_migrate.mongotoentitymap',
    ];
  }
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_migrate_map_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $entity = NULL, $bundle = NULL) {
    if (empty($entity) || empty($bundle)) {
      return new Response('Not found', Response::HTTP_NOT_FOUND);
    }
    $fields = array_diff(array_keys($this->entityFieldManager->getFieldStorageDefinitions($entity, $bundle)), $this->getBaseFields());
    $config = $this->config('custom_migrate.mongotoentitymap');
    $form['#_entity'] = $entity;
    $form['#_bundle'] = $bundle;
    $form['#prefix'] = '<div>'
            . 'The fields of entity ' . $entity . ', bundle ' . $bundle . ' are listed below.<br>'
            . 'Enter the field of the mongo db collection that you want to map to each entity field.<br>'
            . 'Empty fields will be ignored.<br></div>';
    foreach ($fields as $field) {
      $form["${entity}_${bundle}_${field}"] = [
        '#type' => 'textfield',
        '#title' => $this->t($field),
        '#default_value' => $config->get("${entity}_${bundle}_${field}"),
      ];
    };
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $entity = $form['#_entity'];
    $bundle = $form['#_bundle'];
    $fields = array_diff(array_keys($this->entityFieldManager->getFieldStorageDefinitions($entity, $bundle)), $this->getBaseFields());
    $config = $this->config('custom_migrate.mongotoentitymap');
    foreach ($fields as $field) {
      $config->set("${entity}_${bundle}_${field}",$form_state->getValue("${entity}_${bundle}_${field}") );
    }
    $config->save();
  }

  /**
   * Returns array of base fields.
   * 
   * @return array
   *   Fields to ignore.
   */
  private function getBaseFields() {
    return [
      'id',
      'uuid',
      'vid',
      'langcode',
      'type',
      'revision_created',
      'revision_user',
      'revision_log',
      'status',
      'created',
      'changed',
      'revision_translation_affected',
      'default_langcode',
      'revision_default',
    ];
  }

}
