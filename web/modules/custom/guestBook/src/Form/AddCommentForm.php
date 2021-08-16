<?php

namespace Drupal\guestBook\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Ajax\RedirectCommand;

/**
 * Class for work guest book form.
 */
class AddCommentForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'form_add_comment';
  }

  /**
   * Build form for guest book.
   */
  public function buildForm(array $form, FormStateInterface $form_state):array {

    $form['name-valid'] = [
      '#type' => 'markup',
      '#markup' => '<div id="name_message"></div>',
    ];
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your name:'),
      '#description' => $this->t('min length - 2 symbols, min - 100. This field is required'),
      '#required' => TRUE,
      '#maxlength' => 100,
      '#pattern' => '^[\d\D]{2,100}$',
      '#ajax' => [
        'callback' => '::validName',
        'event' => 'change',
      ],
    ];

    $form['email-valid'] = [
      '#type' => 'markup',
      '#markup' => '<div id="email_message"></div>',
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Your email:'),
      '#description' => $this->t('This field is required'),
      '#required' => TRUE,
      '#pattern' => '^[\w+]{2,100}@([\w+]{2,30})\.[\w+]{2,30}$',
      '#ajax' => [
        'callback' => '::validEmail',
        'event' => 'change',
      ],
    ];

    $form['phone-valid'] = [
      '#type' => 'markup',
      '#markup' => '<div id="phone_message"></div>',
    ];
    $form['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Your phone:'),
      '#placeholder' => '123 45 6789 1011',
      '#description' => $this->t('This field is required'),
      '#pattern' => '^[0-9]{12}$',
      '#maxlength' => 12,
      '#ajax' => [
        'callback' => '::validPhone',
        'event' => 'change',
      ],
    ];

    $form['comment-valid'] = [
      '#type' => 'markup',
      '#markup' => '<div id="comment_message"></div>',
    ];
    $form['comment'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Your comment'),
      '#description' => $this->t('This field is required. Max size - 1000 symbols'),
      '#required' => TRUE,
      '#maxlength' => 1000,
    ];

    $form['avatar'] = [
      '#type' => 'managed_file',
      '#title' => t('Your avatar'),
      '#upload_location' => 'public://images/avatars/',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [2097152],
      ],
    ];

    $form['picture'] = [
      '#type' => 'managed_file',
      '#title' => t('Picture to comment'),
      '#upload_location' => 'public://images/pictures/',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [5242880],
      ],
    ];

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div id="result_message"></div>',
    ];
    $form['submit'] = [
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
  public function validName(array &$form, FormStateInterface $form_state):object {
    $regular = '/^[\d\D]{2,100}$/';
    $name = $form_state->getValue('name');

    $response = new AjaxResponse();
    if (!preg_match($regular, $name)) {
      $response->AddCommand(
        new HtmlCommand(
          '#name_message',
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
          '#name_message',
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
    $email = $form_state->getValue('email');
    $regular = '/^[\w+]{2,100}@([\w+]{2,30})\.[\w+]{2,30}$/';

    $response = new AjaxResponse();
    if (!preg_match($regular, $email)) {
      $response->AddCommand(
        new HtmlCommand(
          '#email_message',
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
          '#email_message',
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

    for ($i = 0; $i < $length; $i++) {
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
    $phone = $form_state->getValue('phone');

    $regular = '/^[0-9]{12}$/';
    $response = new AjaxResponse();

    if (!preg_match($regular, $phone)) {
      $response->AddCommand(
        new HtmlCommand(
          '#phone_message',
          '<div class="invalid-message">'
          . $this->t('Your number must have 12 numbers. You can use only numbers')
        )
      );
      $response->addCommand(
        new CssCommand('#edit-phone', ['border-color' => 'red'])
      );
    }
    else {
      $response->AddCommand(
        new HtmlCommand(
          '#phone_message',
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
    \Drupal::messenger()->deleteAll();
    $response = new AjaxResponse();
    if ($form_state->hasAnyErrors()) {
      $response->AddCommand(
        new HtmlCommand(
          '#result_message',
          '<div class="invalid-message">'
          . $this->t('Please enter correct information.')
        )
      );
    }
    else {
      \Drupal::messenger()->addStatus(t('Thanks for sending. You can see your comment in down'));
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
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $picture = $form_state->getValue('picture');
    $avatar = $form_state->getValue('avatar');

    $this->saveImage($picture);
    $this->saveImage($avatar);

    // Insert data in database.
    \Drupal::database()
      ->insert('guest_book')
      ->fields([
        'name' => $form_state->getValue('name'),
        'email' => $form_state->getValue('email'),
        'phone' => $form_state->getValue('phone'),
        'comment' => $form_state->getValue('comment'),
        'avatar' => $avatar[0],
        'image' => $picture[0],
        'date' => time(),
      ])
      ->execute();
  }

}
