<?php

namespace Drupal\islandoraclaw\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\islandoraclaw\FedoraResourceInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Fedora resource entity.
 *
 * @ingroup islandoraclaw
 *
 * @ContentEntityType(
 *   id = "fedora_resource",
 *   label = @Translation("Fedora resource"),
 *   bundle_label = @Translation("Fedora resource type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\islandoraclaw\FedoraResourceListBuilder",
 *     "views_data" = "Drupal\islandoraclaw\Entity\FedoraResourceViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\islandoraclaw\Form\FedoraResourceForm",
 *       "add" = "Drupal\islandoraclaw\Form\FedoraResourceForm",
 *       "edit" = "Drupal\islandoraclaw\Form\FedoraResourceForm",
 *       "delete" = "Drupal\islandoraclaw\Form\FedoraResourceDeleteForm",
 *     },
 *     "access" = "Drupal\islandoraclaw\FedoraResourceAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\islandoraclaw\FedoraResourceHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "fedora_resource",
 *   data_table = "fedora_resource_field_data",
 *   revision_table = "fedora_resource_revision",
 *   revision_data_table = "fedora_resource_field_data_revision",
 *   admin_permission = "administer fedora resource entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   common_reference_target = TRUE,
 *   permission_granularity = "bundle",
 *   links = {
 *     "canonical" = "/admin/structure/fedora_resource/{fedora_resource}",
 *     "add-form" = "/admin/structure/fedora_resource/add/{fedora_resource_type}",
 *     "edit-form" = "/admin/structure/fedora_resource/{fedora_resource}/edit",
 *     "delete-form" = "/admin/structure/fedora_resource/{fedora_resource}/delete",
 *     "collection" = "/admin/structure/fedora_resource",
 *     "version-history" = "/admin/structure/{fedora_resource}/revisions",
 *     "revision" = "/admin/structure/{fedora_resource}/revisions/{node_revision}/view",
 *   },
 *   bundle_entity_type = "fedora_resource_type",
 *   field_ui_base_route = "entity.fedora_resource_type.edit_form"
 * )
 */
class FedoraResource extends ContentEntityBase implements FedoraResourceInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Reindex the entity when it is updated. The entity is automatically
    // indexed when it is added, simply by being added to the {fedora_resource} table.
    // Only required if using the core search index.
    if ($update) {
      if (\Drupal::moduleHandler()->moduleExists('search')) {
        search_mark_for_reindex('fedora_resource_search', $this->id());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->bundle();
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
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? NODE_PUBLISHED : NODE_NOT_PUBLISHED);
    return $this;
  }

  /**
   * Default value callback for 'fedora_has_parent' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getFedoraRoot() {
    return array('root');
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Fedora resource entity.'))
      ->setReadOnly(TRUE);
    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The Fedora resource type/bundle.'))
      ->setSetting('target_type', 'fedora_resource_type')
      ->setRequired(TRUE);
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Fedora resource entity.'))
      ->setReadOnly(TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Fedora resource entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
      
      $fields['fedora_has_parent'] = BaseFieldDefinition::create('entity_reference')
        ->setLabel(t('Fedora has Parent'))
        ->setDescription(t('Parent Fedora Resource.'))
        ->setRevisionable(TRUE)
        ->setSetting('target_type', 'fedora_resource')
        ->setSetting('handler', 'default')
        ->setDefaultValueCallback('Drupal\islandoraClaw\Entity\FedoraResource::getFedoraRoot')
        ->setTranslatable(TRUE)
        ->setDisplayOptions('view', array(
          'label' => 'hidden',
          'type' => 'author',
          'weight' => 0,
        ))
        ->setDisplayOptions('form', array(
          'type' => 'entity_reference_autocomplete',
          'weight' => 5,
          'settings' => array(
            'match_operator' => 'CONTAINS',
            'size' => '60',
            'autocomplete_type' => 'tags',
            'placeholder' => '',
          ),
        ))
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayConfigurable('view', TRUE);

        $fields['ldp_containes'] = BaseFieldDefinition::create('entity_reference')
          ->setLabel(t('LDP Contains'))
          ->setDescription(t('Contains Fedora Resource.'))
          ->setRevisionable(TRUE)
          ->setSetting('target_type', 'fedora_resource_type')
          ->setSetting('handler', 'default')
          ->setTranslatable(TRUE)
          ->setDisplayOptions('view', array(
            'label' => 'hidden',
            'type' => 'author',
            'weight' => 0,
          ))
          ->setDisplayOptions('form', array(
            'type' => 'entity_reference_autocomplete',
            'weight' => 5,
            'settings' => array(
              'match_operator' => 'CONTAINS',
              'size' => '60',
              'autocomplete_type' => 'tags',
              'placeholder' => '',
            ),
          ))
          ->setDisplayConfigurable('form', TRUE)
          ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Fedora resource entity.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Fedora resource is published.'))
      ->setDefaultValue(TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code for the Fedora resource entity.'))
      ->setDisplayOptions('form', array(
        'type' => 'language_select',
        'weight' => 10,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['promote'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Promoted to front page'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'boolean_checkbox',
        'settings' => array(
          'display_label' => TRUE,
        ),
        'weight' => 15,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['sticky'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Sticky at top of lists'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', array(
        'type' => 'boolean_checkbox',
        'settings' => array(
          'display_label' => TRUE,
        ),
        'weight' => 16,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['revision_timestamp'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Revision timestamp'))
      ->setDescription(t('The time that the current revision was created.'))
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Revision user ID'))
      ->setDescription(t('The user ID of the author of the current revision.'))
      ->setSetting('target_type', 'user')
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_log'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Revision log message'))
      ->setDescription(t('Briefly describe the changes you have made.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue('')
      ->setDisplayOptions('form', array(
        'type' => 'string_textarea',
        'weight' => 25,
        'settings' => array(
          'rows' => 4,
        ),
      ));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);
    return $fields;
  }

}
