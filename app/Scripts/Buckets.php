<?php

namespace App\Scripts;

class Buckets
{
	public static function createBuckets($value)
	{
		$predefinedGranularityBuckets = [
			'low' => [
				['precision' => 2, 'min' => 0, 'max' => 5, 'increment' => 0.5]
			],
			'med' => [
				['precision' => 2, 'min' => 0, 'max' => 20, 'increment' => 0.1]
			],
			'high' => [
				['precision' => 2, 'min' => 0, 'max' => 20, 'increment' => 0.01]
			],
			'auto' => [
				['precision' => 2, 'min' => 0, 'max' => 5, 'increment' => 0.05],
				['precision' => 2, 'min' => 5, 'max' => 10, 'increment' => 0.1],
				['precision' => 2, 'min' => 10, 'max' => 20, 'increment' => 0.5],
			],
			'dense' => [
				['precision' => 2, 'min' => 0, 'max' => 3, 'increment' => 0.01],
				['precision' => 2, 'min' => 3, 'max' => 8, 'increment' => 0.05],
				['precision' => 2, 'min' => 8, 'max' => 20, 'increment' => 0.5],
			],
			'test' => [
				['precision' => 2, 'min' => 0, 'max' => 20, 'increment' => 2.5]
			]
		];
		
		if (is_string($value))
		{
			if (isset($predefinedGranularityBuckets[$value])) 
			{
				$value = $predefinedGranularityBuckets[$value];
			}
			else
			{
				echo "Error: You need to choose an value in 'low', 'med', 'high', 'auto','dense'!!!\n";
				exit;
			}
		} else {
			$value = $value["buckets"];
		}
		
		if (!isset($value[0]['increment'])) {
			echo "Error: custom granularity should specify an array of buckets\n";
			exit;
		}
		
		return self::create($value);
	}
	
	private static function create($priceGranularity)
	{
		$buckets = [];
		$lastValue = 0;
		foreach ($priceGranularity as $value)
		{
			$precision = isset($value['precision']) ? $value['precision'] : 2;
			// Floating point comparison is not reliable
			// https://stackoverflow.com/questions/3148937/compare-floats-in-php
			for($i = $value['min']; bccomp($i, $value['max'], $precision) <= 0; $i += $value['increment'])
			{
				if ($i > 0 && bccomp($i, $lastValue, 2) !== 0)
				{
					array_push($buckets, sprintf("%0.{$precision}f", $i));
				}
				$lastValue = $i;
			}
		}
		
		return $buckets;
	}
}