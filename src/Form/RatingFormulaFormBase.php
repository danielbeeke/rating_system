<?php

/**
 * @file
 * Contains Drupal\rating_system\Form\RatingFormulaFormBase.
 */

namespace Drupal\rating_system\Form;

use Drupal;
use Drupal\rating_system\Rpn\RatingFormulaRpn;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RatingFormulaFormBase.
 *
 * Typically, we need to build the same form for both adding a new entity,
 * and editing an existing entity. Instead of duplicating our form code,
 * we create a base class. Drupal never routes to this class directly,
 * but instead through the child classes of RobotAddForm and RobotEditForm.
 *
 * @package Drupal\rating_system\Form
 *
 * @ingroup rating_system
 */
class RatingFormulaFormBase extends EntityForm {

  /**
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQueryFactory;

  /**
   * Construct the RatingFormulaFormBase.
   *
   * For simple entity forms, there's no need for a constructor. Our robot form
   * base, however, requires an entity query factory to be injected into it
   * from the container. We later use this query factory to build an entity
   * query for the exists() method.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   An entity query factory for the robot entity type.
   */
  public function __construct(QueryFactory $query_factory) {
    $this->entityQueryFactory = $query_factory;
  }

  /**
   * Factory method for RatingFormulaFormBase.
   *
   * When Drupal builds this class it does not call the constructor directly.
   * Instead, it relies on this method to build the new object. Why? The class
   * constructor may take multiple arguments that are unknown to Drupal. The
   * create() method always takes one parameter -- the container. The purpose
   * of the create() method is twofold: It provides a standard way for Drupal
   * to construct the object, meanwhile it provides you a place to get needed
   * constructor parameters from the container.
   *
   * In this case, we ask the container for an entity query factory. We then
   * pass the factory to our class as a constructor parameter.
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.query'));
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::form().
   *
   * Builds the entity add/edit form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   An associative array containing the robot add/edit form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get anything we need form the base class.
    $form = parent::buildForm($form, $form_state);

    $form['#tree'] = TRUE;

    // Drupal provides the entity to us as a class variable. If this is an
    // existing entity, it will be populated with existing values as class
    // variables. If this is a new entity, it will be a new object with the
    // class of our entity. Drupal knows which class to call from the
    // annotation on our Robot class.
    $formula = $this->entity;

    // Build the form.
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $formula->label(),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#title' => $this->t('Machine name'),
      '#default_value' => $formula->id(),
      '#machine_name' => array(
        'exists' => array($this, 'exists'),
        'replace_pattern' => '([^a-z0-9_]+)|(^custom$)',
        'error' => 'The machine-readable name must be unique, and can only contain lowercase letters, numbers, and underscores. Additionally, it can not be the reserved word "custom".',
      ),
      '#disabled' => !$formula->isNew(),
    );

    $entity_info = Drupal::entityManager()->getDefinitions();
    $entity_bundles = Drupal::entityManager()->getAllBundleInfo();

    $entity_type_options = array('_none' => $this->t('-Select-'));

    foreach ($entity_info as $entity_type => $entity_type_info) {
      if ($entity_type_info->getGroup() == 'content') {
        foreach ($entity_bundles[$entity_type] as $bundle_key => $bundle_label) {
          $entity_type_options[$entity_type][$entity_type . '|' . $bundle_key] = $bundle_label['label'];
        }
      }
    }

    $entity_type_and_bundle = $formula->entity_type() . '|' . $formula->entity_bundle();

    $form['entity_type_bundle'] = array(
      '#type' => 'select',
      '#title' => $this->t('Entity type and bundle'),
      '#options' => $entity_type_options,
      '#default_value' => $entity_type_and_bundle,
    );

    // Use the query factory to build a new robot entity query.
    $query = $this->entityQueryFactory->get('rs_package');
    $result = $query->execute();

    $packages = entity_load_multiple('rs_package', $result);

    $package_options = array();

    $form['formula'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Formulas')
    );

    $formulas = $formula->formulas();

    foreach ($packages as $package) {
      $form['formula'][$package->id] = array(
        '#required' => TRUE,
        '#type' => 'textfield',
        '#title' => $package->label,
        '#default_value' => $formulas[$package->id],
      );
    }

    // Return the form.
    return $form;
  }

  /**
   * Checks for an existing robot.
   *
   * @param string|int $entity_id
   *   The entity ID.
   * @param array $element
   *   The form element.
   * @param FormStateInterface $form_state
   *   The form state.
   *
   * @return bool
   *   TRUE if this format already exists, FALSE otherwise.
   */
  public function exists($entity_id, array $element, FormStateInterface $form_state) {
    // Use the query factory to build a new robot entity query.
    $query = $this->entityQueryFactory->get('rs_formula');

    // Query the entity ID to see if its in use.
    $result = $query->condition('id', $element['#field_prefix'] . $entity_id)
      ->execute();

    // We don't need to return the ID, only if it exists or not.
    return (bool) $result;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::actions().
   *
   * To set the submit button text, we need to override actions().
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   An array of supported actions for the current entity form.
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    // Get the basic actins from the base class.
    $actions = parent::actions($form, $form_state);

    // Change the submit button text.
    $actions['submit']['#value'] = $this->t('Save');

    // Return the result.
    return $actions;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::validate().
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   */
  public function validate(array $form, FormStateInterface $form_state) {
    parent::validate($form, $form_state);


    $values = $form_state->getValue('formula');

    foreach ($values as $package_id => $formula) {
      $rpn = new RatingFormulaRpn();

      // Small workaround if the current formula is simple just a number.
      $formula_result = $rpn->evaluate('0 + (' . $formula . ')');

      if (!is_numeric($formula_result)) {
        $form_state->setErrorByName('formula][' . $package_id, $formula_result);
      }
    }
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   *
   * Saves the entity. This is called after submit() has built the entity from
   * the form values. Do not override submit() as save() is the preferred
   * method for entity form controllers.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   */
  public function save(array $form, FormStateInterface $form_state) {
    // EntityForm provides us with the entity we're working on.
    $formula = $this->getEntity();

    $formula->formula = json_encode($form_state->getValue('formula'));

    $entity_type_and_bundle_exploded = explode('|', $formula->entity_type_bundle);

    $formula->entity_type = $entity_type_and_bundle_exploded[0];
    $formula->entity_bundle = $entity_type_and_bundle_exploded[1];

    // Drupal already populated the form values in the entity object. Each
    // form field was saved as a public variable in the entity class. PHP
    // allows Drupal to do this even if the method is not defined ahead of
    // time.
    $status = $formula->save();

    // Grab the URL of the new entity. We'll use it in the message.
    $url = $formula->urlInfo();

    // Create an edit link.
    $edit_link = $this->l(t('Edit'), $url);

    if ($status == SAVED_UPDATED) {
      // If we edited an existing entity...
      drupal_set_message($this->t('Rating formula %label has been updated.', array('%label' => $formula->label())));
      $this->logger('contact')->notice('Rating formula %label has been updated.', ['%label' => $formula->label(), 'link' => $edit_link]);
    }
    else {
      // If we created a new entity...
      drupal_set_message($this->t('Rating formula %label has been added.', array('%label' => $formula->label())));
      $this->logger('contact')->notice('Rating formula %label has been added.', ['%label' => $formula->label(), 'link' => $edit_link]);
    }

    // Redirect the user back to the listing route after the save operation.
    $form_state->setRedirect('rating_system.formula_list');
  }

}
