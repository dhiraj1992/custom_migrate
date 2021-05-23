<?php

namespace Drupal\custom_migrate\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerTrait;


/**
 * Defines the Location entity.
 *
 * @ingroup custom_migrate
 *
 * @ContentEntityType(
 *   id = "location",
 *   label = @Translation("Location"),
 *   bundle_label = @Translation("Location type"),
 *   handlers = {
 *     "storage" = "Drupal\custom_migrate\LocationStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\custom_migrate\LocationListBuilder",
 *     "views_data" = "Drupal\custom_migrate\Entity\LocationViewsData",
 *     "translation" = "Drupal\custom_migrate\LocationTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\custom_migrate\Form\LocationForm",
 *       "add" = "Drupal\custom_migrate\Form\LocationForm",
 *       "edit" = "Drupal\custom_migrate\Form\LocationForm",
 *       "delete" = "Drupal\custom_migrate\Form\LocationDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\custom_migrate\LocationHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\custom_migrate\LocationAccessControlHandler",
 *   },
 *   base_table = "location",
 *   data_table = "location_field_data",
 *   revision_table = "location_revision",
 *   revision_data_table = "location_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer location entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/location/{location}",
 *     "add-page" = "/admin/structure/location/add",
 *     "add-form" = "/admin/structure/location/add/{location_type}",
 *     "edit-form" = "/admin/structure/location/{location}/edit",
 *     "delete-form" = "/admin/structure/location/{location}/delete",
 *     "version-history" = "/admin/structure/location/{location}/revisions",
 *     "revision" = "/admin/structure/location/{location}/revisions/{location_revision}/view",
 *     "revision_revert" = "/admin/structure/location/{location}/revisions/{location_revision}/revert",
 *     "revision_delete" = "/admin/structure/location/{location}/revisions/{location_revision}/delete",
 *     "translation_revert" = "/admin/structure/location/{location}/revisions/{location_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/location",
 *   },
 *   bundle_entity_type = "location_type",
 *   field_ui_base_route = "entity.location_type.edit_form"
 * )
 */
class Location extends EditorialContentEntityBase implements LocationInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;
  use EntityOwnerTrait;


  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Location entity.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the Location is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
