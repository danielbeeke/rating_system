<?php
/**
 * @file
 * Contains Drupal\rating_system\Controller\EntityScoreController.
 */

namespace Drupal\rating_system\Controller;

use Drupal;
use Drupal\rating_system\Rpn\RatingFormulaRpn;

/**
 * Controlles the formulas scores.
 *
 * @package Drupal\rating_system\Controller
 *
 * @ingroup rating_system
 */
class EntityScoreController {

  protected $entity;

  public function __construct($entity) {
    $this->entity = $entity;
  }

  protected function getBundlesWithFormulas () {
    $formulas = entity_load_multiple('rs_formula');

    foreach ($formulas as $formula) {
      $types[$formula->entity_type()][] = $formula->entity_bundle();
    }

    return $types;
  }

  protected function entityNeedsScores() {
    // Only for content entities.
    $entity_type = $this->entity->getEntityTypeId();
    $entity_info = Drupal::entityManager()->getDefinition($entity_type);
    if ($entity_info->getGroup() != 'content') { return; }

    // Only for entities that have a formula.
    $bundles_with_formulas = $this->getBundlesWithFormulas();
    if(!in_array($this->entity->bundle(), $bundles_with_formulas[$entity_type])) { return; }

    return TRUE;
  }

  protected function getActiveFormulasForEntity() {
    $formulas = entity_load_multiple('rs_formula');
    $active_formulas = array();

    $entity_type = $this->entity->getEntityTypeId();
    $entity_bundle = $this->entity->bundle();

    foreach ($formulas as $formula) {
      if ($formula->entity_type() == $entity_type && $formula->entity_bundle() == $entity_bundle) {
        $active_formulas[] = $formula;
      }
    }

    return $active_formulas;
  }

  public function getScores() {
    // Only give scores to entities that have formules for them.
    if (!$this->entityNeedsScores()) { return; }

    $formulas = $this->getActiveFormulasForEntity();
    $scores = array();

    foreach ($formulas as $formula) {
      $math_formulas = $formula->formulas();

      foreach ($math_formulas as $package_key => $math_formula) {
        $rpn = new RatingFormulaRpn();
        $formula_result = $rpn->evaluate('0 + (' . $math_formula . ')');

        if(is_numeric($formula_result)) {
          $scores[$formula->id()][$package_key] = $formula_result;
        }
      }
    }

    return $scores;
  }
}
