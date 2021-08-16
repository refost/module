<?php

namespace Drupal\guestBook\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;

/**
 * Class DeleteForm that delete form.
 */
class DeleteForm extends ConfirmFormBase {

  /**
   * Contain id of comment.
   */
  public $id;

  /**
   * {@inheritdoc}
   */
  public function getFormId():string {
    return 'delete_form';
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
    return new Url('guestBook.comments');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription():object {
    return t('Are you sure?');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText():object {
    return t('Delete it!');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText(): object {
    return t('Cancel');
  }

  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL):array {
    $this->id = $id;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
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

  /**
   * On submit form delete record.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $database = \Drupal::database();

    $num = $this->id;

    $query = $database->select('guest_book', 'comment');
    $result = $query->condition('id', $num)
      ->fields('comment', [
        'id',
        'image',
        'avatar',
      ])
      ->execute()->fetch();

    $result = json_decode(json_encode($result), TRUE);
    $this->changStatus($result['image']);
    $this->changStatus($result['avatar']);


    $database->delete('guest_book')
      ->condition('id', $this->id)
      ->execute();

    \Drupal::messenger()->addStatus('You successfully deleted record');
    $form_state->setRedirect('guestBook.comments');
  }

}
