<?php
/**
 * Example of how to run this particular script: php test.php 100 200 https://www.emag.ro/televizoare/c
 */

require 'simple_html_dom.php';
require 'EmagCrawler.php';

$emagCrawler = new EmagCrawler();

$minLimit = $argv[1];
$maxLimit = $argv[2];
$link = $argv[3];

$maxPageNumber = $emagCrawler->getPages($link);
$priceList = $emagCrawler -> getPrices($link, $maxPageNumber);
$linkList = $emagCrawler -> getLinks($link, $maxPageNumber);

$emagCrawler -> findPricesLowerThan($minLimit,$maxLimit,$priceList, $linkList, 'test.txt');

