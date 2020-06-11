<?php

require_once '../src/XliffDocument.php';

echo "Generating new XLIFF document:" . PHP_EOL;
$xliff = new XliffDocument();

$xliff
	//create a new file element
	->file(TRUE)
		//create a new body element
		->body(TRUE)
			//create a new trans-unit element
			->unit(TRUE)
				//create a new source element
				->source(TRUE)
					->setTextContent("text 1")
					->setAttribute('xml:lang', 'en');
	
$xliff
	//use same file element as before
	->file()
		//use same body element as before
		->body()
			//use same trans-unit element as before
			->unit()
				//create a new target element
				->target(TRUE)
					->setTextContent("1 txet")
					->setAttribute('xml:lang', 'fr');
	
$xliff
	->file()
		->body()
			->unit(TRUE)
				->source(TRUE)
					->setTextContent("Hello world")
					->setAttribute('xml:lang', 'en');
$xliff
	->file()
		->body()
			->unit()
				->target(TRUE)
					->setTextContent("world hello")
					->setAttribute('xml:lang', 'fr');

//Add some custom tags which are not officially supported
$alt = new XliffNode();
$alt->setName('alt-trans');
$target2 = new XliffNode();
$target2->setName('target')->setTextContent("world hello 0")->setAttribute('xml:lang', 'fr');
$alt->appendNode($target2);

$xliff->file()->body()->unit()->appendNode($alt);

$dom = $xliff->toDOM();
$xml = $dom->saveXML();

echo $xml;

echo '=============================================='.PHP_EOL;
echo "Generating DOM from XLIFF document and back:" . PHP_EOL;
$dom2 = new DOMDocument();
$dom2->loadXML($xml);
$xliff2 = XliffDocument::fromDOM($dom2);

//var_dump($xliff2);
echo $xliff2->toDOM()->saveXML();


