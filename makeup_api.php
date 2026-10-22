<?php
header('content-Type: text/html; charset=utf-8');
$apiUrl = "http://makeup-api.herokuapp.com/api/v1/products.json?product_type=lipstick";

$jsonData = @file_get_contents($apiUrl);

if($jsonData === false)
{
    die("<p>Could not retrieve data for Makeup API </P>");
}

$products = json_decode($jsonData, true);

if(!$products)
{
    die("<p>No products found.</p>");
}

// https://publicapi.dev/makeup-api?utm_source=chatgpt.com