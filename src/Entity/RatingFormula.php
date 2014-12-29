<?php

/**
 * @file
 * Contains Drupal\rating_system\Entity\RatingFormula.
 *
 * This contains our entity class.
 *
 * Originally based on code from blog post at
 * http://previousnext.com.au/blog/understanding-drupal-8s-config-entities
 */

namespace Drupal\rating_system\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the RatingFormula entity.
 *
 * The lines below, starting with '@ConfigEntityType,' are a plugin annotation.
 * These define the entity type to the entity type manager.
 *
 * The properties in the annotation are as follows:
 *  - id: The machine name of the entity type.
 *  - label: The human-readable label of the entity type. We pass this through
 *    the "@Translation" wrapper so that the multilingual system may
 *    translate it in the user interface.
 *  - controllers: An array specifying controller classes that handle various
 *    aspects of the entity type's functionality. Below, we've specified
 *    controllers which can list, add, edit, and delete our robot entity, and
 *    which control user access to these capabilities.
 *  - config_prefix: This tells the config system the prefix to use for
 *    filenames when storing entities. This means that the default entity we
 *    include in our module has the filename
 *    'rating_system.robot.marvin.yml'.
 *  - entity_keys: Specifies the class properties in which unique keys are
 *    stored for this entity type. Unique keys are properties which you know
 *    will be unique, and which the entity manager can use as unique in database
 *    queries.
 *
 * @see http://previousnext.com.au/blog/understanding-drupal-8s-config-entities
 * @see annotation
 * @see Drupal\Core\Annotation\Translation
 *
 * @ingroup rating_system
 *
 * @ConfigEntityType(
 *   id = "rating_formula",
 *   label = @Translation("Rating formula"),
 *   admin_permission = "administer rating system",
 *   handlers = {
 *     "access" = "Drupal\rating_system\RatingFormulaAccessController",
 *     "list_builder" = "Drupal\rating_system\Controller\RatingFormulaListBuilder",
 *     "form" = {
 *       "add" = "Drupal\rating_system\Form\RatingFormulaAddForm",
 *       "edit" = "Drupal\rating_system\Form\RatingFormulaEditForm",
 *       "delete" = "Drupal\rating_system\Form\RatingFormulaDeleteForm"
 *     }
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "edit-form" = "rating_system.rating_formula_edit",
 *     "delete-form" = "rating_system.rating_formula_delete"
 *   }
 * )
 */
class RatingFormula extends ConfigEntityBase {

  /**
   * The RatingFormula ID.
   *
   * @var string
   */
  public $id;

  /**
   * The RatingFormula UUID.
   *
   * @var string
   */
  public $uuid;

  /**
   * The RatingFormula label.
   *
   * @var string
   */
  public $label;

  /**
   * The formula.
   *
   * @var string
   */
  public $formula;

  public function formula () {
    return $this->formula;
  }
}
