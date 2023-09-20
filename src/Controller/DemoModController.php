<?php

namespace Drupal\demo_mod\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for demo_mod routes.
 */
class DemoModController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {
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
    
    dd($array);

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
