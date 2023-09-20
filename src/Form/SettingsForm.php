<?php

namespace Drupal\demo_mod\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\File;
use Drupal\file\FileInterface;
use Drupal\taxonomy\Entity\Term;
use tidy;

/**
 * Configure demo_mod settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Contains the allowed file type  extension array.
   * 
   * @var array
   */
  protected $allowedTypes;

  /**
   * Contains the current folder name under process.
   * 
   * @var string
   */
  protected $folder;

  /**
   * Constructs the allowed file type extension array.
   */
  public function __construct() {
    $this->allowedTypes = [
      'image/png',
      'image/jpeg',
      'image/jpg',
      'image/gif',
      'image/webp',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'demo_mod_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['demo_mod.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $formatted_date = date('Y-m-d', time());
    $new_folder = 'private://' . $formatted_date;
    if (!is_dir($new_folder)) {
      mkdir($new_folder);
    }

    $form['image'] = [
      '#title' => t('Image file'),
      '#type' => 'managed_file',
      '#upload_location' => 'private://' . $formatted_date,
      '#multiple' => FALSE,
      '#description' => $this->t('Allowed extensions: mp4'),
      '#upload_validators' => [
        'file_validate_extensions' => ['jpg jpeg png gif webp'],
        'file_validate_size' => [25600000],
      ],
    ];

    $form['csv_input'] = [
      '#title' => t('CSV File'),
      '#type' => 'managed_file',
      '#upload_location' => 'private://csv_files',
      '#multiple' => FALSE,
      '#description' => $this->t('Allowed extensions: csv'),
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
        'file_validate_size' => [25600000],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // dd($form_state->getValue('image'));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file = \Drupal::entityTypeManager()->getStorage('file')->load($form_state->getValue('image')[0]);
    if ($file && in_array($file->getMimeType(), $this->allowedTypes)) {
      if ($this->manageFiles($file)) {
        $this->messenger()->addMessage($this->t('File saved'));
      }
      else {
        $this->messenger()->addError($this->t('File saved'));
      }
    }

    $handle = @fopen('private://csv_files/countries.csv', "r");
    $i = 0;
    if ($handle) {
      while (($row = fgetcsv($handle, 4096)) !== FALSE) {
        if (empty($fields)) {
          $fields = $row;
          continue;
        }
        foreach ($row as $k => $value) {
          $array[$i][$fields[$k]] = $value;
        }
        $i++;
      }
    }
    // dd($array);
    $batch = array(
      'title' => t('Creating Nodes...'),
      'operations' => array(
        [
          '\Drupal\demo_mod\CreateNode::createNodes',
          [$array]
        ],
      ),
      'finished' => '\Drupal\demo_mod\CreateNode::nodeCreationFinished',
    );

    batch_set($batch);
  }

  /**
   * Function to manage the file names.
   * 
   * @param \Drupal\file\FileInterface $file
   *   Takes the file too be managed.
   * 
   * @return bool
   *   Returns the bool based on the status of operation performed.
   */
  protected function manageFiles(FileInterface $file) {
    try {
      $current_time = time();
      $temp = explode('.', $file->get('filename')->value);
      $extension = '.' . $temp[count($temp) - 1];
      $temp = explode('/', $file->getFileUri());
      array_pop($temp);
      $file_uri = implode('/', $temp) . '/';
      $new_uri = $file_uri . $current_time . $extension;
      file_move($file, $new_uri);
      $file->setFilename($current_time . $extension);

      return TRUE;
    }
    catch (\Exception $e) {
      return FALSE;
    }
  }

}
