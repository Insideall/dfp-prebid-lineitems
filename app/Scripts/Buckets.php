<?php

namespace App\Scripts;

class Buckets
{
	public static function createBuckets($value)
	{
		$predefinedGranularityBuckets = [
			'low' => [
				[0, 5, 0.5]
			],
			'med' => [
				[0, 20, 0.1]
			],
			'high' => [
				[0, 20, 0.01]
			],
			'auto' => [
				[0, 5, 0.05],
				[5, 10, 0.1],
				[10, 20, 0.5],
			],
			'dense' => [
				[0, 3, 0.01],
				[3, 8, 0.05],
				[8, 20, 0.5],
			],
			'test' => [
				[0, 20, 2.5]
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
		}
		
		return self::create($value);
	}
	
	private static function create($priceGranularity)
	{
		$buckets = [];
		$lastValue = 0;
		foreach ($priceGranularity as $value)
		{
			// Floating point comparison is not reliable
			// https://stackoverflow.com/questions/3148937/compare-floats-in-php
			for($i = $value[0]; bccomp($i, $value[1], 2) <= 0; $i += $value[2])
			{
				if ($i > 0 && bccomp($i, $lastValue, 2) !== 0)
				{
					array_push($buckets, sprintf('%0.2f', $i));
				}
				$lastValue = $i;
			}
		}
		
		return $buckets;
	}
}