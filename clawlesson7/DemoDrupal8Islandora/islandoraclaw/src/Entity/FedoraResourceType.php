<?php

namespace Drupal\islandoraclaw\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\islandoraclaw\FedoraResourceTypeInterface;

/**
 * Defines the Fedora resource type entity.
 *
 * @ConfigEntityType(
 *   id = "fedora_resource_type",
 *   label = @Translation("Fedora resource type"),
 *   handlers = {
 *     "list_builder" = "Drupal\islandoraclaw\FedoraResourceTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\islandoraclaw\Form\FedoraResourceTypeForm",
 *       "edit" = "Drupal\islandoraclaw\Form\FedoraResourceTypeForm",
 *       "delete" = "Drupal\islandoraclaw\Form\FedoraResourceTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\islandoraclaw\FedoraResourceTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "fedora_resource_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "fedora_resource",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/fedora_resource_type/{fedora_resource_type}",
 *     "add-form" = "/admin/structure/fedora_resource_type/add",
 *     "edit-form" = "/admin/structure/fedora_resource_type/{fedora_resource_type}/edit",
 *     "delete-form" = "/admin/structure/fedora_resource_type/{fedora_resource_type}/delete",
 *     "collection" = "/admin/structure/fedora_resource_type"
 *   }
 * )
 */
class FedoraResourceType extends ConfigEntityBundleBase implements FedoraResourceTypeInterface {

  /**
   * The Fedora resource type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Fedora resource type label.
   *
   * @var string
   */
  protected $label;

}
