# Excerpt Highlighter

A PHP library for creating a highlighted excerpt a provided list of tokens in a provided text string.

## Basic usage in an application
The included `index.php` file contains a generation form demo.

Make your code aware of the Highlighter class via your favorite method
(e.g., `use writecrow\Highlighter\HighlightExcerpt;`)

```php
print HighlightExcerpt::highlight('Round the rugged rock,' ['the']);
// Will print 'Round <mark>the</mark> rugged rock'

print HighlightExcerpt::highlight('Round the rugged rock,' ['the', 'rock']);
// Will print 'Round <mark>the</mark> rugged <mark>rock</mark>'

print HighlightExcerpt::highlight('Round the rugged rock,' ['ro', '"round"']);
// Will print 'Round the rugged rock', since `ro` is present, 
// but only a word partial,
// and `round` is present, but double-quotes indicate case-sensitivity
// so `round` != `Round`

```

## Advanced behaviors
The excerpt highlighter will:
- Default to case-insensitive highlighting for a word (e.g., `'the'`)
- Become case-sensitive if the token is wrapped in double quotes (e.g. `'"The"'`)
- Not highlight partial word matched (e.g., `'the'` will not highlight `therefore`)
- Given multiple tokens passed to be highlighted, will create shorter, concatenated excerpts from the text which overall try to honor the length parameter specified (see below)
- Produce excerpts that start/end on word boundaries


### Length
Pass a 3rd parameter, an integer, to the function to specify the desired length of the excerpt.

```php
print HighlightExcerpt::highlight('Round the rugged rock,' ['the'], 300);
// Would limit the excerpt to 300 characters
```