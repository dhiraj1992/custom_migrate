<?php

namespace Drupal\custom_migrate\Plugin\migrate\destination;

use Drupal\migrate\Plugin\migrate\destination\DestinationBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Row;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @MigrateDestination(
 *   id = "location"
 * )
 */
class EntityLocationType extends DestinationBase implements ContainerFactoryPluginInterface {

  /**
   * @var array
   */
  protected $fields;
  
  /**
   * @var Drupal\Core\Config\ConfigFactoryInterface 
   */
  protected $config;

  /**
   * Drupal\Core\Entity\EntityFieldManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;
  
  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  
  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, ConfigFactoryInterface $config, EntityFieldManagerInterface $entityFieldManager, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    $this->config = $config->get('custom_migrate.mongotoentitymap');
    $this->entityFieldManager = $entityFieldManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->supportsRollback = TRUE;
    $this->fields = array_diff(array_keys($this->entityFieldManager->getFieldStorageDefinitions('location', 'city')), $this->getBaseFields());
  }
  
    /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('config.factory'),
      $container->get('entity_field.manager'),
      $container->get('entity_type.manager')
    );
  }
 

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'id  ' => [
        'type' => 'integer',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function fields(MigrationInterface $migration = NULL) {
    return $this->fields;
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = []) {
    $values['type'] = 'city';
    foreach ($this->fields as $field) {
      if ($this->config->get("location_city_$field")) {
        if (is_array($row->getSourceProperty($this->config->get("location_city_$field")))) {
          $values[$field] = implode(', ', $row->getSourceProperty($this->config->get("location_city_$field")));
        }
        else {
          $values[$field] = $row->getSourceProperty($this->config->get("location_city_$field"));
        }
      }
    }
    $location_entity = $this->entityTypeManager->getStorage('location')->create($values);
    if ($location_entity->save()) {
      return $row->getSourceIdValues();
    }
    else {
      return FALSE;
    }
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

