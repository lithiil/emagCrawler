<?php
/**
 * This class can be used to crawl items from https://emag.ro which match the price ranges indicated by you
 * You can create the links this way:
 * Go to the site and select a category, you get this link:
 * https://www.emag.ro/aparate-frigorifice/sd?ref=hp_menu_quick-nav_267_0&type=subdepartment
 * Now delete the part that comes after the last / , like so:
 * https://www.emag.ro/aparate-frigorifice/c
 * NOTE: This is just and example and is made for educative purposes, it is not meant to infringe any license
 */

class EmagCrawler

{

    /**
     * The link must be a clean category link (ex: https://www.emag.ro/televizoare/c)
     * @param string $link
     * @return array|mixed|string The returned variable is an INT which represents the maximum number of pages available in the category chosen
     */

    function getPages($link)
    {

        $html = new simple_html_dom();

        $context = stream_context_create(array(
            'http' => array(
                'header' => array('User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; rv:2.2) Gecko/20110201'),
            ),
        ));

        $html->load_file($link, false, $context);

        $pageMax = $html->find('.pagination .visible-xs');

        $maxPageNumber = $pageMax[count($pageMax) - 1]->plaintext . PHP_EOL;
        $maxPageNumber = str_replace('din ', '', $maxPageNumber);
        $maxPageNumber = explode(' ', $maxPageNumber);
        $maxPageNumber = max($maxPageNumber);

        echo $maxPageNumber . ' Pages Found' . PHP_EOL;

        return $maxPageNumber;
    }

    /**
     * The link must be a clean category link (ex: https://www.emag.ro/televizoare/c)
     * @param string $link
     * @param int $maxPageNumber
     * @return array returns an array of links crawled in the link provided by you
     */

    function getLinks($link, $maxPageNumber)
    {

        $html = new simple_html_dom();

        $context = stream_context_create(array(
            'http' => array(
                'header' => array('User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; rv:2.2) Gecko/20110201'),
            ),
        ));

        $linkList = [];


        for ($i = 0; $i <= $maxPageNumber; $i++) {

            if ($i == 1) {
                $tmplink = strstr($link, '/c', true);
                $tmplink = $tmplink . '/p' . $i . '/c';
            }

            if ($i > 1) {
                $tmplink = strstr($tmplink, '/p', true);
                $tmplink = $tmplink . '/p' . $i . '/c';
            }

//            echo 'Going through page ' . $i . PHP_EOL;

            if ($i == 0) {
                $html->load_file($link, false, $context);
            } else {
                $html->load_file($tmplink, false, $context);
            }
            $links = $html->find('.card  .card-section-wrapper .card-section-top .card-heading a');

            foreach ($links as $currentLink) {
                $currentLink = $currentLink->href;
                array_push($linkList, $currentLink);

            }
        }

        echo 'Found ' . count($linkList) . ' links' . PHP_EOL;
        return $linkList;
    }

    /**
     * The link must be a clean category link (ex: https://www.emag.ro/televizoare/c)
     * @param string $link
     * @param int $maxPageNumber
     * @return array returns a list of prices crawled from the link provided
     */

    function getPrices($link, $maxPageNumber)
    {

        $priceList = [];

        $html = new simple_html_dom();

        $context = stream_context_create(array(
            'http' => array(
                'header' => array('User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; rv:2.2) Gecko/20110201'),
            ),
        ));

        for ($i = 0; $i <= $maxPageNumber; $i++) {

            if ($i == 1) {
                $tmplink = strstr($link, '/c', true);
                $tmplink = $tmplink . '/p' . $i . '/c';
            }

            if ($i > 1) {
                $tmplink = strstr($tmplink, '/p', true);
                $tmplink = $tmplink . '/p' . $i . '/c';
            }

            if ($i == 0) {
                $html->load_file($link, false, $context);
            } else {
                $html->load_file($tmplink, false, $context);
            }

            $prices = $html->find('div .page-container .card .product-new-price');

            foreach ($prices as $price) {
                $price = str_replace('&#46;', '', $price->plaintext);
                $price = str_replace(' Lei', '', $price);
                $price = substr($price, 0, -3);
                array_push($priceList, $price);

            }
//            echo 'Found ' . count($priceList) . ' products on page ' .$i. PHP_EOL;
        }

        echo 'Found a total of ' . count($priceList) . ' prices' . PHP_EOL;
        return $priceList;

    }

    /**
     *This function compares the prices with a minimum and maximum limit provided by you and returns viable results
     * @param int $minLimit is the lowest limit
     * @param int $maxLimit is the maximum limit
     * @param array $priceList
     * @param array $linkList
     * @param string $file this parameter is optional, if you set it the results will go in a .txt .
     * Be carefull tho' because the files will get re-written each time this runs because of the 'w' option.
     */

    function findPricesBetween($minLimit, $maxLimit, $priceList, $linkList, $file = null)
    {
        $counter = 0;
        $viableResults = 0;
        if (isset($file)) {
            $handle = fopen($file, 'w');
        }
        foreach ($priceList as $price) {

            if ($price >= $minLimit && $price <= $maxLimit) {

                echo 'Item with link ' . $linkList[$counter] . ' has price ' . $price . PHP_EOL;
                $viableResults++;

                if (isset($file)) {
                    fwrite($handle, 'Item with link ' . $linkList[$counter] . ' has price ' . $price . PHP_EOL);
                } else {
                    continue;
                }
            }

            $counter++;
        }

        echo 'Found ' . $viableResults . ' viable results!' . PHP_EOL;

        if (isset($file)) {
            fclose($handle);
        }
    }
/**
 * This will return ALL of the viable category links that emag has.
 * It can be used in a loop to search for things found in ALL of the categories
 * @return Array $categoryLinks
 */
    function getCategories() 
    {
        $categoryLinks = [];
        $html = new simple_html_dom();

        $context = stream_context_create(array(
            'http' => array(
                'header' => array('User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; rv:2.2) Gecko/20110201'),
            ),
        ));

        $html->load_file('https://www.emag.ro/all-departments', false, $context);

        foreach ($html->find('#department-expanded ul li a') as $category) {
            if (strstr($category->href, '/c') == false) {
                continue;
            }
            $categoryLink = 'https://www.emag.ro' . strstr($category->href, '?', true);
            array_push($categoryLinks, $categoryLink);
        }
        return $categoryLinks;
    }
}