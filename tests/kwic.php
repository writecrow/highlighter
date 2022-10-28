<style>
  .kwic {
    width: 100%;
  }

  .kwic span {
    display: inline-block;
  }

  .before {
    text-align: right;
    width: 40%
  }

  .target {
    padding: 0 1ch;
  }

  .after {
    text-align: left;
    width: 40%;
  }
</style>
<?php

require './../vendor/autoload.php';

use writecrow\Highlighter\HighlightExcerpt;

$texts = [
  'This is the first of three sentences and the first contains argue! This is the second sentence. This is a third sentence.',
  'This is the first sentence and contains argue. This is the second sentence and also contains argue. This is a third sentence. This is a very long sentence that should show up under some circumstances.',
  'This is the first sentence. This is the seconds sentence and it contains argue. This is a third sentence.',
  'This is the first sentence. This is the second sentence. This is a third sentence and it contains argue.',
];

$tokens = ['argue'];

echo '<h2>Kwic output for "argue"</h2>';
foreach ($texts as $key => $text) {
  echo '<div class="kwic">';
  echo $key + 1 . ': ' . HighlightExcerpt::highlight($text, $tokens, $length = 350, $type = 'kwic');
  echo '</div>';
}
$tokens = ['argue', 'second', 'seconds'];


echo '<h2>Kwic output for "argue + second + seconds"</h2>';
foreach ($texts as $key => $text) {
  echo '<div class="kwic">';
  echo $key + 1 . ': ' . HighlightExcerpt::highlight($text, $tokens, $length = 350, $type = 'kwic');
  echo '</div>';
}
