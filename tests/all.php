<?php
require './../vendor/autoload.php';

use writecrow\Highlighter\HighlightExcerpt;

$tests = [];
$tests[] = [
 'input' => 'This is the first of three sentences and the first contains argue! This is the second sentence. This is a third sentence.',
 'tokens' => ['argue'],
 'type' => 'crowcordance',
 'output' => '[BEGINNING OF TEXT] This is the first of three sentences and the first contains <mark>argue</mark>! This is the second sentence.',
];
$tests[] = [
  'input' => 'This is the first sentence and contains argue. This is the second sentence and also contains argue. This is a third sentence. This is a very long sentence that should show up under some circumstances.',
  'tokens' => ['argue'],
  'type' => 'crowcordance',
  'output' => 'This is the first sentence and contains argue. This is the second sentence and also contains <mark>argue</mark>. This is a third sentence.',
];
$tests[] = [
  'input' => 'This is the first sentence. This is the seconds sentence and it contains argue. This is a third sentence.',
  'tokens' => ['argue'],
  'type' => 'crowcordance',
  'output' => 'This is the first sentence. This is the seconds sentence and it contains <mark>argue</mark>. This is a third sentence.',
];
$tests[] = [
  'input' => 'This is the first sentence. This is the second sentence. This is a third sentence and it contains argue.',
  'tokens' => ['argue'],
  'type' => 'crowcordance',
  'output' => 'This is the second sentence. This is a third sentence and it contains <mark>argue</mark>. [END OF TEXT]',
];

echo '<table border="1"><thead><td>Input</td><td>Search</td><td>Method</td><td>Output</td><td>Result</td></thead>';
foreach ($tests as $test) {
  $result = 'FAIL';
  $output = HighlightExcerpt::highlight($test['input'], $test['tokens'], FALSE, $test['type']);
  if ($output === $test['output']) {
    $result = 'PASS';
  }
  echo '<tr><td>' . $test['input'] . '</td><td>' . implode(",", $test['tokens']) . '</td><td>' . $test['type'] . '</td><td>' . $output . '</td><td>' . $result . '</td></tr>';
}
echo '</table>';
