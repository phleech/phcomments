## About

A basic lightweight comment scraper for PornHub videos. 

## Install (Requires PHP 8.1+)

The recommended way to install PHComments is through Composer.

```bash
composer require phleech/phcomments
```

## Usage

### Example

```php
    use PHComments\Parser;

    $parser = new Parser();
    $comments = $parser->randomVideo()->getComments();

    // $comments = [
    //    {"body":"Identity theft is not a joke, Jim!","timestamp":"9 years ago","author":"TightyDwighty","votes":"4"},
    //    {"body":"Name?","timestamp":"8 months ago","author":"RandyStarbucks","votes":"0"}
    // ];
```

### Page selection

The `Parser` class exposes a `setPageUrl()` method used to specify a PornHub resource to scrape.

```php
    $parser->setPageUrl(url: 'view_video.php?viewkey=123456789');

    // results in the following page being scraped:
    // https://www.pornhub.com/view_video.php?viewkey=123456789.
``` 

> The default base path of `https://www.pornhub.com` is used with all requests and must not be present in the URL used in `setPageUrl()`.

There are two additional helper methods available which can be used to increase code readability:


`randomVideo()` is an alias of `setPageUrl('/video/random')`.

`setViewKey(viewKey: 'abcdef')` is an alias of `setPageUrl('view_video.php?viewkey=abcdef')`.

### Optional configuration

Any comments scraped are subject to a filter to remove those with a larger than specified author or body.

These restrictions are set in `Comment::DEFAULT_MAX_BODY_LENGTH` and `Comment::DEFAULT_MAX_AUTHOR_LENGTH`.

If you wish to use a different value for either/both of these restrictions then simply provide them when creating the `Parser` class.

```php
    use PHComments\Parser;

    $parser = new Parser(
        maxCommentBodyLength: 250
        maxCommentAuthorLength: 100
    );

    // results in any comments with a body greater than 250 chars
    // or an author greater than 100 chars being removed.
```

> These parameters are public on the `Parser` class and can be retrieved.

## Tests

The package has 100% code coverage.

To run the test suite run

```bash
./run-tests.sh
```
