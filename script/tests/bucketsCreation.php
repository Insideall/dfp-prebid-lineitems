<?php

require (__DIR__."/../scriptLoader.php");

use App\Scripts\Buckets;

$buckets = Buckets::createBuckets("dense");

echo json_encode($buckets, JSON_PRETTY_PRINT);

echo count($buckets);