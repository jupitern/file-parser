
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

Given a csv file "csv.txt" with contents (animal, category, count):
crocodile,reptile,4
dolphin,mammal,0
duck,bird,2
koala,mammal,4
lion,mammal,5

lets:
    - convert encoding to ISO-9959-1
    - convert lines to objects
    - remove animals with count 0
    - format the animal type to uppercase
    - group by type

// read a file to array
$objectsArr = \Lib\Parser\Parser::instance()
    ->setFile("csv.txt", ',')
    ->setEncoding('ISO-8859-1', 'UTF-8')
    ->toObject(['animal', 'type', 'number'])
    ->filter(function ($line) {
        return $line->number > 0;
    })
    ->format('type', function ($val) {
        return strtoupper($val);
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
    [REPTILE] => Array(
            [0] => stdClass Object(
                    [animal] => crocodile
                    [type] => REPTILE
                    [number] => 4
                )
        )

    [BIRD] => Array(
            [0] => stdClass Object(
                    [animal] => duck
                    [type] => BIRD
                    [number] => 2
                )
        )

    [MAMMAL] => Array (
            [0] => stdClass Object(
                    [animal] => koala
                    [type] => MAMMAL
                    [number] => 4
                )
            [1] => stdClass Object(
                    [animal] => lion
                    [type] => MAMMAL
                    [number] => 5
                )
        )

    [FISH] => Array
        (
            [0] => stdClass Object(
                    [animal] => áéíóú
                    [type] => FISH
                    [number] => 3
                )
        )
)
*/


in the same file lets:
   - convert encoding to ISO-9959-1
   - convert lies to arrays
   - remove animals with count 0
   - group by type

$objectsArr = \Lib\Parser\Parser::instance()
    ->setFile("csv.txt", ',')
    ->setEncoding('ISO-8859-1', 'UTF-8')
    ->filter(function ($line) {
        return $line[2] > 0;
    })
    ->group(function ($line) {
        return $line[1];
    })
    ->parse();

echo '<pre>';
print_r($objectsArr);

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
            [1] => Array(
                    [0] => lion
                    [1] => mammal
                    [2] => 5
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


Given a csv file "file.txt" with contents (empolyee number, birth date, monthly income):
01john doe        1980-01-01          923.5
01luis west       1976-01-01         1143.3
01madalena        1983-01-01         2173.6
02Jaqueline Wayne 1983-01-01         822.44
05luís manuel    1983-01-01         1323.52

lets:
    - convert encoding to ISO-9959-1
    - convert lines to objects
    - format person name capitalize first letters
    - group by wage bellow or above 1000

$objectsArr = \Lib\Parser\Parser::instance()
    ->setFile("test.txt")
    ->setEncoding('ISO-8859-1', 'UTF-8')
    ->each(function ($line){
        $obj = [];
        $obj['Number'] = substr($line, 0, 2);
        $obj['Name'] = substr($line, 2, 16);
        $obj['BirthDate'] = substr($line, 18, 10);
        $obj['MonthlyIncome'] = (float)substr($line, 28, 15);
        return (object)$obj;
    })
    ->format('Name', function ($val) {
        return ucwords($val);
    })
    ->group(function ($line) {
        return (float)$line->MonthlyIncome >= 1000 ? 'above 1000' : 'bellow 1000';
    })
    ->parse();

echo '<pre>';
print_r($objectsArr);

/*
Output:
Array
(
    [bellow 1000] => Array(
            [0] => stdClass Object(
                    [Number] => 01
                    [Name] => John Doe
                    [BirthDate] => 1980-01-01
                    [MonthlyIncome] => 923.5
                )
            [1] => stdClass Object(
                    [Number] => 02
                    [Name] => Jaqueline Wayne
                    [BirthDate] => 1983-01-01
                    [MonthlyIncome] => 822.44
                )
        )
    [above 1000] => Array(
            [0] => stdClass Object(
                    [Number] => 01
                    [Name] => Luis West
                    [BirthDate] => 1976-01-01
                    [MonthlyIncome] => 1143.3
                )
            [1] => stdClass Object(
                    [Number] => 01
                    [Name] => Madalena
                    [BirthDate] => 1983-01-01
                    [MonthlyIncome] => 2173.6
                )
            [2] => stdClass Object(
                    [Number] => 05
                    [Name] => Luís Manuel
                    [BirthDate] => 1983-01-01
                    [MonthlyIncome] => 1323.52
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
