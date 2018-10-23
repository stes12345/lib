<?php
class Response {
	public $responseArray=array();
	static function action($function,$params=null) {
		if ($params && !is_array($params)){$params=array($params);}
		//if ($params==null) {$params=array();}
		try {
			// if($params) Str::log1($function,$params);
			$result=call_user_func_array($function,$params);
			if (is_array($result))
				Response::success($result);
			elseif ($result)
				Response::success(array('html'=>$result));
			Response::error(_('System Eror'));
		} catch (Exception $e) {
			Response::error($e);
		}
	}
	static function getClock() {
		return date('d.m.Y H:i:s');
	}
	function send($response=null) {
		$this->responseArray['clock']=self::getClock();;
		if ($response && is_array($response)) {
			if (count($response)) {
				$this->responseArray+=$response;
			} else {Str::log1($response);}
		}
		self::json($this->responseArray);
	}
	static function success($okMess=null,$html=null) {
		$response=new Response();
		$response->setSuccessed();
		if (is_array($okMess)) {
			$response->set($okMess);
		} elseif ($okMess) {
			$response->set(array('html'=>$okMess));
		}
		if ($html)
			$response->set('html',$html);
		$response->send();
	}
	static function expired($message=null) {
		$response=new Response();
		if ($message) {$response->setError($message);}
		$response->setExpired();
		$response->send();
	}
	function setExpired(){
		$this->responseArray['expired']=1;
	}
	function setSuccessed($okMess=null){
		$this->responseArray['success']=1;
		if ($okMess) {$this->setOk($okMess);}
	}
	function set($name,$value=null){
		if (is_array($name)) { // if $name is array of name-value pairs
			foreach($name as $n=>$v) {
				if ($n=='okMess') {
					$this->setOk($v);
				} else {
					$this->set($n,$v);
				}
			}
		} else {
			if (!array_key_exists($name,$this->responseArray)) {
			$this->responseArray[$name]=$value;
			} else {
				$this->responseArray[$name].=$value;
			}
		}
	}
	function setMessage($message){
		$this->set('message',$message);
	}
	function setOk($message){
		$this->setMessage(self::prepareOk($message));
	}
	function setError($message){
		$this->setMessage(self::prepareError($message));
	}
	static function error($message,$response=null) {
		if ($message instanceof Exception) {
			if ($message->getCode()==999)
				self::expired($message->getMessage());
			$message=$message->getMessage();
		}
		self::message(self::prepareError($message),$response);
	}
	static function message($message,$response=null) {
		$response=new Response();
		$response->setMessage($message);
		$response->send($response);
	}
	static function json($var = array()) {
		if ($ob = ob_get_clean()) {/*Str::log1($ob);*/}
		header('Content-type: text/html; charset: UTF-8');
		if (is_array($var)) {
			echo self::php2js($var); //echo json_encode($var);
		} else { Str::log1($var); }
		exit(0);
	}
	static function prepareOk($message) {
		return "<span class=\"okay\">$message</span>";
	}
	static function prepareError($message) {
		return "<span class=\"err\">$message</span>";
	}
	static function php2js($a=false) {
		if (is_null($a)) return 'null';
		if ($a === false) return 'false';
		if ($a === true) return 'true';
		if (is_scalar($a)) {
			if (is_float($a))   {
				$a = str_replace(",", ".", strval($a));
			}
			static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
			array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
			return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
		}
		$isList = true;
		for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
			if (key($a) !== $i) {
				$isList = false;
				break;
			}
		}
		$result = array();
		if ($isList) {
			foreach ($a as $v) $result[] = self::php2js($v);
			return '[ ' . join(', ', $result) . ' ]';
		} else {
			foreach ($a as $k => $v) $result[] = self::php2js($k).': '.self::php2js($v);
			return '{ ' . join(', ', $result) . ' }';
		}
	}
}
?>