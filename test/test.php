<?php

include(dirname(__FILE__) . '/vendor/lime/lime.php');
include(dirname(__FILE__) . '/../PhpClassDepExtractor.php');


$t = new lime_test();
 

$sourceCode = '<?php $x = "class one"';
$expected = array();
$t->is(PhpClassDepExtractor::extractFromSourceCode($sourceCode), $expected);

$sourceCode = '<?php class Foo {}';
$expected = array('Foo' => array());
$t->is(PhpClassDepExtractor::extractFromSourceCode($sourceCode), $expected);

$sourceCode = '<?php class   
	Foo {

	}';
$expected = array('Foo' => array());
$t->is(PhpClassDepExtractor::extractFromSourceCode($sourceCode), $expected);

$sourceCode = '<?php class /* skip this */ Foo {}';
$expected = array('Foo' => array());
$t->is(PhpClassDepExtractor::extractFromSourceCode($sourceCode), $expected);

$sourceCode = '<?php class Foo extends Bar {}';
$expected = array('Foo' => array('Bar'));
$t->is(PhpClassDepExtractor::extractFromSourceCode($sourceCode), $expected);

$sourceCode = '<?php class Foo extends Bar implements Baz{}';
$expected = array('Foo' => array('Bar', 'Baz'));
$t->is(PhpClassDepExtractor::extractFromSourceCode($sourceCode), $expected);

$sourceCode = '<?php class Foo extends Bar implements Baz {} $x = "vas"; ?>';
$expected = array('Foo' => array('Bar', 'Baz'));
$t->is(PhpClassDepExtractor::extractFromSourceCode($sourceCode), $expected);

$sourceCode = '<?php 
/* letsgo */
class Foo 
/* parent */
extends Bar 
/* one interface */
implements Baz
{}';
$expected = array('Foo' => array('Bar', 'Baz'));
$t->is(PhpClassDepExtractor::extractFromSourceCode($sourceCode), $expected);

$sourceCode = '<?php 
/* letsgo */
class Foo 
/* parent */
extends Bar 
/* interfaces */
implements Baz , Blabla
{}';
$expected = array('Foo' => array('Bar', 'Baz', 'Blabla'));
$t->is(PhpClassDepExtractor::extractFromSourceCode($sourceCode), $expected);

$sourceCode = '<?php 
/* abtraction! */
abstract class Foo 
/* parent */
extends Bar 
/* interfaces */
implements Baz , Blabla
{}';
$expected = array('Foo' => array('Bar', 'Baz', 'Blabla'));
$t->is(PhpClassDepExtractor::extractFromSourceCode($sourceCode), $expected);

$sourceCode = '<?php interface   Ioo    {}';
$expected = array('Ioo' => array());
$t->is(PhpClassDepExtractor::extractFromSourceCode($sourceCode), $expected);

$sourceCode = '<?php 
interface   
Ioo    
{}';
$expected = array('Ioo' => array());
$t->is(PhpClassDepExtractor::extractFromSourceCode($sourceCode), $expected);

$sourceCode = '<?php interface   Ioo  extends Iar, Iaz   {}';
$expected = array('Ioo' => array('Iar', 'Iaz'));
$t->is(PhpClassDepExtractor::extractFromSourceCode($sourceCode), $expected);

$sourceCode = '<?php 
/* an interface */
interface  /*skipme*/  
Ioo 
/* extends others */
extends Iar /*and*/, Iaz   {}';
$expected = array('Ioo' => array('Iar', 'Iaz'));
$t->is(PhpClassDepExtractor::extractFromSourceCode($sourceCode), $expected);
