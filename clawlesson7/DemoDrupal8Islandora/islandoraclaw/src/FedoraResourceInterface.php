<?php

namespace Drupal\islandoraclaw;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Fedora resource entities.
 *
 * @ingroup islandoraclaw
 */
interface FedoraResourceInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Fedora resource type.
   *
   * @return string
   *   The Fedora resource type.
   */
  public function getType();

  /**
   * Gets the Fedora resource name.
   *
   * @return string
   *   Name of the Fedora resource.
   */
  public function getName();

  /**
   * Sets the Fedora resource name.
   *
   * @param string $name
   *   The Fedora resource name.
   *
   * @return \Drupal\islandoraclaw\FedoraResourceInterface
   *   The called Fedora resource entity.
   */
  public function setName($name);

  /**
   * Gets the Fedora resource creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Fedora resource.
   */
  public function getCreatedTime();

  /**
   * Sets the Fedora resource creation timestamp.
   *
   * @param int $timestamp
   *   The Fedora resource creation timestamp.
   *
   * @return \Drupal\islandoraclaw\FedoraResourceInterface
   *   The called Fedora resource entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Fedora resource published status indicator.
   *
   * Unpublished Fedora resource are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Fedora resource is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Fedora resource.
   *
   * @param bool $published
   *   TRUE to set this Fedora resource to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\islandoraclaw\FedoraResourceInterface
   *   The called Fedora resource entity.
   */
  public function setPublished($published);

}
