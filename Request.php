<?php
defined( '_CONTROL' ) or die( '' );
class Request {
	public static function exists($key) {
		return array_key_exists($key, $_REQUEST);
	}
	public static function get($key, $def = null) {
		if (self::exists($key) && $_REQUEST[$key])
			return $_REQUEST[$key];
		elseif ($def)
		return $def;
	}
	public static function set($key, $value) {
		$_REQUEST[$key] = $value;
	}
	public static function update($key, $def) {
		if (self::exists($key) && $_REQUEST[$key]) return $_REQUEST[$key];
		return $_REQUEST[$key] = $def;
	}
	public static function getAction($def = null) {
		return self::get('action', $def);
	}
	public static function existsValue($key, $value) {
		return self::exists($key) ? self::get($key) == $value : false;
	}
	public static function getBool($key, $def = false) {
		$val = self::get($key, $def);
		return strtolower($val) == 'true' || strtolower($val) == 'yes' || strtolower($val) == 'on' || intval($val);
	}
	public static function getInt($key, $def = 0) {
		return intval(self::get($key, $def));
	}
	public static function getFloat($key, $def = 0) {
		return floatval(self::get($key, $def));
	}
	public static function delete($key) {
		if (self::exists($key)) {
			unset($_REQUEST[$key]);
		}
	}
	public static function loadFrom($obj, $fields = null) {
		if (!is_array($fields) || count($fields) < 1) $fields = array_keys(get_class_vars(get_class($obj)));
		foreach ($fields as $name) {
			if (!self::exists($name) && !is_array($obj->$name) && !is_object($obj->$name)) {
				self::set($name, stripslashes(str_replace('\\\'', '\'', $obj->$name)));
			}
		}
	}

	public static function saveTo(&$obj, $requiredFields = null, $skippedFields = null) {
		$fields = get_class_vars(get_class($obj));
		foreach ($fields as $name=>$value) {
			if (!$skippedFields || !in_array($name, $skippedFields, true)) {
				if (self::exists($name)) {
					$obj->$name = stripslashes(str_replace('\\\'', '\'', self::get($name)));
					if (substr($name, 0, 2) == 'is') {
						$obj->$name = empty($obj->$name) ? 0 : 1;
					}
				} elseif (self::exists("{$name}_day")) {
					if (self::exists("{$name}_minute")) {
						$timeStampS = date('YmdHis', mktime(self::get($name.'_hour'), self::get($name.'_minute'), 0,
							self::get($name.'_month'), self::get($name.'_day'), self::get($name.'_year')));
					} else {
						$timeStampS = date('Ymd', mktime(0,0,0,
							self::get($name.'_month'), self::get($name.'_day'), self::get($name.'_year')));
					}
					$obj->$name = $timeStampS;
				} elseif ($requiredFields && in_array($name, $requiredFields, true)) {
					$obj->$name = null;
				}
			}
		}
	}
	static function parseRequest($fields){
		if (isset($_REQUEST) && $fields){
			$fieldArr=explode(',',$fields);
			foreach ($fieldArr as $field)
				$GLOBALS[$field]=Request::get($field);
		}
	}
}
?>