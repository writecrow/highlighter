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

?>
<h1>Example output & questions for Crowcordance & KWIC excerpts</h1>

<h2>Definition of current excerpt design</h2>
<ul><li><strong>KWIC</strong>: The Keyword in Context display will highlight the search term, showing 9 words before and after it, including all punctuation. The text will be positioned to center on the highlighted search term so that multiple results will align vertically.</li>
<li><strong>Crowcordance</strong>: The Crowcordance line will highlight the search term in a target sentence and will display a full sentence preceding and following that sentence, for the purpose of context (e.g., transitions).</li>
</ul>

<h2>Questions for the team</h2>
1. Crowcordance lines: in some texts, the target word may be located in the <strong>final</strong> sentence of the text, in which case a following sentence cannot be displayed. Should the Crowcordance display be redefined only to include a preceding sentence and not a following sentence? The alternative would be to display the following sentence when possible and omit it when not present.<br />
2. Crowcordance lines: in some texts, the target word may be located in the <strong>first</strong> sentence of the text. In this scenario, the Crowcordance line would not display a preceding sentence. Should some explanation be displayed about this, or is it sufficent to simply display the target word in the sentence containing it?<br />
3. The current excerpt highlighter in Crow's interface uses currently highlights <strong>all</strong> instance of the search term. Should the Crowcordance and KWIC displays follow suit (as can seen below with multiple highlights of "argue" in Sample Sentence 2)? Or should the Crowcordance and KWIC displays only highlight the target word once?<br />
4. The Crow interface allows for users to input <strong>multiple</strong> search terms, and the current excerpt highlighter will highlight all instances of each search term. Should the Crowcordance and KWIC displays follow suit (as shown below, where both "second" and "argue" are highlighted)? If not, how should the Crowcordance and KWIC displays handle searches for multiple terms?


<?php

$texts = [
  'This is the first of three sentences and the first contains argue! This is the second sentence. This is a third sentence.',
  'This is the first sentence and contains argue. This is the second sentence and also contains argue. This is a third sentence. This is a very long sentence that should show up under some circumstances.',
  'This is the first sentence. This is the second sentence and it contains argue. This is a third sentence.',
  'This is the first sentence. This is the second sentence. This is a third sentence and it contains argue.',
];

$tokens = ['argue'];

echo '<h2>Sample "texts"</h2>';
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

// echo '<h2>Default results for "argue"</h2>';
// foreach ($texts as $key => $text) {
//   echo '<div>';
//   echo $key + 1 . ': ' . HighlightExcerpt::highlight($text, $tokens, $length = 350);
//   echo '</div>';
// }

$tokens = ['argue', 'second'];


echo '<h2>Crowcordance output for "argue + second"</h2>';
foreach ($texts as $key => $text) {
  echo '<div>';
  echo $key + 1 . ': ' . HighlightExcerpt::highlight($text, $tokens, $length = 350, $type = 'crowcordance');
  echo '</div>';
}

echo '<h2>KWIC output for "argue + second"</h2>';
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
