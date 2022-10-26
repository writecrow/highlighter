<?php

require './../vendor/autoload.php';

use writecrow\Highlighter\HighlightExcerpt;

$texts = [
  'This is the first of three sentences and the first contains argue! This is the second sentence. This is a third sentence.',
  'This is the first sentence and contains argue. This is the second sentence and also contains argue. This is a third sentence. This is a very long sentence that should show up under some circumstances.',
  'This is the first sentence. This is the second sentence and it contains argue. This is a third sentence.',
  'This is the first sentence. This is the second sentence. This is a third sentence and it contains argue.',
];

$tokens = ['argue'];

echo '<h2>Crowcordance output for "argue"</h2>';
foreach ($texts as $key => $text) {
  echo '<div>';
  echo $key + 1 . ': ' . HighlightExcerpt::highlight($text, $tokens, $length = 350, $type = 'crowcordance');
  echo '</div>';
}
$tokens = ['argue', 'second'];


echo '<h2>Crowcordance output for "argue + second"</h2>';
foreach ($texts as $key => $text) {
  echo '<div>';
  echo $key + 1 . ': ' . HighlightExcerpt::highlight($text, $tokens, $length = 350, $type = 'crowcordance');
  echo '</div>';
}
