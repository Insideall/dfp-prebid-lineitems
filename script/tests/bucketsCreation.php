<?php

require __DIR__.'/../scriptLoader.php';

use App\Scripts\Buckets;

$buckets = Buckets::createBuckets(["buckets" =>[
		['precision' => 2, 'min' => 0, 'max' => 4.49, 'increment' => 0.01]
	]
]);

$buckets = Buckets::createBuckets('dense');

echo json_encode($buckets, JSON_PRETTY_PRINT);

echo count($buckets);
/*
['precision' => 2, 'min' => 0, 'max' => 4.49, 'increment' => 0.01]
['precision' => 2, 'min' => 4.50, 'max' => 8.99, 'increment' => 0.01]
['precision' => 2, 'min' => 9.00, 'max' => 13.49, 'increment' => 0.01]
['precision' => 2, 'min' => 13.50, 'max' => 17.99, 'increment' => 0.01]
['precision' => 2, 'min' => 18.00, 'max' => 20.00, 'increment' => 0.01]

