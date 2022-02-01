# mattjmattj/range-parser

A PHP hold'em range parser

## Installation

No published package yet, so you'll have to clone the project manually, or add a
[VCS repository](https://getcomposer.org/doc/05-repositories.md#vcs) to your composer.json
in order to require the code into your project.

## Usage

Split a range into a list of hands

```php
$parser = new RangeParser;
$hands = $parser->split('KK-TT,AQs-A9s,AKo,KJo+');
// $hands == ['KK','QQ','JJ','TT','AQs','AJs','ATs','A9s','AKo','KJo','KQo']
```

Or compact hands into a range

```php
$parser = new RangeParser;
$range = $parser->compact(['AA','KK','QQ','AKs']);
// $range === 'QQ+,AKs'
```