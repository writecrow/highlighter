<?php

namespace writecrow\Highlighter;

/**
 * Class HighlightExcerpt.
 *
 * Highlight words/phrases per specifications.
 *
 * @author markfullmer <mfullmer@gmail.com>
 *
 * @link https://github.com/writecrow/highlighter/
 *
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
class HighlightExcerpt {

  private static $regex = [
    'alpha_only' => [
      'start' => '/[^a-zA-Z>]',
      'end' => '[^a-zA-Z<]/',
    ],
    'non_alpha_only' => [
      'start' => '/(.)',
      'end' => '(.)/',
    ],
  ];

  /**
   * Given a word, return its lemma form.
   *
   * @param string $text
   *    The string to highlight.
   * @param string[] $tokens
   *    The words/phrases to be highlighted.
   * @param int $length
   *    The approximate target length of the entire excerpt.
   *
   * @return string
   *    The highlighted text.
   */
  public static function highlight($text, array $tokens, $length = '300') {
    $excerpt_list = [];
    $matches = [];
    $excerpt = "";
    $excerpt_list = [];
    $text = preg_replace('~[\r\n]+~u', '<br> ', $text);
    if ($length !== FALSE) {
      $text = str_replace("<br>", " ", $text);
    }
    // We pad this so that matches at the beginning & end of text are honoured.
    $text = ' ' . $text . ' ';
    if (empty($tokens)) {
      return mb_substr($text, 0, $length);
    }
    foreach ($tokens as $key => $value) {
      if (empty($value)) {
        unset($tokens[$key]);
      }
    }
    $ideal_length = self::getIdealLength($length, count($tokens));
    // The first loop simply retrieves the match metadata.
    foreach ($tokens as $token) {
      if (empty($token)) {
        continue;
      }
      $matches[$token] = self::findFirstMatchPosition($text, strip_tags($token, "<name><place><date>"));
    }
    if (empty($matches)) {
      return mb_substr($text, 0, $length);
    }
    if ($length === FALSE) {
      $excerpt = $text;
    }
    else {
      // Create the concatenated excerpt, pre-highlighting.
      foreach ($matches as $match) {
        if ($match['pos'] >= 0) {
          // If this is more than 50 characters into the start of the text,
          // start the excerpt at 50 characters before the instance.
          $rstart = $match['rstart'];
          $rend = $match['rend'];
          $start = $match['pos'] - 50 < 0 ? 0 : $match['pos'] - 50;
          $excerpt = mb_substr($text, $start, $ideal_length);
          $replacement = $match['f'] . '<mark>' . $match['string'] . '</mark>' . $match['l'];
          $excerpt_list[] = "..." . $excerpt . "...";
        }
        $excerpt = implode('<br />', $excerpt_list);
      }
    }

    // Now that the excerpt(s) are created, highlight all instances.
    foreach ($matches as $match) {
      if ($match['pos'] >= 0) {
        if ($match['sensitive']) {
          $replacement = $match['f'] . '<mark>' . $match['string'] . '</mark>' . $match['l'];
          $excerpt = preg_replace($match['rstart'] . preg_quote($match['string']) . $match['rend'], $replacement, $excerpt);
        }
        else {
          $replacement = $match['f'] . '<mark>' . mb_strtolower($match['string']) . '</mark>' . $match['l'];
          $excerpt = preg_replace($match['rstart'] . preg_quote(mb_strtolower($match['string'])) . $match['rend'], $replacement, $excerpt);
          $replacement = $match['f'] . '<mark>' . self::mbUcfirst($match['string']) . '</mark>' . $match['l'];
          $excerpt = preg_replace($match['rstart'] . preg_quote(self::mbUcfirst($match['string'])) . $match['rend'], $replacement, $excerpt);
        }
      }
    }
    // Finally, ensure that problematic characters are encoded (particularly for JSON).
    $str = htmlentities($excerpt,ENT_NOQUOTES,'UTF-8', FALSE);
    $str = str_replace(array('&lt;','&gt;'),array('<','>'), $str);
    $str = str_replace(array('&amp;lt;','&amp;gt'),array('&lt;','&gt;'), $str);
    return $str;
  }

  /**
   * Helper function to uppercase multibyte strings.
   */
  public static function mbUcfirst($str, $encoding = "UTF-8", $lower_str_end = FALSE) {
    $first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
    $str_end = "";
    if ($lower_str_end) {
      $str_end = mb_mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
    }
    else {
      $str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
    }
    $str = $first_letter . $str_end;
    return $str;
  }

  /**
   * Locate the first matching position in the text, plus metadata.
   *
   * @param string $text
   *   The original text to be highlighted.
   * @param string $token
   *   The token, potentially with quotation marks.
   */
  private static function findFirstMatchPosition($text, $token) {
    // Determine whether the token is quoted or not.
    $quoted = FALSE;
    $alpha = 'alpha_only';
    $falpha = 'alpha_only';
    $preg_i = '';
    $lalpha = 'alpha_only';
    $first = mb_substr($token, 0, 1);
    $last = mb_substr($token, -1);
    if ($first == '"' && $last == '"') {
      $token = trim($token, '"');
      $quoted = TRUE;
    }
    preg_match('/[^a-zA-Z]/', mb_substr($token, 0, 1), $non_alpha);
    if (isset($non_alpha[0])) {
      $falpha = 'non_alpha_only';
    }
    preg_match('/[^a-zA-Z]/', mb_substr($token, -1), $non_alpha);
    if (isset($non_alpha[0])) {
      $lalpha = 'non_alpha_only';
    }
    $rstart = self::$regex{$falpha}['start'];
    $rend = self::$regex{$lalpha}['end'];
    if (!$quoted) {
      $preg_i = 'iu';
    }
    preg_match($rstart . preg_quote($token) . $rend . $preg_i, $text, $match);
    if (isset($match[0])) {
      $first_char = mb_substr($match[0], 0, 1);
      $last_char = mb_substr($match[0], -1);
      if ($quoted) {
        $pos = mb_strpos($text, $match[0]);
      }
      else {
        $pos = mb_stripos($text, $match[0]);
      }
      if ($pos >= 0) {
        return [
          'string' => $token,
          'f' => $first_char,
          'l' => $last_char,
          'pos' => $pos,
          'sensitive' => $quoted,
          'rstart' => $rstart,
          'rend' => $rend,
        ];
      }
    }
    return ['pos' => -1];
  }

  /**
   * Winnow down the excerpt length if individual excerpts are supplied.
   */
  private static function getIdealLength($length, $count) {
    switch ($count) {
      case 1:
        return (int) $length;

      case 2:
        return (int) $length / 2;

      default:
        return (int) $length / 3;
    }
  }

}
