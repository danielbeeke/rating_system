<?php

/**
 * @file
 * Contains Drupal\rating_system\Form\RobotEditForm.
 */

namespace Drupal\rating_system\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class RobotEditForm
 *
 * Provides the edit form for our Robot entity.
 *
 * @package Drupal\rating_system\Form
 *
 * @ingroup rating_system
 */
class RatingPackageEditForm extends RatingPackageFormBase {

  /**
   * Returns the actions provided by this form.
   *
   * For the edit form, we only need to change the text of the submit button.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   An array of supported actions for the current entity form.
   */
  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = t('Update package');
    return $actions;
  }

}
