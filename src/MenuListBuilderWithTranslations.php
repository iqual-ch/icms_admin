<?php

namespace Drupal\icms_admin;

use Drupal\menu_ui\MenuListBuilder;

/**
 * Extends MenuListBuilder to display translated menu names.
 *
 * This overrides the default behavior that loads config entities without
 * language overrides, ensuring menu names appear in the current UI language.
 *
 * @see https://www.drupal.org/project/drupal/issues/3281219
 */
class MenuListBuilderWithTranslations extends MenuListBuilder {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entity_ids = $this->getEntityIds();
    // Use loadMultiple() instead of loadMultipleOverrideFree() to get
    // translations.
    $entities = $this->storage->loadMultiple($entity_ids);

    // Sort the entities using the entity class's sort() method.
    // See \Drupal\Core\Config\Entity\ConfigEntityBase::sort().
    uasort($entities, [$this->entityType->getClass(), 'sort']);
    return $entities;
  }

}
