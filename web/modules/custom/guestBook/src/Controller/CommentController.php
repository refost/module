<?php

namespace Drupal\guestBook\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Drupal\Core\Url;

/**
 * Controller that use this class controll works of form.
 */
class CommentController extends ControllerBase {

  /**
   * The function passes data to display.
   */
  public function content():array {

    $form = \Drupal::formBuilder()->getForm('Drupal\guestBook\Form\AddCommentForm');

    $data = $this->getComments();

    return [
      '#theme' => 'guestBook_template',
      '#form' => $form,
      '#data' => $data,
    ];
  }

  /**
   * Create data that will be displayed.
   */
  public function getComments():array {

    // Connect to database and select fields.
    $results = \Drupal::database()
      ->select('guest_book', 'comment')
      ->fields('comment', [
        'id',
        'name',
        'email',
        'phone',
        'comment',
        'date',
        'image',
        'avatar',
      ])
      ->execute()->fetchAll();

    // Creating array from database.
    $commment = [];
    foreach ($results as $data) {
      $image = $this->getImage($data->image);
      $avatar = $this->getImage($data->avatar);

      // If there is no image then default is used.
      if ($avatar == NULL) {
        $avatar = [
          '#theme' => 'image',
          '#uri' => '/modules/custom/guestBook/files/User_Icon.png',
          '#attributes' => [
            'alt' => 'picture',
            'width' => 250,
            'height' => 250,
          ],
        ];
      }

      $url_delete = Url::fromRoute('guestBook.delete', ['id' => $data->id], []);
      $linkDelete = $this->linkCreate('Delete', $url_delete);

      $url_edit = Url::fromRoute('guestBook.edit', ['id' => $data->id], []);
      $linkEdit = $this->linkCreate('Edit', $url_edit);

      $phone = $this->userPhone($data->phone);

      $commment[] = [
        'name' => $data->name,
        'email' => $data->email,
        'phone' => $phone,
        'comment' => $data->comment,
        'image' => $image,
        'avatar' => $avatar,
        'date' => date('Y-m-d H:i:s', $data->date),
        'delete' => $linkDelete,
        'edit' => $linkEdit,
      ];
    }

    if ($commment != NULL) {
      krsort($commment);
    }

    return $commment;
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

}
