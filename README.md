
# jupitern/FileParser
#### PHP File Parser.

read, filter, parse and format {csv, tsv, dsv, variable-length-delimited} and other txt files

## Requirements

PHP 5.4 or higher.

## Installation

Include jupitern/FileParser in your project, by adding it to your composer.json file.
```javascript
{
    "require": {
        "jupitern/FileParser": "1.*"
    }
}
```

## Usage
```php

Given a csv file "file.txt" with contents:
crocodile,reptile,4
dolphin,mammal,0
duck,bird,2
koala,mammal,4

lets:
    - convert encofing to ISO-9959-1
    - convert lines to objects
    - remove animals with count 0
    - group by type

// read a file to array
$objectsArr = \Lib\Parser\Parser::instance()
    ->setFile("D:\\www\\csv.txt", ',')
    ->setEncoding('ISO-8859-1', 'UTF-8')
    ->toObject(['animal', 'type', 'number'])
    ->filter(function ($line) {
        return $line->number > 0;
    })
    ->group(function ($line) {
        return $line->type;
    })
    ->parse();

echo '<pre>';
print_r($objectsArr);

/*
output:
Array
(
    [reptile] => Array(
            [0] => stdClass Object(
                    [animal] => crocodile
                    [type] => reptile
                    [number] => 4
                )
        )

    [bird] => Array(
            [0] => stdClass Object(
                    [animal] => duck
                    [type] => bird
                    [number] => 2
                )
        )

    [mammal] => Array (
            [0] => stdClass Object(
                    [animal] => koala
                    [type] => mammal
                    [number] => 4
                )
        )

    [fish] => Array
        (
            [0] => stdClass Object(
                    [animal] => áéíóú
                    [type] => fish
                    [number] => 3
                )
        )
)
*/


in the same file lets:
   - convert encofing to ISO-9959-1
   - convert lies to arrays
   - remove animals with count 0
   - group by type

$objectsArr = \Lib\Parser\Parser::instance()
    ->setFile("D:\\www\\csv.txt", ',')
    ->setEncoding('ISO-8859-1', 'UTF-8')
    ->filter(function ($line) {
        return $line[2] > 0;
    })
    ->group(function ($line) {
        return $line[1];
    })
    ->parse();

/*
Output:
Array
(
    [reptile] => Array
        (
            [0] => Array(
                    [0] => crocodile
                    [1] => reptile
                    [2] => 4
                )
        )
    [bird] => Array(
            [0] => Array(
                    [0] => duck
                    [1] => bird
                    [2] => 2
                )
        )
    [mammal] => Array
        (
            [0] => Array(
                    [0] => koala
                    [1] => mammal
                    [2] => 4
                )
        )
    [fish] => Array
        (
            [0] => Array(
                    [0] => áéíóú
                    [1] => fish
                    [2] => 3
                )
        )
)
*/


```

## ChangeLog

 - initial release

## Contributing

 - welcome to discuss a bugs, features and ideas.

## License

jupitern/FileParser is release under the MIT license.

You are free to use, modify and distribute this software
