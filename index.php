<?php

/**
 * @file
 * Demonstration file of using the Highlighter library.
 */

require './vendor/autoload.php';

use writecrow\Highlighter\HighlightExcerpt;

$text = 'While settling this point, she was suddenly roused by the sound of the door-bell, and her spirits were a little fluttered by the idea of its being Colonel Fitzwilliam himself, who had once before called late in the evening, and might now come to inquire particularly after her. But this idea was soon banished, and her spirits were very differently affected, when, to her utter amazement, she saw Mr. Darcy walk into the room. In an hurried manner he immediately began an inquiry after her health, imputing his visit to a wish of hearing that she were better. She answered him with cold civility. He sat down for a few moments, and then getting up, walked about the room. Elizabeth was surprised, but said not a word. After a silence of several minutes, he came towards her in an agitated manner, and thus began: "In vain I have struggled."';
$tokens = [];
if (isset($_POST['token1'])) {
  $tokens[] = $_POST['token1'];
}
if (isset($_POST['token2'])) {
  $tokens[] = $_POST['token2'];
}
if (isset($_POST['token3'])) {
  $tokens[] = $_POST['token3'];
}
if (empty($tokens)) {
  $tokens = ['he', 'Cold', '. Elizabeth'];
}

echo '<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/skeleton/2.0.4/skeleton.min.css">
</head>
<body>';

echo '
<div class="container">
  <div class="row">
  <div class="six twelve columns">
    <span><h3>PHP Highlighter</h3><span class="u-pull-right">Source code: <a  href="https://github.com/writecrow/highlighter">https://github.com/writecrow/highlighter</a></span><hr />
  </div>
  <div class="six twelve columns">
    <h3>Text to be highlighted:</h3>' . $text . '<hr />
  </div>
  </div>
  <form action="//' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . '" method="POST">
    <div class="row">
      <div class="twelve columns">
        <label for="token1">Word/phrase to be highlighted</label>
        <input name="token1" value=\'' . $tokens[0] . '\'>
        <input name="token2" value=\'' . $tokens[1] . '\'>
        <input name="token3" value=\'' . $tokens[2] . '\'>
        <input type="submit" value="Highlight" />';
echo '<div>';
echo HighlightExcerpt::highlight($text, $tokens, $method = 'word', $length = 350);
echo '</div>';
echo '
      </div>
    </div>
  </form>
</div>
</body>
</html>';
