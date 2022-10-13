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
      'start' => '/[\s\p{P}]',
      'end' => '[\s\p{P}]/',
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
  public static function highlight($text, array $tokens, $length = '300', $type = 'concat') {
    $excerpt_list = [];
    $matches = [];
    $excerpt = "";
    $excerpt_list = [];
    $text = preg_replace('~[\r\n]+~u', '<br> ', $text);
    // Remove HTML.
    $text = strip_tags($text);
    if ($length !== FALSE) {
      $text = str_replace("<br>", " ", $text);
    }
    // We pad this so that matches at the beginning & end of text are honoured.
    $text = ' ' . $text . ' ';
    // There are no terms to highlight. Just return the ideal length.
    if (empty($tokens)) {
      return mb_substr($text, 0, $length);
    }
    // Clean empty tokens.
    foreach ($tokens as $key => $value) {
      if (empty($value)) {
        unset($tokens[$key]);
      }
    }
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

    switch ($type) {
      case 'fixed':
        $excerpt = self::getFixed($text, $matches);
        break;
      case 'kwic':
        $excerpt = self::getKwic($text, $tokens);
        break;

      case 'crowcordance':
        $excerpt = self::getCrowcordance($text, $tokens);
        break;

      default:
        $ideal_length = self::getIdealLength($length, count($tokens));
        if ($length === FALSE) {
          $excerpt = $text;
        }
        // Create the concatenated excerpt, pre-highlighting.
        foreach ($matches as $match) {
          if ($match['pos'] >= 0) {
            // If this is more than 50 characters into the start of the text,
            // start the excerpt at 50 characters before the instance.
            $start = $match['pos'] - 50 < 0 ? 0 : $match['pos'] - 50;
            $excerpt = mb_substr($text, $start, $ideal_length);
            $excerpt_list[] = "..." . $excerpt . "...";
          }
          $excerpt = implode('<br />', $excerpt_list);
        }
        break;
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
    if ($type === 'kwic') {
      $parts = explode(" ", $excerpt);
      $chunks = ['<span class="before">'];
      for ($i = 0; $i < 10; ++$i) {
        $chunks[] = $parts[$i] . ' ';
      }
      $chunks[] = '</span><span class="target"> ' . $parts[10] . '</span><span class="after">';
      for ($i = 11; $i < 20; ++$i) {
        $chunks[] = $parts[$i] . ' ';
      }
      $chunks[] = '</span>';
      $excerpt = implode("", $chunks);
    }
    // Finally, ensure that problematic characters are encoded
    // (particularly for JSON).
    $str = htmlentities($excerpt, ENT_NOQUOTES, 'UTF-8', FALSE);
    $str = str_replace(['&lt;', '&gt;'], ['<', '>'], $str);
    $str = str_replace(['&amp;lt;', '&amp;gt'], ['&lt;', '&gt;'], $str);
    return $str;
  }

  public static function splitSentences($text) {
    $split_sentences = '%(?#!php/i split_sentences Rev:20160820_1800)
    # Split sentences on whitespace between them.
    # See: http://stackoverflow.com/a/5844564/433790
    (?<=          # Sentence split location preceded by
      [.!?]       # either an end of sentence punct,
    | [.!?][\'"]  # or end of sentence punct and quote.
    )             # End positive lookbehind.
    (?<!          # But don\'t split after these:
      Mr\.        # Either "Mr."
    | Mrs\.       # Or "Mrs."
    | Ms\.        # Or "Ms."
    | Jr\.        # Or "Jr."
    | Dr\.        # Or "Dr."
    | Prof\.      # Or "Prof."
    | Sr\.        # Or "Sr."
    | T\.V\.A\.   # Or "T.V.A."
                 # Or... (you get the idea).
    )             # End negative lookbehind.
    \s+           # Split on whitespace between sentences,
    (?=\S)        # (but not at end of string).
    %xi';
    return preg_split($split_sentences, $text, -1, PREG_SPLIT_NO_EMPTY);
  }

  public static function getFixed($text, $matches) {
    foreach ($matches as $match) {
      if ($match['pos'] >= 0) {
        // If this is more than 50 characters into the start of the text,
        // start the excerpt at 50 characters before the instance.
        $start = $match['pos'] - 60 < 0 ? 0 : $match['pos'] - 60;
        $excerpt = mb_substr($text, $start, $match['pos'] + 60 + mb_strlen($match['string']) - $start);
        if ($start == 0) {
          $excerpt = self::mbStrPad($excerpt, 120 + mb_strlen($match['string']));
        }
        break;
      }
    }
    return $excerpt;
  }

  public static function getKwic($text, $tokens) {
    $words = explode(' ', $text);
    $found = [];
    for ($i = 0; $i < count($words); ++$i) {
      if ($i < 9) {
        // Don't bother checking the first 9 words.
        continue;
      }
      foreach ($tokens as $token) {
        if (mb_strpos(mb_strtolower($words[$i]), mb_strtolower($token)) !== FALSE) {
          $found[] = $i;
        }
        if ($i > 20 && count($found) > 0) {
          // If we have a match in the first 20 words, stop looking.
          break;
        }
      }
    }
    if (!empty($found)) {
      $start = reset($found) - 10;
      $end = reset($found) + 10;
      for ($i = $start; $i < $end; ++$i) {
        if (isset($words[$i])) {
          $output[] = $words[$i];
        }
        else {
          $output[] = '&nbsp;';
        }
      }
    }
    else {
      $output = array_slice($words, 0, 19);
    }
    return implode(" ", $output);
  }

  public static function getCrowcordance($text, $tokens) {
    $text = mb_convert_encoding($text, 'UTF-8', mb_list_encodings());
    $sentences = self::splitSentences($text);
    $inc = 0;
    $found = [];
    for ($i = 0; $i < count($sentences); ++$i) {
      foreach ($tokens as $token) {
        if (mb_strpos(mb_strtolower($sentences[$i]), mb_strtolower($token)) !== FALSE) {
          $found[] = $i;
        }
      }
    }
    if (empty($found)) {
      return '';
    }
    // Handle scenario where the only sentence with a target token is the 1st.
    if (count($found) === 1 && $found == [0]) {
      // The only sentence in the text with the token is the first.
      $string = $sentences[0];
      if (isset($sentences[1])) {
        $string .= ' ' . $sentences[1];
      }
      return $string;
    }
    $output = [];
    $first_match = reset($found);
    if ($first_match === 0) {
      array_shift($found);
      $first_match = reset($found);
    }
    $preceding_sentence = (int) ($first_match - 1);
    $following_sentence = (int) ($first_match + 1);
    if (isset($sentences[$preceding_sentence])) {
      $output[] = $sentences[$preceding_sentence];
    }
    $output[] = $sentences[$first_match];
    // Add the subsequent sentence if it is present.
    if (isset($sentences[$following_sentence])) {
      $output[] = $sentences[$following_sentence];
    }
    return implode(" ", $output);
  }

  /**
   * Helper function to uppercase multibyte strings.
   */
  public static function mbUcfirst($str, $encoding = "UTF-8", $lower_str_end = FALSE) {
    $first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
    $str_end = "";
    if ($lower_str_end) {
      $str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
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
    preg_match('/[\s\p{P}]/u', mb_substr($token, 0, 1), $non_alpha);
    if (isset($non_alpha[0])) {
      $falpha = 'non_alpha_only';
    }
    preg_match('/[\s\p{P}]/u', mb_substr($token, -1), $non_alpha);
    if (isset($non_alpha[0])) {
      $lalpha = 'non_alpha_only';
    }
    $rstart = self::$regex[$falpha]['start'];
    $rend = self::$regex[$lalpha]['end'];
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

  /**
   * Multibye str_pad.
   *
   * @param string $input
   *   The input.
   * @param int $pad_length
   *   The amount to pad.
   * @param string $pad_string
   *   The character to use.
   * @param int $pad_type
   *   Left, right, or both.
   *
   * @return string
   *   The padded string.
   */
  private static function mbStrPad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_LEFT) {
    $diff = strlen($input) - mb_strlen($input);
    return str_pad($input, $pad_length + $diff, $pad_string, $pad_type);
  }

}
