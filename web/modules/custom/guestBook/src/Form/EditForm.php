<?php

namespace Drupal\guestBook\Form;

use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\file\Entity\File;
use Drupal\Core\Url;

/**
 * Class EditForm that edit form.
 */
class EditForm extends FormBase {

  /**
   * Contain id of comment.
   */
  public $fid;

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'form_edit_comment';
  }

  /**
   * Build form with default data for editing.
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $id = \Drupal::routeMatch()->getParameter('id');

    $database = Database::getConnection();
    $query = $database->select('guest_book', 'comments')
      ->condition('id', $id)
      ->fields('comments');
    $data = $query->execute()->fetchAssoc();

    $this->fid = [
      'image' => $data['image'],
      'avatar' => $data['avatar'],
    ];

    $form['edit-name-valid'] = [
      '#type' => 'markup',
      '#markup' => '<div id="edit_name_message"></div>',
    ];
    $form['edit-name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your name:'),
      '#description' => $this->t('min length - 2 symbols, min - 100'),
      '#default_value' => (isset($data['name'])) ? $data['name'] : '',
      '#required' => TRUE,
      '#maxlength' => 100,
      '#pattern' => '^[\w+\s]{2,100}$',
      '#ajax' => [
        'callback' => '::validName',
        'event' => 'change',
      ],
    ];

    $form['edit_email-valid'] = [
      '#type' => 'markup',
      '#markup' => '<div id="edit_email_message"></div>',
    ];
    $form['edit-email'] = [
      '#type' => 'email',
      '#title' => $this->t('Your email:'),
      '#description' => $this->t('your@mail.com'),
      '#default_value' => (isset($data['email'])) ? $data['email'] : '',
      '#required' => TRUE,
      '#pattern' => '^[\w+]{2,100}@([\w+]{2,30})\.[\w+]{2,30}$',
      '#ajax' => [
        'callback' => '::validEmail',
        'event' => 'change',
      ],
    ];

    $form['edit-phone-valid'] = [
      '#type' => 'markup',
      '#markup' => '<div id="edit_phone_message"></div>',
    ];
    $form['edit-phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Your phone:'),
      '#placeholder' => '123 45 6789 1011',
      '#description' => $this->t('This field is required'),
      '#default_value' => (isset($data['phone'])) ? $data['phone'] : '',
      '#pattern' => '^[0-9]{12}$',
      '#maxlength' => 12,
      '#ajax' => [
        'callback' => '::validPhone',
        'event' => 'change',
      ],
    ];

    $form['edit-comment-valid'] = [
      '#type' => 'markup',
      '#markup' => '<div id="edit_comment_message"></div>',
    ];
    $form['edit-comment'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Your comment'),
      '#default_value' => (isset($data['comment'])) ? $data['comment'] : '',
      '#required' => TRUE,
      '#maxlength' => 1000,
    ];

    $form['edit-avatar'] = [
      '#type' => 'managed_file',
      '#title' => t('Your avatar'),
      '#upload_location' => 'public://images/avatars/',
      '#default_value' => (isset($data['avatar'])) ? [$data['avatar']] : [],
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [2097152],
      ],
    ];

    $form['edit-picture'] = [
      '#type' => 'managed_file',
      '#title' => t('Picture to comment'),
      '#upload_location' => 'public://images/pictures/',
      '#default_value' => (isset($data['image'])) ? [$data['image']] : [],
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [5242880],
      ],
    ];

    $form['edit-message'] = [
      '#type' => 'markup',
      '#markup' => '<div id="edit_result_message"></div>',
    ];
    $form['edit-submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add comment'),
      '#ajax' => [
        'callback' => '::setMessage',
        'event' => 'click',
      ],
    ];

    return $form;
  }

  /**
   * Function that validate name and display message with status of inputed data.
   */
  public function validName(array &$form, FormStateInterface $form_state) {
    $regular = '/^[\w+\s]{2,100}$/';
    $name = $form_state->getValue('edit-name');

    $response = new AjaxResponse();
    if (!preg_match($regular, $name)) {
      $response->AddCommand(
        new HtmlCommand(
          '#edit_name_message',
          '<div class="invalid-message">'
          . $this->t('You name must be longer than 2 symbols')
        )
      );
      $response->addCommand(
        new CssCommand('#edit-name', ['border-color' => 'red'])
      );
    }
    else {
      $response->AddCommand(
        new HtmlCommand(
          '#edit_name_message',
          '<div class="correct-message">'
          . $this->t('You name is correct')
        )
      );
      $response->addCommand(
        new CssCommand('#edit-name', ['border-color' => 'green'])
      );
    }
    return $response;
  }

  /**
   * Function that validate email and display message with status of inputed data.
   */
  public function validEmail(array &$form, FormStateInterface $form_state):object {
    $email = $form_state->getValue('edit-email');
    $regular = '/^[\w+]{2,100}@([\w+]{2,30})\.[\w+]{2,30}$/';

    $response = new AjaxResponse();
    if (!preg_match($regular, $email)) {
      $response->AddCommand(
        new HtmlCommand(
          '#edit_email_message',
          '<div class="invalid-message">'
          . $this->t('Email must be like this "yourname@mail.com"')
        )
      );
      $response->addCommand(
        new CssCommand('#edit-email', ['border-color' => 'red'])
      );
    }
    else {
      $response->AddCommand(
        new HtmlCommand(
          '#edit_email_message',
          '<div class="correct-message">'
          . $this->t('You email is correct')
        )
      );
      $response->addCommand(
        new CssCommand('#edit-email', ['border-color' => 'green'])
      );
    }
    return $response;
  }

  /**
   * Function that convert number in easy form.
   */
  public function userPhone($phone):string {

    $userPhone = '+';
    $length = strlen($phone);

    for ($i = 0; $i <= $length; $i++) {
      switch ($i) {
        case 2:
          $userPhone .= '(' . $phone[$i];
          break;

        case 4:
          $userPhone .= $phone[$i] . ')-';
          break;

        case 7:
          $userPhone .= $phone[$i] . '-';
          break;

        default:
          $userPhone .= $phone[$i];
      }
    }
    return $userPhone;
  }

  /**
   * Function that validate зрщту and display message with status of inputed data.
   */
  public function validPhone(array &$form, FormStateInterface $form_state):object {
    $phone = $form_state->getValue('edit-phone');
    $regular = '/^[0-9]{12}$/';

    $response = new AjaxResponse();
    if (!preg_match($regular, $phone)) {
      $response->AddCommand(
        new HtmlCommand(
          '#edit_phone_message',
          '<div class="invalid-message">'
          . $this->t('Your number must have 12 numbers')
        )
      );
      $response->addCommand(
        new CssCommand('#edit-phone', ['border-color' => 'red'])
      );
    }
    else {
      $response->AddCommand(
        new HtmlCommand(
          '#edit_phone_message',
          '<div class="correct-message">'
          . $this->t('You phone is') . ' ' . $this->userPhone($phone)
        )
      );
      $response->addCommand(
        new CssCommand('#edit-phone', ['border-color' => 'green'])
      );
    }
    return $response;
  }

  /**
   * Function check form on errors.
   */
  public function setMessage(array &$form, FormStateInterface $form_state):object {

    $response = new AjaxResponse();
    if ($form_state->hasAnyErrors()) {
      $response->AddCommand(
        new HtmlCommand(
          '#edit_result_message',
          '<div class="invalid-message">'
          . $this->t('Please enter correct information.')
        )
      );
    }
    else {
      \Drupal::messenger()->deleteAll();
      \Drupal::messenger()->addStatus(t('Comment was edited'));
      $response->addCommand(new RedirectCommand('\guest-book\comments'));
    }

    return $response;
  }

  /**
   * {@inheritDoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Function save file if it not null.
   */
  public function saveImage($picture) {
    if ($picture != NULL) {
      $file = File::load($picture[0]);
      $file->setPermanent();
      $file->save();
    }
  }

  /**
   * Function change status image in file_managed table if image was changed.
   */
  public function changStatus($newId, $currId) {
    if ($newId != $currId) {
      \Drupal::database()
        ->update('file_managed')
        ->fields(['status' => 0])
        ->condition('fid', $currId)->execute();
    }
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $fid = $this->fid;

    $picture = $form_state->getValue('edit-picture');
    $avatar = $form_state->getValue('edit-avatar');

    $data = [
      'name' => $form_state->getValue('edit-name'),
      'email' => $form_state->getValue('edit-email'),
      'phone' => $form_state->getValue('edit-phone'),
      'comment' => $form_state->getValue('edit-comment'),
      'avatar' => $avatar[0],
      'image' => $picture[0],
    ];

    $id = \Drupal::routeMatch()->getParameter('id');

    $this->saveImage($picture);
    $this->saveImage($avatar);

    $this->changStatus($picture, $fid['image']);
    $this->changStatus($avatar, $fid['avatar']);

    // Update data in database.
    \Drupal::database()
      ->update('guest_book')
      ->fields($data)
      ->condition('id', $id)
      ->execute();
  }

}
