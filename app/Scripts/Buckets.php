<?php

namespace App\Scripts;

class Buckets
{
	public static function createBuckets($value)
	{
		if(in_array($value, ['low', 'med', 'high', 'auto','dense', 'test']))
		{
			$function = "create".ucfirst($value)."Buckets";
			return self::$function();
		}
		else
		{
			echo "Error: You need to choose an value in 'low', 'med', 'high', 'auto','dense'!!!\n";
			exit;
		}
	}

	private static function createDenseBuckets()
	{
		$buckets = [];
		for($i = 0;$i <= 3; $i = $i + 0.01)
		{
			array_push($buckets, sprintf('%0.2f', $i));
		}
		for($i = 3.05;$i <= 8; $i = $i + 0.05)
		{
			array_push($buckets, sprintf('%0.2f', $i));
		}
		for($i = 8.5;$i <= 20; $i = $i + 0.5)
		{
			array_push($buckets, sprintf('%0.2f', $i));
		}
		return $buckets;
	}

	private static function createAutoBuckets()
	{
		$buckets = [];
		for($i = 0;$i <= 5; $i = $i + 0.05)
		{
			array_push($buckets, sprintf('%0.2f', $i));
		}
		for($i = 5.1 ;$i <= 10; $i = $i + 0.1)
		{
			array_push($buckets, sprintf('%0.2f', $i));
		}
		for($i = 10.5;$i <= 20; $i = $i + 0.5)
		{
			array_push($buckets, sprintf('%0.2f', $i));
		}
		return $buckets;
	}

	private static function createLowBuckets()
	{
		$buckets = [];
		for($i = 0;$i <= 5; $i = $i + 0.5)
		{
			array_push($buckets, sprintf('%0.2f', $i));
		}
		return $buckets;
	}

	private static function createMedBuckets()
	{
		$buckets = [];
		for($i = 0;$i <= 20; $i = $i + 0.1)
		{
			array_push($buckets, sprintf('%0.2f', $i));
		}
		return $buckets;
	}

	private static function createHighBuckets()
	{
		$buckets = [];
		for($i = 0;$i <= 20; $i = $i + 0.01)
		{
			array_push($buckets, sprintf('%0.2f', $i));
		}
		return $buckets;
	}

	private static function createTestBuckets()
	{
		$buckets = [];
		for($i = 0;$i <= 20; $i = $i + 2.5)
		{
			array_push($buckets, sprintf('%0.2f', $i));
		}
		return $buckets;
	}

}