<?php

namespace Drupal\custom_migrate\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Location type entity.
 *
 * @ConfigEntityType(
 *   id = "location_type",
 *   label = @Translation("Location type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\custom_migrate\LocationTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\custom_migrate\Form\LocationTypeForm",
 *       "edit" = "Drupal\custom_migrate\Form\LocationTypeForm",
 *       "delete" = "Drupal\custom_migrate\Form\LocationTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\custom_migrate\LocationTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "location_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "location",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/location_type/{location_type}",
 *     "add-form" = "/admin/structure/location_type/add",
 *     "edit-form" = "/admin/structure/location_type/{location_type}/edit",
 *     "delete-form" = "/admin/structure/location_type/{location_type}/delete",
 *     "collection" = "/admin/structure/location_type"
 *   }
 * )
 */
class LocationType extends ConfigEntityBundleBase implements LocationTypeInterface {

  /**
   * The Location type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Location type label.
   *
   * @var string
   */
  protected $label;

}
