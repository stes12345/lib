<?PHP
class ArrayExt {
	public static function exists($array, $key) {
		return is_array($array) && array_key_exists($key, $array);
	}
	public static function get($array, $key, $def = null) {
		if (is_object($array)) {
			if (self::exists(array_keys(get_object_vars($array)),$key))
				return $array->$key;
			else
				return $array->$key;
		} else {
			if (!self::exists($array,$key)) {
				$array[$key]=$def;
			}
			return $array[$key];
		}
	}
	public static function set($array, $key, $value) {
		if (is_object($array) && $key)
			$array->$key=$value;
		else 
		$array[$key] = $value;
	}
	public static function existsValue($array, $key, $value) {
		return self::exists($array,$key) && self::get($array,$key) == $value;
	}
	public static function getBool($array, $key, $def = false) {
		$val = self::get($array,$key, $def);
		return strtolower($val) == 'true' || strtolower($val) == 'yes' || strtolower($val) == 'on' || intval($val);
	}
	public static function getInt($array, $key, $def = 0) {
		return intval(self::get($array,$key, $def));
	}
	public static function getFloat($array, $key, $def = 0) {
		return floatval(self::get($array,$key, $def));
	}
	public static function delete($array, $key) {
		if (self::exists($array,$key))
			unset($array[$key]);
	}
}
?>