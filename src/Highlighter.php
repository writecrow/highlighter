<?php

namespace writecrow\Highlighter;

/**
 * Class Highlighter.
 *
 * Highlight words/phrases per specifications.
 *
 * @author markfullmer <mfullmer@gmail.com>
 *
 * @link https://github.com/writecrow/highlighter/
 *
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
class Highlighter {

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
   *   The string to highlight.
   * @param string[] $tokens
   *   The words/phrases to be highlighted.
   * @param int $length
   *   The approximate target length of the entire excerpt.
   * @param string $type
   *   The type of highlighting to perform (fixed, kwic, crowcordance, concat).
   *
   * @return string
   *   The highlighted text.
   */
  public static function process($text, array $tokens, $length = '300', $type = 'concat') {
    $matches = [];
    $excerpt = '';
    $highlighted = '';
    $text = self::cleanText($text);
    // Clean empty tokens.
    foreach ($tokens as $key => $value) {
      if (empty($value)) {
        unset($tokens[$key]);
      }
    }
    // There are no terms to highlight. Just return the requested length.
    if (empty($tokens)) {
      return mb_substr($text, 0, $length);
    }
    // Locate the first location of each token.
    foreach ($tokens as $token) {
      if (empty($token)) {
        continue;
      }
      $matches[$token] = self::findFirstMatchPosition($text, strip_tags($token, "<name><place><date>"));
    }
    if (empty($matches)) {
      return mb_substr($text, 0, $length);
    }
    if (empty($matches)) {
      return mb_substr($text, 0, $length);
    }

    switch ($type) {

      case 'crowcordance':
        $highlighted = self::getCrowcordance($text, $tokens);
        break;

      case 'all':

        $excerpt = $text;
        $highlighted = self::highlight($excerpt, $matches);
        break;

      case 'kwic':
      default:
        $highlighted = self::getKwic($text, $tokens, $matches);
        break;

    }
    return self::finalize($highlighted);
  }

  public static function highlight($excerpt, $matches) {
    foreach ($matches as $match) {
      if (!empty($match)) {
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
    return $excerpt;
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

  public static function getKwic($text, $tokens, $matches) {
    foreach ($matches as $match) {
      if (!empty($match)) {
        if ($match['pos'] < 50) {
          $text = self::mbStrPad($text, 50 - $match['pos'] + mb_strlen($text), ' ', STR_PAD_LEFT);
        }
        $start = $match['pos'] < 50 ? 0 : $match['pos'] - 50;
        $before = mb_substr($text, $start, 50);
        $after = mb_substr($text, $start + 51 + mb_strlen($match['string']), 50);
        if (mb_strlen($after) < 50) {
          $after = self::mbStrPad($after, 50 - mb_strlen($after), ' ', STR_PAD_RIGHT);
        }
        $chunks = [];
        array_push($chunks, '<span class="before">', $before, ' </span>');
        array_push($chunks, '<span class="target"><mark>', $match['string'], '</mark></span>');
        array_push($chunks, '<span class="after">', $after, '</span>');
        $highlighted = implode("", $chunks);
        // Return just the first match;
        return $highlighted;
      }
    }
  }

  public static function getIddl($text, $tokens, $matches) {
    print_r('here');
    foreach ($matches as $match) {
      if (!empty($match)) {
        if ($match['pos'] < 50) {
          $text = self::mbStrPad($text, 50 - $match['pos'] + mb_strlen($text), ' ', STR_PAD_LEFT);
        }
        $start = $match['pos'] < 50 ? 0 : $match['pos'] - 50;
        $before = mb_substr($text, $start, 50);
        $after = mb_substr($text, $start + 51 + mb_strlen($match['string']), 50);
        if (mb_strlen($after) < 50) {
          $after = self::mbStrPad($after, 50 - mb_strlen($after), ' ', STR_PAD_RIGHT);
        }
        $chunks = [$before, '<mark>', $match['string'], '</mark>', $after];
        $highlighted = implode("", $chunks);
        return $highlighted;
      }
    }
  }


  public static function getCrowcordance($text, $tokens) {
    $output = [
      'pre' => '[BEGINNING OF TEXT]',
      'target' => '',
      'post' => '[END OF TEXT]',
    ];
    $text = mb_convert_encoding($text, 'UTF-8', mb_list_encodings());
    $sentences = self::splitSentences($text);
    $found = [];
    for ($i = 0; $i < count($sentences); ++$i) {
      $clean_sentence = preg_replace("/\pP+/", " ", $sentences[$i]);
      foreach ($tokens as $token) {
        $clean_token = preg_replace("/\pP+/", "", $token);
        if (mb_strpos(mb_strtolower($clean_sentence), mb_strtolower($clean_token . ' ')) !== FALSE) {
          $found[] = $i;
        }
      }
    }
    if (empty($found)) {
      return 'Search term not found.';
    }
    // Handle scenario where the only the first sentence has a target.
    if (count($found) === 1 && $found == [0]) {
      $output['target'] = $sentences[0];
      if (isset($sentences[1])) {
        $output['post'] = $sentences[1];
      }
    }
    $first_match = reset($found);
    // If the first of multiple matches is the first sentence, skip to the second;
    if ($first_match === 0) {
      array_shift($found);
      $first_match = reset($found);
    }
    $preceding_sentence = (int) ($first_match - 1);
    $following_sentence = (int) ($first_match + 1);
    if (isset($sentences[$preceding_sentence])) {
      $output['pre'] = $sentences[$preceding_sentence];
    }
    $output['target'] = $sentences[$first_match];
    // Add the subsequent sentence if it is present.
    if (isset($sentences[$following_sentence])) {
      $output['post'] = $sentences[$following_sentence];
    }
    // Highlight any tokens in ONLY the target sentence.
    $output['target'] = self::process($output['target'], $tokens, FALSE, 'all');
    $prepared = implode(" ", [$output['pre'], $output['target'], $output['post']]);
    $prepared = trim($prepared);
    return preg_replace('/\s+/', ' ', $prepared);
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
    return [];
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

  public static function cleanText($text) {
    $text = preg_replace('~[\r\n]+~u', '<br> ', $text);
    $text = strip_tags($text);
    $text = str_replace("<br>", " ", $text);
    // Pad this so that matches at the beginning & end of text are honoured.
    $text = ' ' . $text . ' ';
    return $text;
  }

  public static function finalize($highlighted) {
    // Ensure problematic characters are encoded (particularly for JSON).
    $str = htmlentities($highlighted, ENT_NOQUOTES, 'UTF-8', FALSE);
    $str = str_replace(['&lt;', '&gt;'], ['<', '>'], $str);
    $str = str_replace(['&amp;lt;', '&amp;gt'], ['&lt;', '&gt;'], $str);
    return $str;
  }

}
