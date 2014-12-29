<?php

/**
 * @file
 * Contains \Drupal\rating_system\Rpn\RatingFormulaRpn.
 */

namespace Drupal\rating_system\Rpn;

use Drupal\rating_system\Rpn\Math_Rpn;

/**
 * Overrides the PEAR error handler.
 *
 * @ingroup rating_system
 */
class RatingFormulaRpn extends Math_Rpn {
  function _raiseError($error) {
    return $error;
  }
}
