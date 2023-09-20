<?php

namespace Drupal\demo_mod;

use Drupal\Core\Messenger\MessengerTrait;
use Drupal\node\Entity\Node;
use Symfony\Contracts\Translation\TranslatorTrait;

class CreateNode {
	use TranslatorTrait;
	use MessengerTrait;

	public static function createNodes($array, &$context){
		$message = 'Creating Node...';
		$results = [];
		foreach ($array as $item) {
			$node = Node::create([
				'type' => 'demo',
				'title' => $item['name'],
				'field_iso3' => $item['iso3'],
				'field_iso2' => $item['iso2'],
				'field_currency' => $item['currency'],
		  ]);
			$node->save();

			$results[] = $node->id();
		}
		$context['message'] = $message;
		$context['results'] = $results;
	}

	public function nodeCreationFinished($success, $results, $operations) {
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One post processed.', '@count posts processed.'
      );
    }
    else {
      $message = $this->t('Finished with an error.');
    }
    $this->messenger()->addMessage($message);
	}

}
