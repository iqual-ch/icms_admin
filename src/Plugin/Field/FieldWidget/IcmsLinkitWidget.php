<?php

namespace Drupal\icms_admin\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\linkit\Plugin\Field\FieldWidget\LinkitWidget;

/**
 * Replaces the linkit widget to fix storing links to unrouted entity paths.
 *
 * Swapped in via icms_admin_field_widget_info_alter().
 */
class IcmsLinkitWidget extends LinkitWidget {

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$value) {
      // LinkWidget::validateUriElement() has already converted the raw user
      // input (e.g. "/media/6" inserted by the linkit autocomplete) into an
      // "internal:" URI. LinkitHelper::uriFromUserInput() only performs its
      // direct entity lookup for schemeless input, and the route-based
      // fallback fails for unrouted paths such as /media/N while
      // media.settings standalone_url is disabled — the link would be stored
      // as "internal:/media/N" and never resolve to the file URL (ICMS-595).
      // Strip the scheme again so linkit matches the entity from the path.
      // Only do so for actual paths: query- or fragment-only links (e.g.
      // "internal:#contact") can never reference an entity.
      if (is_string($value['uri'] ?? NULL) && str_starts_with($value['uri'], 'internal:/')) {
        $value['uri'] = substr($value['uri'], strlen('internal:'));
      }
    }
    return parent::massageFormValues($values, $form, $form_state);
  }

}
