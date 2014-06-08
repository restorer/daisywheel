<?php

namespace daisywheel\core;

class ArrayHelper
{
	public static function merge()
	{
		$parts = func_get_args();
		$result = array_shift($parts);

		while (count($parts)) {
			$part = array_shift($parts);

			foreach ($part as $k => $v) {
				if (is_array($v) && array_key_exists($k, $result) && is_array($result[$k])) {
					$result[$k] = self::merge($result[$k], $v);
				} elseif (is_integer($k) && !array_key_exists($k, $result)) {
					$result[] = $v;
				} else {
					$result[$k] = $v;
				}
			}
		}

		return $result;
	}
}
