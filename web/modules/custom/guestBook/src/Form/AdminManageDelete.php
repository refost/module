<?php

namespace Drupal\guestBook\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;

/**
 * Class DeleteForm.
 */
class AdminManageDelete extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId():string {
    return 'admin_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion():object {
    return t('Delete data');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl():object {
    return new Url('guestBook.manage');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription():object {

    if ($_SESSION['id'] != NULL) {
      $descript = t('Are you sure?');
    }
    else {
      $descript = t('Nothing to delete');
    }

    return $descript;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText():object {
    if ($_SESSION['id'] != NULL) {
      $descript = t('Delete it!');
    }
    else {
      $descript = t('Go back');
    }
    return $descript;
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText():object {
    return t('Cancel');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state):array {
    return parent::buildForm($form, $form_state);
  }

  /**
   * Function delete record and change file status.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get ID comment from _SESSION and delete it.
    $results = $_SESSION['id'];

    $database = \Drupal::database();

    foreach ($results as $id) {

      $results = $database
        ->select('guest_book', 'comments')
        ->condition('id', $id)
        ->fields('comments', ['id', 'image', 'avatar'])
        ->execute()
        ->fetch();

      $result = json_decode(json_encode($results), TRUE);
      $this->changStatus($result['image']);
      $this->changStatus($result['avatar']);

      $database
        ->delete('guest_book')
        ->condition('id', $id)
        ->execute();

    }
    if ($_SESSION['id'] != NULL) {
      \Drupal::messenger()->addStatus('You successfully deleted records');
    }
    $form_state->setRedirect('guestBook.manage');
  }

  /**
   * The function changes the status of deleted files.
   */
  public function changStatus($image) {
    if ($image != NULL) {

      $fid = intval($image);

      \Drupal::database()
        ->update('file_managed')
        ->fields(['status' => 0])
        ->condition('fid', $fid)->execute();
    }
  }

}
