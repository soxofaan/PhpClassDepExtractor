PhpClassDepExtractor
====================

Simple, lightweight, one-file, no dependency library to extract
class/interface hierarchies from PHP files and source code.


PhpClassHierarchy
=================

extends `PhpClassDepExtractor` to be a command line tool that spits json (parseable by [json](https://github.com/trentm/json)) when a class or regexp is provided
```
$ php PhpClassHierarchy.php <class|regexp> | json 
```
* if [json](https://github.com/trentm/json) is not found, it will output a PHP array.


### Example:

1. PhpClassHierarchy
```
$ php PhpClassHierarchy.php PhpClassHierarchy | json 
```
will output
```
  {
    "PhpClassHierarchy": [
      "PhpClassDepExtractor"
    ]
  }
```

2. PhpClass
```
$ php PhpClassHierarchy.php PhpClass | json 
```
will output
```
  {
    "0": "PhpClassDepExtractor",
    "PhpClassDepExtractionException": {
      "Exception": null
    },
    "PhpClassHierarchy": [
      "PhpClassDepExtractor"
    ]
  }
```

