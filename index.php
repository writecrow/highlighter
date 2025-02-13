<?php

/**
 * @file
 * Demonstration file of using the Highlighter library.
 */

require './vendor/autoload.php';

use writecrow\Highlighter\Highlighter;

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
<style>';
  require 'css/style.css';
echo '</style>
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
        <br />
        <strong>Highlight style</strong>
        <select name="type">
          <option value="crowcordance">"Crowcordance"</option>
          <option value="kwic" selected="selected">Keyword in context</option>
          <option value="all">All matching results</option>
        </select>
        <input type="submit" value="Highlight" />';
echo '<div><h4>Highlighted Excerpt</h4>';
echo Highlighter::process($text, $tokens, $length = 350, $type);
echo '</div>';
echo '
      </div>
    </div>
  </form>
</div>
</body>
</html>';
