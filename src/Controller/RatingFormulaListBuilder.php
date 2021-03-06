<?php
/**
 * @file
 * Contains Drupal\rating_system\Controller\RatingFormulaListBuilder.
 */

namespace Drupal\rating_system\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of rating formula entities.
 *
 * @package Drupal\rating_system\Controller
 *
 * @ingroup rating_system
 */
class RatingFormulaListBuilder extends ConfigEntityListBuilder {

  /**
   * Builds the header row for the entity listing.
   *
   * @return array
   *   A render array structure of header strings.
   *
   * @see Drupal\Core\Entity\EntityListController::render()
   */
  public function buildHeader() {
    $header['label'] = $this->t('Formula');
    $header['machine_name'] = $this->t('Machine Name');
    return $header + parent::buildHeader();
  }

  /**
   * Builds a row for an entity in the entity listing.
   *
   * @param EntityInterface $entity
   *   The entity for which to build the row.
   *
   * @return array
   *   A render array of the table row for displaying the entity.
   *
   * @see Drupal\Core\Entity\EntityListController::render()
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);
    $row['machine_name'] = $entity->id();

    return $row + parent::buildRow($entity);
  }
}
