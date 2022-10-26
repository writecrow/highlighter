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

echo '<h1>Example output & questions for Crowcordance & KWIC excerpts</h1>';
echo '<h2>Sample "texts"</h2>';
foreach ($texts as $key => $text) {
  echo '<div>';
  echo $key + 1 . ': ' . $text;
  echo '</div>';
}

?>

<h2>Keyword in context (KWIC)</h2>
<ol>
  <li>Highlight a single search term, showing 10 words before and after it, including punctuation.</li>
  <li>Position target words of the results area. (Multiple excerpts will then align vertically on both Crow interface and iDDL).
  </li>
  <li>
    If multiple search terms are provided, KWIC will highlight the first one it finds.
  </li>
</ol>

<?php

$tokens = ['argue'];

echo '<h3>KWIC output for "argue"</h3>';
foreach ($texts as $key => $text) {
  echo '<div class="kwic">';
  echo $key + 1 . ': ' . HighlightExcerpt::highlight($text, $tokens, $length = 100, $type = 'kwic');
  echo '</div>';
}


$tokens = ['argue', 'second'];


echo '<h3>KWIC output for "argue + second"</h3>';
foreach ($texts as $key => $text) {
  echo '<div class="kwic">';
  echo $key + 1 . ': ' . HighlightExcerpt::highlight($text, $tokens, $length = 100, $type = 'kwic');
  echo '</div>';
}

// echo '<h2>Default results for "argue + this"</h2>';
// foreach ($texts as $key => $text) {
//   echo '<div>';
//   echo $key + 1 . ': ' . HighlightExcerpt::highlight($text, $tokens, $length = 350);
//   echo '</div>';
// }

?>

<h2>Crowcordance</h2>
<ol>
  <li>Highlight any search terms within a target sentence.</li>
  <li>Display a full sentence preceding and following that sentence.</li>
  <li>If there is no sentence in the original text that precedes the target sentence, display the text "[BEGINNING OF TEXT]" before the target sentence.</li>
  <li>If there is no sentence in the original text that follows the target sentence, display the text "[END OF TEXT]" after the target sentence.</li>
</ol>

<?php

$tokens = ['argue'];

echo '<h3>Crowcordance output for "argue"</h3>';
foreach ($texts as $key => $text) {
  echo '<div>';
  echo $key + 1 . ': ' . HighlightExcerpt::highlight($text, $tokens, $length = 350, $type = 'crowcordance');
  echo '</div>';
}

$tokens = ['argue', 'second'];


echo '<h3>Crowcordance output for "argue + second"</h3>';
foreach ($texts as $key => $text) {
  echo '<div>';
  echo $key + 1 . ': ' . HighlightExcerpt::highlight($text, $tokens, $length = 350, $type = 'crowcordance');
  echo '</div>';
}

