<?php

namespace Drupal\custom_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Provides a 'MongoToEntity' migrate source.
 *
 * @MigrateSource(
 *  id = "custom_migrate",
 *  source_module = "custom_migrate"
 * )
 */
class MongoToEntity extends SourcePluginBase {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      '_id' => $this->t('ID'),
      'city' => $this->t('City'),
      'loc' => $this->t('Location'),
      'pop' => $this->t('Population'),
      'state' => $this->t('State'),
    ];
  }

  /**
   * Data obtained from the source plugin configuration.
   *
   * @var array[]
   *   Array of data rows, each one an array of values keyed by field names.
   */
  protected $dataRows = [];

  /**
   * Description of the unique ID fields for this source.
   *
   * @var array[]
   *   Each array member is keyed by a field name, with a value that is an
   *   array with a single member with key 'type' and value a column type such
   *   as 'integer'.
   */
  protected $ids = [];

  /**
   * {@inheritdoc}
   */
//  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
//    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
//    $db = \Drupal::config('custom_migrate.mongodbcredentials');
//    $db_string = 'mongodb://' . $db->get('database_host') . ':' . $db->get('port') . '/' . $db->get('database_name');
//    $mongo = new \MongoDB\Driver\Manager($db_string);
//
//    $filter = [];
//    $options = [];
//
//    // Query mongo database
//    $query = new \MongoDB\Driver\Query($filter, $options);
//    $cursor = $mongo->executeQuery($db->get('database_name'), $query);
//    $cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);
//    $this->dataRows = $cursor->toArray();
//    $this->ids = $configuration['ids'];
//  }
      public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
//        $db = \Drupal::config('custom_migration_mongo.mongodbcredentials');
//        $db_string = 'mongodb://' . $db->get('database_host') . ':' . $db->get('port') . '/' . $db->get('database_name');
//        $mongo = new \MongoDB\Driver\Manager($db_string);

        $filter = [];
        $options = [];
         $row = json_decode(file_get_contents("http://freashd8.dd:8083/sites/default/files/cities.json"), true);
//        // Query mongo database
//        $query = new \MongoDB\Driver\Query($filter, $options);
//        $cursor = $mongo->executeQuery($db->get('database_name').'.cities', $query);
//        $cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);
        $this->dataRows = $row;
        $this->ids = $configuration['ids'];
    }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    return new \ArrayIterator($this->dataRows);
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return 'Mongo DB to Entity';
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return $this->ids;
  }

  /**
   * {@inheritdoc}
   */
  public function count($refresh = FALSE) {
    return count($this->dataRows);
  }

}
