<?php

namespace Drupal\guestBook\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Url;

/**
 * Class for confirm deleting.
 */
class AdminManageForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'admin_manage_form';
  }

  /**
   * Create data that will be displayed.
   */
  public function createTable(): array {
    // Connect to database and select fields.
    $table = \Drupal::database()
      ->select('guest_book', 'comments')
      ->fields('comments', [
        'id',
        'name',
        'email',
        'phone',
        'comment',
        'date',
        'image',
        'avatar',
      ])
      ->execute()
      ->fetchAll();

    // Creating array from database.
    $rows = [];
    foreach ($table as $row) {
      $image = $this->getImage($row->image);
      $avatar = $this->getImage($row->avatar);

      $url_edit = Url::fromRoute('guestBook.edit_manage', ['id' => $row->id], []);
      $linkEdit = $this->linkCreate('Edit', $url_edit);

      $rows[$row->id] = [
        'name' => $row->name,
        'email' => $row->email,
        'phone' => $this->userPhone($row->phone),
        'comment' => $row->comment,
        'image' => ['data' => $image],
        'avatar' => ['data' => $avatar],
        'date' => date('d-m-Y H:i:s', $row->date),
        'edit' => ['data' => $linkEdit],
      ];

    }

    if (!$rows == NULL) {
      krsort($rows);
    }

    return $rows;

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
   * Function get text and url for link and create renderer array.
   */
  public function linkCreate($title, $link):array {
    return [
      '#type' => 'link',
      '#title' => $title,
      '#url' => $link,
      '#options' => [
        'attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
        ],
      ],
      '#attached' => ['library' => ['core/drupal.dialog.ajax']],
    ];
  }

  /**
   * Function get image if it not null then renderer array is created.
   */
  public function getImage($image) {
    if ($image != NULL) {

      $file = File::load($image);
      $path = $file->getFileUri();
      $image_render = [
        '#theme' => 'image',
        '#uri' => $path,
        '#attributes' => [
          'alt' => 'picture',
          'width' => 250,
          'height' => 250,
        ],
      ];
    }
    else {
      $image_render = NULL;
    }
    return $image_render;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state):array {
    $header = [
      'name' => $this->t('name'),
      'email' => $this->t('email'),
      'phone' => $this->t('phone'),
      'comment' => $this->t('comment'),
      'image' => $this->t('image'),
      'avatar' => $this->t('avatar'),
      'date' => $this->t('date'),
      'edit' => $this->t('Edit'),
    ];

    $rows = $this->createTable();

    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $rows,
      '#title' => t('mange table'),
      '#empty' => t('No records found'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('delete'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $_SESSION['id'] = $form_state->getValue(['table']);
    $form_state->setRedirect('guestBook.confirm_manage');
  }

}
