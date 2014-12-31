<?php

/**
 * @file
 * Contains \Drupal\rating_system\RatingPackageAccessController.
 */

namespace Drupal\rating_system;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an access controller for the robot entity.
 *
 * We set this class to be the access controller in RatingPackage's entity annotation.
 *
 * @see \Drupal\rating_system\Entity\RatingPackage
 *
 * @ingroup rating_system
 */
class RatingPackageAccessController extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    return parent::checkAccess($entity, $operation, $langcode, $account);
  }

}
