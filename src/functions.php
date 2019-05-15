<?php

error_reporting(-1);
ini_set('display_errors', 'On');

function getQuery($searchFFilter = '', $orderFFilter = '', $tagsFilter = '')
{

    $pattern = "
        SELECT  book.book_name, book.price,book.ISBN, book.url, book.poster ,
               GROUP_CONCAT(tag.tag_name) AS tags
        FROM book
            LEFT JOIN book_tag
                ON book.book_id = book_tag.book_id
            LEFT JOIN tag
                ON book_tag.tag_id = tag.tag_id
                {$searchFFilter}   {$tagsFilter}
        GROUP BY book.book_name , book.price,book.ISBN , book.url , book.poster
       {$orderFFilter}
        ";

    return $pattern;
}

function isInRequest($name){
    if(isset($_GET[$name])&& $_GET[$name]!="NAN"){
        return true;

    }
    return false;
}

function getBooks($dbCon, $paramsMap)
{
    $searchFFilter = '';
    $orderFFilter = "";
    $tagsFilterArr = [];
    $tagsFilter = "";
    $result = NAN;
    $arr = [];

    if (isset($paramsMap['tags'])) {

        foreach ($paramsMap['tags'] as $index => $tag) {
            $tagsFilterArr [] = " tag.tag_name = :tag{$index} ";
            $arr[":tag{$index}"] = $tag;
        }
        $tagsFilter = implode("OR", $tagsFilterArr);

    }
    if (isset($paramsMap['searchBY'])) {
        $arr[':searchBY'] = "%{$paramsMap['searchBY']}%";
        $searchFFilter = " WHERE  book.book_name like :searchBY and ";
    }
    if (isset($paramsMap['order'])) {

        $orderFFilter = "ORDER BY  {$paramsMap['order']}";
    }

    if (isset($paramsMap['tags'])) {
        if (isset($paramsMap['tags']) && count($paramsMap['tags']) > 1) {
            $tagsFilter = '(' . $tagsFilter . ')';

        }
        $query = getQuery($searchFFilter, $orderFFilter, $tagsFilter);


        $result = $dbCon->prepare($query);
        foreach ($arr as $key => $value) {
            $result->bindValue($key, $value, PDO::PARAM_STR);
        }
        $result->execute();
        return $result;

    } else {

        if (isset($paramsMap['searchBY'])) {
            $searchFFilter = str_replace('and', '', $searchFFilter);
        }

        $query = getQuery($searchFFilter, $orderFFilter, $tagsFilter);
        $result = $dbCon->prepare($query);
        foreach ($arr as $key => $value) {
            $result->bindValue($key, $value, PDO::PARAM_STR);
        }
        $result->execute();

        return $result;

    }
}


function checkOrderCookies($value)
{
    if (isset($_COOKIE["price_name"])) {
        if ($_COOKIE["price_name"] === $value) {
            return "checked = 'checked'";
        }
    }
}


function checkTagCookies($value)
{

    if (isset($_COOKIE["tags"])) {

        if (strpos($_COOKIE["tags"], $value) != false) {
            return "checked = 'checked'";
        }
    }
}


function getTagsArr(PDO $dbConn)
{
    $tagsQuery = $dbConn->query('SELECT tag.tag_name FROM tag');
    $tagsArr = $tagsQuery->fetchAll(PDO::FETCH_ASSOC);

    return $tagsArr;
}







