<?php

namespace Drupal\icms_admin\EventSubscriber;

use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigImporterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to install webforms after config import.
 *
 * This ensures webforms are installed after site installation with --existing-config,
 * where config import would otherwise remove webforms created during hook_install().
 *
 * How it works:
 * 1. Modules install and run hook_install() (webforms created)
 * 2. Config import runs (webforms removed by config sync)
 * 3. This event subscriber fires after config import (webforms recreated)
 *
 * To add a new webform:
 * 1. Create the webform YAML in your module's config/install/ directory
 * 2. Add the webform to the $webformsToInstall array below
 * 3. The webform will auto-install during site installation and module installation
 */
class WebformInstallSubscriber implements EventSubscriberInterface {

  /**
   * List of webforms to install after config import.
   *
   * Add any webforms that should be automatically installed here.
   * Format: ['config_name' => 'module_name']
   *
   * @var array
   */
  protected static $webformsToInstall = [
    'webform.webform.contact' => 'icms_core_logic',
    'webform.webform.icms_event_registration' => 'icms_bundle_event_logic',
  ];

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Subscribe to config import completion event.
    // Use a low priority to ensure this runs after all other import operations.
    $events[ConfigEvents::IMPORT][] = ['onConfigImport', -100];
    return $events;
  }

  /**
   * Reacts to config import completion.
   *
   * @param \Drupal\Core\Config\ConfigImporterEvent $event
   *   The config importer event.
   */
  public function onConfigImport(ConfigImporterEvent $event) {
    // Only proceed if the import was successful.
    if (!$event->getConfigImporter()->hasUnprocessedConfigurationChanges()) {
      foreach (self::$webformsToInstall as $config_name => $module_name) {
        // Check if the module is installed before trying to install its webform.
        if (\Drupal::moduleHandler()->moduleExists($module_name)) {
          icms_admin_install_webform($config_name, $module_name);
        }
      }
    }
  }

}
