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
  $tokens = ['Elizabeth', '', ''];
}
$type = "crowcordance";
if (isset($_POST['type'])) {
  $type = $_POST['type'];
}
echo '<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/skeleton/2.0.4/skeleton.min.css">
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
</head>
<body>';

echo '
<div class="container">
  <div class="row">
  <div class="six twelve columns">
    <span><h3>PHP Highlighter</h3><span class="u-pull-right">Source code: <a  href="https://github.com/writecrow/highlighter">https://github.com/writecrow/highlighter</a></span><br /><hr />
  </div>
  <div class="six twelve columns">
    <h4>Text to be highlighted:</h4>' . $text . '<hr />
  </div>
  </div>
  <form action="//' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . '" method="POST">
    <div class="row">
      <div class="twelve columns">
        <label for="token1">Word/phrase to be highlighted</label>
        <input name="token1" value=\'' . strip_tags($tokens[0]) . '\'>
        <input name="token2" value=\'' . strip_tags($tokens[1]) . '\'>
        <input name="token3" value=\'' . strip_tags($tokens[2]) . '\'>
        <br />
        <strong>Highlight style</strong>
        <select name="type">
          <option value="crowcordance">"Crowcordance"</option>
          <option value="kwic">Keyword in context</option>
          <option value="default">Multiple matching excerpts</option>
        </select>
        <ul>
          <li>"Crowcordance" attempts to render full sentences on either side of the target word. If full sentences are not present, it will render what is available.</li>
          <li>"Keyword in Context" attempts to render 0 words on either side of the target word.</li>
          <li>"Multiple matching excerpts" attempts to highlight multiple target words, if provided, similar to a search engine result excerpt</li>
        </ul>
        <input type="submit" value="Highlight" />';
echo '<div><h4>Highlighted Excerpt</h4>';
echo HighlightExcerpt::highlight($text, $tokens, $length = 350, $type);
echo '</div>';
echo '
      </div>
    </div>
  </form>
</div>
</body>
</html>';
