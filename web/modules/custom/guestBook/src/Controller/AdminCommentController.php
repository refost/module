<?php

namespace Drupal\guestBook\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Admin controll class.
 */
class AdminCommentController extends ControllerBase {

  /**
   * Function displays control form.
   */
  public function content():array {
    return \Drupal::formBuilder()->getForm('Drupal\guestBook\Form\AdminManageForm');
  }

}
