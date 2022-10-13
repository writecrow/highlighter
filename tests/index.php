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
  'This is the first sentence. This is the second sentence and it contains argue. This is a third sentence.',
  'This is the first sentence. This is the second sentence. This is a third sentence and it contains argue.',
];

$tokens = ['argue'];

echo '<h2>Original sentences</h2>';
foreach ($texts as $key => $text) {
  echo '<div>';
  echo $key + 1 . ': ' . $text;
  echo '</div>';
}

echo '<h2>Crowcordance output for "argue"</h2>';
foreach ($texts as $key => $text) {
  echo '<div>';
  echo $key + 1 . ': ' . HighlightExcerpt::highlight($text, $tokens, $length = 350, $type = 'crowcordance');
  echo '</div>';
}

echo '<h2>KWIC output for "argue"</h2>';
foreach ($texts as $key => $text) {
  echo '<div class="kwic">';
  echo $key + 1 . ': ' . HighlightExcerpt::highlight($text, $tokens, $length = 100, $type = 'kwic');
  echo '</div>';
}

echo '<h2>Default results for "argue"</h2>';
foreach ($texts as $key => $text) {
  echo '<div>';
  echo $key + 1 . ': ' . HighlightExcerpt::highlight($text, $tokens, $length = 350);
  echo '</div>';
}

$tokens = ['argue', 'second'];


echo '<h2>Crowcordance output for "argue + second"</h2>';
foreach ($texts as $key => $text) {
  echo '<div>';
  echo $key + 1 . ': ' . HighlightExcerpt::highlight($text, $tokens, $length = 350, $type = 'crowcordance');
  echo '</div>';
}

echo '<h2>KWIC output for "argue + this"</h2>';
foreach ($texts as $key => $text) {
  echo '<div class="kwic">';
  echo $key + 1 . ': ' . HighlightExcerpt::highlight($text, $tokens, $length = 100, $type = 'kwic');
  echo '</div>';
}

echo '<h2>Default results for "argue + this"</h2>';
foreach ($texts as $key => $text) {
  echo '<div>';
  echo $key + 1 . ': ' . HighlightExcerpt::highlight($text, $tokens, $length = 350);
  echo '</div>';
}
