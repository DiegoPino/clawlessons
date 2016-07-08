<?php
/**
 * @file
 * Contains \Drupal\islandoraclaw\FedoraResourceInterface.
 */

namespace Drupal\islandoraclaw;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface for defining a Fedora 4 Resource entity.
 *
 * This interface extends multiple ones so we can use all the extended ones at once.
 *
 * @ingroup islandoraclaw
 */
interface FedoraResourceInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
