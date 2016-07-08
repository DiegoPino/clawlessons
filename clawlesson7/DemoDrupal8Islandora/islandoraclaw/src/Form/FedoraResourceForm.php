<?php

namespace Drupal\islandoraclaw\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Fedora resource edit forms.
 *
 * @ingroup islandoraclaw
 */
class FedoraResourceForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\islandoraclaw\Entity\FedoraResource */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Fedora resource.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Fedora resource.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.fedora_resource.canonical', ['fedora_resource' => $entity->id()]);
  }

}
