<?php

namespace Drupal\islandoraclaw;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Fedora resource entities.
 *
 * @ingroup islandoraclaw
 */
class FedoraResourceListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Fedora resource ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\islandoraclaw\Entity\FedoraResource */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.fedora_resource.edit_form', array(
          'fedora_resource' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
