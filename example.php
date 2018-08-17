<?php
/**
 * Example of how to run this particular script: php test.php 100 200 https://www.emag.ro/televizoare/c
 */

require 'simple_html_dom.php';
require 'EmagCrawler.php';

$emagCrawler = new EmagCrawler();

$minLimit = 100;
$maxLimit = 200;
$link = 'https://www.emag.ro/laptopuri-accesorii/c';

$maxPageNumber = $emagCrawler->getPages($link);
$priceList = $emagCrawler -> getPrices($link, $maxPageNumber);
$linkList = $emagCrawler -> getLinks($link, $maxPageNumber);

$emagCrawler -> findPricesBetween($minLimit,$maxLimit,$priceList, $linkList, 'test.txt');

/**
 * Example of how to search for items in that price range in ALL of the categories
 */
// $categories = $emagCrawler->getCategories();

// foreach ($categories as $categoryLink) {
//     echo 'Checking Link ' . $categoryLink . PHP_EOL;
//     $maxPageNumber = $emagCrawler->getPages($categoryLink);
//     $priceList = $emagCrawler -> getPrices($categoryLink, $maxPageNumber);
//     $linkList = $emagCrawler -> getLinks($categoryLink, $maxPageNumber);
//     $emagCrawler -> findPricesBetween(100,200,$priceList, $linkList, 'test.txt');
// }

