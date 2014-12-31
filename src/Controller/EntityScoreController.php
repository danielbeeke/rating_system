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
    $types = array();
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
    if(!isset($bundles_with_formulas[$entity_type]) ||
      !in_array($this->entity->bundle(), $bundles_with_formulas[$entity_type])) { return; }

    return TRUE;
  }

  protected function getActiveFormulasForEntity() {
    $formulas = entity_load_multiple('rs_formula');
    $active_formulas = array();

    $entity_type = $this->entity->getEntityTypeId();
    $entity_bundle = $this->entity->bundle();

    if (isset($formulas)) {
      foreach ($formulas as $formula) {
        if ($formula->entity_type() == $entity_type && $formula->entity_bundle() == $entity_bundle) {
          $active_formulas[] = $formula;
        }
      }
    }

    return $active_formulas;
  }

  protected function fetchScore($formula, $package_id) {
    $score = db_select('rating_score', 'rs')
    ->condition('entity_type', $formula->entity_type())
    ->condition('entity_id', $this->entity->id())
    ->condition('rating_formula_id', $formula->id())
    ->fields('rs', array('score'))
    ->execute()
    ->fetchField();

    return $score;
  }

  protected function calculateScore($math_formula) {
    $rpn = new RatingFormulaRpn();
    $formula_result = $rpn->evaluate('0 + (' . $math_formula . ')');

    if(is_numeric($formula_result)) {
      return $formula_result;
    }
  }

  protected function writeScore($formula, $score, $package_id) {
    \Drupal::database()->merge('rating_score')
    ->key(array(
        'rating_formula_id' => $formula->id(),
        'entity_id' => $this->entity->id(),
        'package_id' => $package_id,
        'entity_type' => $formula->entity_type()
    ))
    ->fields(array(
        'score' => $score,
        'timestamp' => REQUEST_TIME,
    ))
    ->execute();
  }

  public function getScores() {
    // Only give scores to entities that have formules for them.
    if (!$this->entityNeedsScores()) { return; }

    // Get the right formulas.
    $formulas = $this->getActiveFormulasForEntity();
    $scores = array();

    // Split up to the deepest niveau: entity-formula-package specific.
    if (isset($formulas)) {
      foreach ($formulas as $formula) {
        $math_formulas = $formula->formulas();

        foreach ($math_formulas as $package_id => $math_formula) {
          // Try database.
          $database_score = $this->fetchScore($formula, $package_id);

          if ($database_score === FALSE) {
            // Calculate score and write it to database.
            $calculated_score = $this->calculateScore($math_formula);
            $this->writeScore($formula, $calculated_score, $package_id);

            $scores[$formula->id()][$package_id] = $calculated_score;
          }
          else {
            $scores[$formula->id()][$package_id] = $database_score;
          }
        }
      }
    }

    return $scores;
  }
}
