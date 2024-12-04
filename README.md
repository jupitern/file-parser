
# jupitern/file-parser
#### PHP File Parser.

read, filter, parse and format {csv, tsv, dsv, variable-length-delimited} from files or strings

## Requirements

PHP 8.0 or higher.

## Installation

Include jupitern/file-parser in your project, by adding it to your composer.json file.
```javascript
{
    "require": {
        "jupitern/file-parser": "1.*"
    }
}
```

## Usage
```php

Lets parse a csv from a string with contents (animal, category, count):
animal,type,count
crocodile,reptile,4
dolphin,mammal,0
duck,bird,2
koala,mammal,4
lion,mammal,5

lets parse the file with:
    - ignore the first line
    - convert encoding from ISO-9959-1 to UTF-8
    - convert lines to objects
    - remove animals with count 0
    - format the animal type to uppercase
    - group by type

$objectsArr = \Jupitern\Parser\FileParser::instance()
            ->fromString('animal,type,count
crocodile,reptile,4
dolphin,mammal,0
duck,bird,2
koala,mammal,4
lion,mammal,5', ',')
            ->setEncoding('ISO-8859-1', 'UTF-8')
            ->toObject(['animal', 'type', 'animalCount'])
            ->filter(function ($line, $lineNumber) {
                return $lineNumber > 1 && $line->animalCount > 0;
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
    [REPTILE] => Array
        (
            [0] => stdClass Object
                (
                    [animal] => crocodile
                    [type] => REPTILE
                    [animalCount] => 4
                )

        )

    [BIRD] => Array
        (
            [0] => stdClass Object
                (
                    [animal] => duck
                    [type] => BIRD
                    [animalCount] => 2
                )

        )

    [MAMMAL] => Array
        (
            [0] => stdClass Object
                (
                    [animal] => koala
                    [type] => MAMMAL
                    [animalCount] => 4
                )

            [1] => stdClass Object
                (
                    [animal] => lion
                    [type] => MAMMAL
                    [animalCount] => 5
                )

        )

)
*/

Given a csv file "filename.csv" with contents (animal, category, count):
animal,type,count
crocodile,reptile,4
dolphin,mammal,0
duck,bird,2
koala,mammal,4
lion,mammal,5

lets parse the file with:
    - ignore the first line
    - convert encoding from ISO-9959-1 to UTF-8
    - convert lines to objects
    - remove animals with count 0
    - format the animal type to uppercase
    - group by type

// read a file to array
$objectsArr = \Jupitern\Parser\FileParser::instance()
    ->fromFile('D:\\aaa.txt', ',')
    ->setEncoding('ISO-8859-1', 'UTF-8')
    ->toObject(['animal', 'type', 'animalCount'])
    ->filter(function ($line, $lineNumber) {
        return $lineNumber > 1 && $line->animalCount > 0;
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
    [REPTILE] => Array
        (
            [0] => stdClass Object
                (
                    [animal] => crocodile
                    [type] => REPTILE
                    [animalCount] => 4
                )

        )

    [BIRD] => Array
        (
            [0] => stdClass Object
                (
                    [animal] => duck
                    [type] => BIRD
                    [animalCount] => 2
                )

        )

    [MAMMAL] => Array
        (
            [0] => stdClass Object
                (
                    [animal] => koala
                    [type] => MAMMAL
                    [animalCount] => 4
                )

            [1] => stdClass Object
                (
                    [animal] => lion
                    [type] => MAMMAL
                    [animalCount] => 5
                )

        )

)
*/


For the same file lets parse with:
   - convert encoding from ISO-9959-1 to UTF-8
   - convert lines to arrays
   - remove animals with count 0
   - group by type

$objectsArr = \Jupitern\Parser\FileParser::instance()
    ->setFile("csv.txt", ',')
    ->setEncoding('ISO-8859-1', 'UTF-8')
    ->filter(function ($line, $lineNumber) {
        return $lineNumber > 1 && $line[2] > 0;
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
            [0] => Array
                (
                    [0] => crocodile
                    [1] => reptile
                    [2] => 4
                )

        )

    [bird] => Array
        (
            [0] => Array
                (
                    [0] => duck
                    [1] => bird
                    [2] => 2
                )

        )

    [mammal] => Array
        (
            [0] => Array
                (
                    [0] => koala
                    [1] => mammal
                    [2] => 4
                )

            [1] => Array
                (
                    [0] => lion
                    [1] => mammal
                    [2] => 5
                )

        )

)
*/


Given a dsv file "file.txt" with contents (empolyee number, birth date, monthly income):
01john doe        1980-01-01          923.5
01luis west       1976-01-01         1143.3
01madalena        1983-01-01         2173.6
02Jaqueline Wayne 1983-01-01         822.44
05luís manuel     1983-01-01        1323.52

lets parse the file doing:
    - convert encoding from ISO-9959-1 to UTF-8
    - convert lines to objects
    - format person name capitalize first letters
    - group by wage bellow or above 1000

$objectsArr = \Jupitern\Parser\FileParser::instance()
    ->setFile("test.txt")
    ->setEncoding('ISO-8859-1', 'UTF-8')
    ->each(function ($line){
        $obj = [];
        $obj['Number'] = mb_substr($line, 0, 2);
        $obj['Name'] = mb_substr($line, 2, 16);
        $obj['BirthDate'] = mb_substr($line, 18, 10);
        $obj['MonthlyIncome'] = (float)mb_substr($line, 28, 15);
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
    [bellow 1000] => Array
        (
            [0] => stdClass Object
                (
                    [Number] => 01
                    [Name] => John Doe
                    [BirthDate] => 1980-01-01
                    [MonthlyIncome] => 923.5
                )

            [1] => stdClass Object
                (
                    [Number] => 02
                    [Name] => Jaqueline Wayne
                    [BirthDate] => 1983-01-01
                    [MonthlyIncome] => 822.44
                )

            [2] => stdClass Object
                (
                    [Number] => 05
                    [Name] => LuÃ­s Manuel
                    [BirthDate] =>  1983-01-0
                    [MonthlyIncome] => 1
                )

        )

    [above 1000] => Array
        (
            [0] => stdClass Object
                (
                    [Number] => 01
                    [Name] => Luis West
                    [BirthDate] => 1976-01-01
                    [MonthlyIncome] => 1143.3
                )

            [1] => stdClass Object
                (
                    [Number] => 01
                    [Name] => Madalena
                    [BirthDate] => 1983-01-01
                    [MonthlyIncome] => 2173.6
                )

        )

)
*/

```

## ChangeLog

v1.2.0

- min php version updated to 8.0
- code refactor for php8
- allow parse from string or file

v1
 - initial release

## Contributing

 - welcome to discuss a bugs, features and ideas.

## License

jupitern/file-parser is release under the MIT license.

You are free to use, modify and distribute this software
