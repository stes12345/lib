<?php
class Str {
	const LOGFILENAME='logfilename';
	const SITEPATH   ='pathSite';
	static private $params=array();
	static function executeShell($commandExec){
		$WshShell = new COM("WScript.Shell"); //$commandExec="\"".MYSQL_PATH."mysqlcheck.exe\" --repair --all-databases -uroot -padmin 2>&1>>"
		$command = "cmd /C $commandExec " . preg_replace('|/|', '\\\\', LOG_PATH) . 'service.log ';
		Str::log1($command);
		$WshShell->Run($command, 0, false);
	}
    static function serverTimestamp(){
        return "Cas.serverTimestamp='" . date('Y-m-d H:i:s') . "';";
    }
	public static function htmlEncode($string) {
		return htmlspecialchars($string, ENT_QUOTES, "utf-8");
	}

	static public function trimEncode($string) {
		return self::htmlEncode(trim($string));
	}

	public static function removeLastComma($string, $comma = ",", $offset = 1) {
		$last = substr($string, strlen($string)-1, strlen($string));
		if($last != $comma) return false;
		return substr($string, 0, strlen($string) - $offset);
	}

	public static function quote_smart($value) {
		if ($value=='NOW()' || $value==='NULL' || $value==='CURRENT_TIMESTAMP')
			return $value;
		if (get_magic_quotes_gpc()) {
			$value = stripslashes($value);
		}
		// Quote if not integer
		if (!is_numeric($value)) {
			$value = "'" . preg_replace("/'/","&#27;",$value) . "'";
		}
		return $value;
	}
	
	public static function quote_smart2($value) {
		if ($value=='NOW()' || $value==='NULL' || $value==='CURRENT_TIMESTAMP')
			return $value;
		if (get_magic_quotes_gpc()) {
			$value = stripslashes($value);
		}
		// Quote if not integer
		if (!is_numeric($value)) {
			$value = preg_replace("/'/","&#27;",$value);
		}
		return $value;
	}
	static function IsNullOrEmpty($question) {
		return !isset($question) || trim("$question") === '';
	}
	public static function log1($n,$s='xxxxxxx') {
		$sitePath = preg_replace('/\//','\\',ArrayExt::get($GLOBALS,self::SITEPATH));
        if (!$sitePath) {$sitePath=dirname(__FILE__);}
		$logfilename = ArrayExt::get($GLOBALS,self::LOGFILENAME);
		if (!$logfilename) {$logfilename='error.log';}
		$logfilename =LOG_PATH.$logfilename;
		$bt = '' ;
		$dbts = debug_backtrace();
		$len=strlen($sitePath);
		foreach ($dbts as $dbt) {
			if (array_key_exists('file', $dbt)){
				$bt = substr($dbt['file'],$len) . '(' . $dbt['line'] . '):';
				break ;
			}
		}
		if(!$bt) $bt = $_SERVER['SCRIPT_NAME'] . ': ';

		@error_log(date('Y.m.d H:i:s ') . $bt.var_export($n,true).($s!='xxxxxxx'?': '.var_export($s,true):'')."\n",3, $logfilename);
	}
	
	public static function logShort1($var, $level = 0) {
		// self::log1(json_encode($var));
		// return;
		if(gettype($var) == 'object') {
			$var = get_object_vars($var);
		}
		$result = null;
		// Str::log1('gettype($var) ' . gettype($var));
		$varType = gettype($var);
		switch($varType) {
			case "array":
				$aData = array();
				foreach($var as $name => $value) {
					$aData[$name] = self::logShort1($value, $level + 1);
				}
				$result = $aData;
			case "string":
				// $var = mb_convert_encoding($var,'UTF-8','UTF-8');
				//$var = htmlentities( (string) $var, ENT_QUOTES, 'utf-8', FALSE);
				if(is_array ($var)){
					$result = 'var is array';
				} else {
					$result = 'var is string';
				}
				// if(mb_detect_encoding($var, 'UTF-8', true)) {
					// Str::log1('mb_detect_encoding - yes ');
					// $result = var_export($var, true);
				// } else {
					// $result = var_export(substr(base64_encode($var), 0, 20), true);
				// }
			default:
				$result = var_export($var, true);
		}
		$result = "($varType)$result";
		if($level == 0) {
			self::log1($result);
		} else {
			return $result;
		}
	}
	public static function log2($n,$s=null) {
        $sitePath = preg_replace('/\//','\\',$GLOBALS['pathSite']);
		if (!$sitePath) {$sitePath=dirname(__FILE__);}
		$logfilename = ArrayExt::get($GLOBALS,self::LOGFILENAME, 'error.log');
        $logfilename =LOG_PATH.$logfilename;
		@error_log(date('Y.m.d H:i:s ') . var_export(self::logShort1($n),true).($s?': '.var_export(self::logShort1($s),true):'').Str::__backtrace(0) . "\n",3, $logfilename);
	}
	static function __backtrace($skip = 1) {
		$skip++;
		$bt = '';
		$dbts = debug_backtrace();
		foreach ($dbts as $dbt) {
			if ($skip-- > 0) continue;
			$bt .= "\n\t";
			if (array_key_exists('class', $dbt)) $bt .= $dbt['class'] . (array_key_exists('type', $dbt) ? $dbt['type'] : '.');
			$bt .= $dbt['function'];
			if (array_key_exists('args', $dbt) && $dbt['args']) {
				$bt .= ' (' ;
				$vars='';
				foreach ($dbt['args'] as $arg) {
					if ($vars) $vars .= ',';
						if (is_array   ($arg)) $vars .= 'array('.count($arg). ')';
					elseif (is_object  ($arg)) $vars .= 'oject:'.get_class($arg);
					elseif (is_resource($arg)) $vars .= 'resource:'.get_resource_type($arg);
					elseif (is_scalar  ($arg)) {
						if (is_string($arg)) 
							$vars .= "'".Str::short($arg,10)."'";
						else
							$vars .= $arg;
					} elseif ($arg===null) $vars .= gettype($arg);
					  else $vars .= gettype($arg).'(?)';
				}
				$bt .= $vars.')' ;
			}
			if (array_key_exists('file', $dbt)) $bt .= ' (' . $dbt['file'] . ':' . $dbt['line'] . ')';
		}
		return $bt;
	}
	static function tracFile($name) {
	    $pathSite=ArrayExt::get($GLOBALS,'pathSite');
	    error_log(date('Y.m.d H:i:s ') . "\n",3, LOG_PATH.$name);
	}
	static function short($str, $maxlen = 42) {
		if (!$str) {return;}
		$str1=html_entity_decode($str);
		if (Str::utf8_strlen($str1) > $maxlen) {
			return htmlentities(Str::utf8_substr($str1, 0, $maxlen - 1)) . '&#8230;'; // %u2026 &#x2026; &#8230;
		} else {
			return htmlentities($str);
		}
	}
	static function short1($str, $maxlen = 42) {
		if (!$str) {return;}
		$str1=($str); //html_entity_decode
		if (mb_strlen($str1) > $maxlen) {
			return (mb_substr($str1, 0, $maxlen - 1)) . '&#8230;'; // %u2026 &#x2026; &#8230;
		} else {
			return ($str);
		}
	}
	static function utf8_strlen($str) {
		$ar=null;
		preg_match_all('/./su', $str, $ar);
		return count($ar);
	}
	static function utf8_substr($str,$start) {
        $ar=$end=null;
		preg_match_all('/./su', $str, $ar);

		if(func_num_args() >= 3) {
			$end = func_get_arg(2);
			return join("",array_slice($ar[0],$start,$end));
		} else {
			return join("",array_slice($ar[0],$start));
		}
	}
	static function anyWordOrLikeClause ($field,$words) {
		if (!$field || !$words )
			return ;
		$wordsTmp  = $words;
		$sql = '';
		$aWords  = explode(' ',preg_replace('/[,\.:;\/]+/',' ',$wordsTmp));
		foreach ($aWords as $word) {
			if (strlen($word) > 0) {
				if ($sql) 
					$sql .= ' OR ';
				$sql .= "$field LIKE '%$word%'" ;
			}
		}
		if ($sql)
			return " AND ($sql)";
	}
	static function isDate($sDateTime) {
		if (!$sDateTime){return false;}
		$matches=null;
		if (	preg_match('/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2}):(\d{2})$/',$sDateTime) ||
				preg_match('/^(\d{4})-(\d{2})-(\d{2})$/',$sDateTime) ||
				preg_match('/^(\d{1,2})[.,\/](\d{1,2})[.,\/](\d{1,4})\s+(\d{1,2}):(\d{1,2}):(\d{1,2})$/',$sDateTime) ||
				preg_match('/^(\d{1,2})[.,\/](\d{1,2})$/',$sDateTime) ||
				preg_match('/^(\d{1,2})[.,\/](\d{4})$/',$sDateTime) ||
				preg_match('/^(\d{1,2})[.,\/](\d{1,2})[.,\/](\d{1,4})$/',$sDateTime) ||
				preg_match('/^[yYгГ]:(\d{4})$/u',$sDateTime)
				) {
			//log1('true,'.gettype($sDateTime).','.$sDateTime);
			return true;
		} elseif(preg_match('/^(\d{2})(\d{2})(\d{2})$/',$sDateTime,$matches)) {
			if ($matches[1]>31 && $matches[3]<=31 && $matches[2]<=12 ||
				$matches[3]>31 && $matches[1]<=31 && $matches[2]<=12){
				return true;
			} else {
				//Str::log1('false,'.gettype($sDateTime).','.$sDateTime);
				return false;
			}
		} else {
			//Str::log1('false,'.gettype($sDateTime).','.$sDateTime);
			return false;
		}
	}
	static function str2utime($sDateTime) {
		//Str::log1('gettype($sDateTime):'.gettype($sDateTime));
		if ($sDateTime) {
			$matches=null;
			if (preg_match('/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2}):(\d{2})$/',$sDateTime,$matches)) {
				//log1('$matches:'.var_export($matches,true));
				return mktime($matches[4],$matches[5],$matches[6],$matches[2],$matches[3],$matches[1]);
			} elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/',$sDateTime,$matches)) {
				return mktime(0,0,0,$matches[2],$matches[3],$matches[1]);
			} elseif (preg_match('/^(\d{1,2})[.,\/](\d{1,2})[.,\/](\d{1,4})\s+(\d{1,2}):(\d{1,2}):(\d{1,2})$/',$sDateTime,$matches)) {
				if ($matches[1]>31 && $matches[3]<=31 && $matches[2]<=12) {
					if ($matches[1]<10)
						$matches[1]+=2000;
					elseif($matches[1]<100)
						$matches[1]+=1900;
					return mktime(0,0,0,$matches[2],$matches[3],$matches[1]);
				} elseif ($matches[1]<=31 && $matches[3]>31 && $matches[2]<=12) {
					if ($matches[3]<10)
						$matches[3]+=2000;
					elseif($matches[3]<100)
						$matches[3]+=1900;
					//log1('$matches',$matches);
					return mktime($matches[4],$matches[5],$matches[6],$matches[2],$matches[1],$matches[3]);
				}
			} elseif (preg_match('/^(\d{1,2})[.,\/](\d{1,2})[.,\/](\d{1,4})$/',$sDateTime,$matches) || preg_match('/^(\d{2})(\d{2})(\d{2})$/',$sDateTime,$matches)) {
				if ($matches[1]>31 && $matches[3]<=31 && $matches[2]<=12){
					if ($matches[1]<10)
						$matches[1]+=2000;
					elseif($matches[1]<100)
						$matches[1]+=1900;
					return mktime(0,0,0,$matches[2],$matches[3],$matches[1]);
				} elseif ($matches[1]<=31 && $matches[3]>31 && $matches[2]<=12){
					if ($matches[3]<10)
						$matches[3]+=2000;
					elseif($matches[3]<100)
						$matches[3]+=1900;
					//log1('$matches',$matches);
					return mktime(0,0,0,$matches[2],$matches[1],$matches[3]);
				} else {
					if ($matches[3]<10)
						$matches[3]+=2000;
					elseif($matches[3]<100)
						$matches[3]+=1900;
					//log1('$matches',$matches);
					return mktime(0,0,0,$matches[2],$matches[1],$matches[3]);
				}
			}
		}
	}
	static function dateSmart($date) {
		$date=Str::str2utime($date);
		$dateNow=mktime();
		if (date('Y',$date)!=date('Y',$dateNow))
			return date('d.m.Y',$date);
		elseif (date('m',$date)!=date('m',$dateNow))
			return date('d.m',$date);
		elseif (date('d',$date)!=date('d',$dateNow))
			return date('d.m H:i',$date);
		else
			return date('H:i',$date);
	}
	static function date($format,$date=null) {
		if (!$date)
		  $date=date('Y-m-d H:i:s');
		return date($format,Str::str2utime($date));
	}
	static function truncate($string, $wordCount = 10,$strLen = 200) {
		$string = substr($string, 0, $strLen);
		$words = explode(" ", $string);
		if(count($words) > $wordCount) { 
		array_pop($words); //Make sure there are no cutted through last word
		$words = array_slice($words, 0, $wordCount);
		return implode(" ", $words)." ...";
		} else
			return $string;
	}
	static function getHtmlSelect(&$valuesArr, $name, $selected=null, $addAll=false) {
		$html="<SELECT name=\"$name\">";
		if ($addAll) //TODO make $addAll customizable, e.g. $addAll == "Choose" or $addAll == false
			$html.='<option value="">'._('All').'</option>';
		foreach ($valuesArr as $key=>$name) {	
			$html.="<option value=\"$key\"";
			if ($selected==$key) {$html.=' SELECTED ';}
			$html.='>'.$name.'</option>';
		}
		return $html.'</SELECT>';
	}	
	static function getParam($name) {
		if (!self::$params) {
			$handle = @fopen(dirname(__FILE__)."/../params.ini", "r");
			if ($handle) {
				$matches=null;
				while (!feof($handle)) {
					$buffer = fgets($handle, 300);
					if (preg_match('/^([A-Za-z_]+)\=([^\r\n]+)/',$buffer,$matches)) {
						self::$params[$matches[1]]=$matches[2];
					}
			   }
			   fclose($handle);
			}
		   //Str::log1(self::$params);
		}
		if ($name && array_key_exists($name,self::$params)){
			return self::$params[$name];
		}
	}
	static function toArray($obj) {
		$values=array();
		//$name='';
		if (is_array($obj)) {
			//$name='array';
			$keys=array_keys($obj);
		} elseif (is_object($obj)) {
			//$name=get_class($obj);
			$keys=array_keys(get_object_vars($obj));
		} else {return $obj;}
		foreach ($keys as $key) {
			if (is_array($obj)) {
				$values[$key]=self::toArray($obj[$key]);
			} else {
				$values[$key]=self::toArray($obj->$key);
			}
		}
		return $values;
	}
	public static function sec2time($sec) {
		$h = intval( $sec / (60*60) );
		$m = intval( ($sec % (60*60)) / 60);
		$s = intval( $sec % 60 );

		return sprintf("%02d:%02d:%02d", $h, $m, $s);
	}
	static function durationHuman($n) { // date(format,$n) show timezone
	    $sec=$n % 60;
	    $min=intval($n/60) % 60 ;
	    $hwr=intval($n/(60*60));
	    return sprintf("%02d:%02d:%02d", $hwr, $min, $sec);
	}
	static function durationHumanU($n) { // date(format,$n) show timezone
	    $usec=($n-intval($n))*1000;
	    $sec=$n % 60;
	    $min=intval($n/60) % 60 ;
	    return sprintf(" %02d:%02d.%03d",$min,$sec,$usec);
	}
	static function durationHumanU1($n) { // date(format,$n) show timezone
	    return ' '.(intval($n*1000)/1000).' s';
	}
    static function pingTest($host,$count=10) {
        for($i=0;$i<$count;$i++) {
            if (self::ping($host)!==false) {
                return $count;
            }
        }
        return false;
    }
	static function pingAtLeast($host,$count=10,$minOk=1) {
        $ok=0;
        for($i=0;$i<$count;$i++)
            if (self::ping($host)!==false) {
                $ok++;
                if ($ok>=$minOk)
                    return $ok;
            }
        return false;
    }
	static function ping($host) {
		$package = "\x08\x00\x19\x2f\x00\x00\x00\x00\x70\x69\x6e\x67";
		/* create the socket, the last '1' denotes ICMP */ 
		$socket = socket_create(AF_INET, SOCK_RAW, 1);
		/* set socket receive timeout to 1 second */
		socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 1, "usec" => 0));
		/* connect to socket */
		socket_connect($socket, $host, null);
		/* record start time */
		list($start_usec, $start_sec) = explode(" ", microtime());
		$start_time = ((float) $start_usec + (float) $start_sec);
		socket_send($socket, $package, strlen($package), 0);
		if(@socket_read($socket, 255)) {
			list($end_usec, $end_sec) = explode(" ", microtime());
			$end_time = ((float) $end_usec + (float) $end_sec);
			$total_time = $end_time - $start_time;
		    return $total_time;
		} else {
		    return false;
		}
		socket_close($socket);
	}
    static function restartService($serviceName,$logfilename,$maxtime=1800) {
    	$pathSite=ArrayExt::get($GLOBALS,'pathSite');
	    $file=LOG_PATH.$logfilename;
        $fileTime=null;
        clearstatcache();
        if (file_exists($file)) {
            $fileTime=filemtime($file);
	    }
	    $state=win32_query_service_status($serviceName);
	    if ($state['CurrentState']!=WIN32_SERVICE_RUNNING || $fileTime==null || $fileTime && (time()-$fileTime)>$maxtime) {
            Str::log1("----------- service $serviceName check, try to start/restart service at ".date('Y.m.d H:i:s',time())." fileTime:$fileTime time():".time().' time()-fileTime:'.(time()-$fileTime));
	        if ($state['CurrentState']!=WIN32_SERVICE_RUNNING && $state['CurrentState']!=WIN32_SERVICE_STOPPED) {
	            Str::log1(system("Process.exe -k php.exe",$retval),' retval:'.$retval);
	        }
	        if ($state['CurrentState']==WIN32_SERVICE_RUNNING) {
	            Str::log1(system("net stop $serviceName",$retval),' retval:'.$retval);
	        }
	        $state=win32_query_service_status($serviceName);
	        $i=0;
	        for($i=0;$i<180 && $state['CurrentState']!=WIN32_SERVICE_STOPPED;$i++) {
	            sleep(1);
	            $state=win32_query_service_status($serviceName);
	        }
	        if ($state['CurrentState']!=WIN32_SERVICE_STOPPED) {
	            Str::log1(system(LOG_PATH."Process.exe -k php.exe",$retval),' state:'.$state['CurrentState'].' retval:'.$retval);
	            Str::log1($state=win32_query_service_status($serviceName));
	        }
	        Str::log1(system("net start $serviceName",$retval),' retval:'.$retval);
	        $state=win32_query_service_status($serviceName);
	        Str::log1('last CurrentState:'.$state['CurrentState']);
	    } else {
            //Str::log1("----------- service $serviceName check ok at ".date('Y.m.d H:i:s',time()));
	    }
/*
C:\WINDOWS.0\system32>net stop remoteimages
The service could not be controlled in its present state.

More help is available by typing NET HELPMSG 2189.
SERVICE_STOPPED 0x1
SERVICE_START_PENDING 0x2
SERVICE_STOP_PENDING 0x3
SERVICE_RUNNING 0x4
SERVICE_CONTINUE_PENDING 0x5
SERVICE_PAUSE_PENDING 0x6
SERVICE_PAUSED 0x7
// ping
echo exec("c:\\windows\\system32\\ping -n 1 -w 1 10.10.0.2", $input, $result);
if ($result == 0){
	echo $result;
}else{
	echo $result;
}
*/
	}
	static function serialize($o) {
		return bin2hex(serialize($o));
	}
	static function unSerialize($s) {
		return unserialize(pack("H*", $s));
	}
	static function hex_dump($data, $newline="") {
		static $from = '';
		static $to = '';
		static $width = 16; # number of bytes per line
		static $pad = '.'; # padding for non-visible characters
		if ($from==='') {
			for ($i=0; $i<=0xFF; $i++) {
				$from .= chr($i);
				$to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
			}
		}
		$hex = str_split(bin2hex($data), $width*2);
		$chars = str_split(strtr($data, $from, $to), $width);
		$offset = 0;
		$dump = '';
		foreach ($hex as $i => $line) {
			$dump .= sprintf('%6X',$offset).' : '.implode(' ', str_split($line,2)) . ' [' . $chars[$i] . ']' . $newline;
			$offset += $width;
		}
		return $dump;
	}
	static function loginLog($log){
		if(COMPUTER != DEVELOP) {
			@error_log(date('Y.m.d H:i:s') . " $log\r\n" ,3, RECEPTION_LOG);
			self::log1($log);
		}
	}
	static function jsonDecode ($msg) {
		return strtr($msg, array("\\u0430"=>"а", "\\u0431"=>"б", "\\u0432"=>"в",
			"\\u0433"=>"г", "\\u0434"=>"д", "\\u0435"=>"е", "\\u0451"=>"ё", "\\u0436"=>"ж", "\\u0437"=>"з", "\\u0438"=>"и",
			"\\u0439"=>"й", "\\u043A"=>"к", "\\u043B"=>"л", "\\u043C"=>"м", "\\u043D"=>"н", "\\u043E"=>"о", "\\u043F"=>"п",
			"\\u0440"=>"р", "\\u0441"=>"с", "\\u0442"=>"т", "\\u0443"=>"у", "\\u0444"=>"ф", "\\u0445"=>"х", "\\u0446"=>"ц",
			"\\u0447"=>"ч", "\\u0448"=>"ш", "\\u0449"=>"щ", "\\u044A"=>"ъ", "\\u044B"=>"ы", "\\u044C"=>"ь", "\\u044D"=>"э",
			"\\u044E"=>"ю", "\\u044F"=>"я", "\\u0410"=>"А", "\\u0411"=>"Б", "\\u0412"=>"В", "\\u0413"=>"Г", "\\u0414"=>"Д",
			"\\u0415"=>"Е", "\\u0401"=>"Ё", "\\u0416"=>"Ж", "\\u0417"=>"З", "\\u0418"=>"И", "\\u0419"=>"Й", "\\u041A"=>"К",
			"\\u041B"=>"Л", "\\u041C"=>"М", "\\u041D"=>"Н", "\\u041E"=>"О", "\\u041F"=>"П", "\\u0420"=>"Р", "\\u0421"=>"С",
			"\\u0422"=>"Т", "\\u0423"=>"У", "\\u0424"=>"Ф", "\\u0425"=>"Х", "\\u0426"=>"Ц", "\\u0427"=>"Ч", "\\u0428"=>"Ш",
			"\\u0429"=>"Щ", "\\u042A"=>"Ъ", "\\u042B"=>"Ы", "\\u042C"=>"Ь", "\\u042D"=>"Э", "\\u042E"=>"Ю", "\\u042F"=>"Я")
			);
	}
	function varName( $v ) {
		$trace = debug_backtrace();
		$vLine = file( __FILE__ );
		$fLine = $vLine[ $trace[0]['line'] - 1 ];
		preg_match( "#\\$(\w+)#", $fLine, $match );
		if($match) {
			Str::log1($match);
			return $match[0];
		}
	}
	function cp1251toUTFnew( $s ) {
		$t="";
		$s = preg_replace(array('/&#61480;/i','/&#61481;/i'),array('(',')'),$s);
		for( $i = 0 ; $i < strlen( $s ) ; $i++ ) {
			$c = ord( substr( $s , $i , 1 ) );
			if( $c == 0xB2 ) { $t .= 'I' ; }
			elseif ( $c == 0x96 ){ $t .= '­' ; }
			elseif ( $c == 0xB9 ){ $t .= '№' ; }
			elseif ( $c == 0x93 || $c == 0x94 ){ $t .= '"' ; }
			elseif ( $c >= 0xC0 && $c <= 0xEF ) { $t .= chr( 0xD0 ) . chr( $c - 0x30 ) ; }
			elseif ( $c >= 0xF0 && $c <= 0xFF ) { $t .= chr( 0xD1 ) . chr( $c - 0x70 ) ; }
			else { $t .= chr( $c ) ; }
		}
		return $t ;
	}
	function cp1251toUTF($s){
		return preg_replace(array('/\xB2/i','/([\xC0-\xC2\xC5-\xFF])/e','/[\x93\x94]/i','/\x96/i','/\xC3/i','/\xC4/i','/&#61480;/i','/&#61481;/i','/\xB9/i'),
		array('I','(ord("$1")<0xF0)?chr(0xD0).chr(ord("$1")-0x30):chr(0xD1).chr(ord("$1")-0x70)','"','­','Г','Д','(',')','№'),$s);
	}
	function cp1251toUCS($s){
		return preg_replace('/([\xC0-\xFF])/e','chr((ord("$1")+0x350)/256).chr((ord("$1")+0x350)%256)',$s);
	}
	function cp1251toAsc($s){
		$search=array('/([\xC0-\xFF])/e','/[\x93\x94]/i','/\xB9/i');
		$replace=array('"&#".(ord("$1")+0x350).";"','"','&#8470;');
		return preg_replace($search,$replace,$s);
	}
	function utfToAsc( $s ){
		$search=array('/(\xD0)([\x90-\xBF])/e','/(\xD1)([\x80-\x8F])/e','/\xE2\x84\x96/i','/\xE2\x80\x9C/i','/\xE2\x80\x9D/i');
		$replace=array('"&#".(ord("$1")*256+ord("$2")-0xCC80).";"','"&#".(ord("$1")*256+ord("$2")-0xCD40).";"','&#8470;','&#8222;','&#8220;');
		return preg_replace($search,$replace,$s);
	}
	function cp1251aToUTF($s){
		return preg_replace('/\xC3([\x80-\xBF])/e','(ord("$1")<0xB0)?chr(0xD0).chr(ord("$1")+0x10):chr(0xD1).chr(ord("$1")-0x30)',$s);
	}
	function utf2cp1251($s){
		return preg_replace('/\xC3([\x80-\xBF])/e','(ord("$1")<0xB0)?chr(0xD0).chr(ord("$1")+0x10):chr(0xD1).chr(ord("$1")-0x30)',$s);
	}
	function utf2unicode($s){
		return preg_replace('/([\xD0\xD1])([\x80-\xBF])/e','(ord("$1")==0xD0)?chr(4).chr(ord("$2")-0x80):chr(4).chr(ord("$2")-0x40)',$s);
	}
	function utf2cp1251b($s){
		#myLog(stringToHexTxt($s));
		$s=preg_replace('/\xE2\x84\x96\x0/e','chr(0xB9)',$s);
		return preg_replace('/([\xD0\xD1])([\x80-\xBF])/e','(ord("$1")==0xD0)?chr(ord("$2")+0x30):chr(ord("$2")+0x70)',$s);
	}
	function utf2cp1251a($s){
		return preg_replace('/\xC3([\x80-\xBF])/e','chr(ord("$1")+0x40)',$s);
	}
	function cp1251toU( $s ) { #
		return preg_replace('/([\xC0-\xFF])/e','chr((848+ord("$1"))/256).chr((848+ord("$1"))%256)',$s);
	}
	function utf2cp1251c($s){
		return preg_replace('/([\x10-\x4F])\x0(\x4)\x0/e','chr(ord("$1")).chr(ord("$2")',$s);
	}
	function utf2cp1251d($s){
		return preg_replace('/([\x10-\x4F])\x0\x4\x0/e','(ord("$1")<0x40)?chr(0xD0).chr(ord("$1")+0xB0):chr(0xD1).chr(ord("$2"+0x70)',$s);
	}
	function MysqlEscape($s){return preg_replace('/([\x00\n\r\'\"\x1A\x5C])/e','chr(0x5C).chr(ord($1))',$s);}
	function anyWordLikeClause ($field,$words) {
		if (!$field || !$words )
			return ;
		$wordsTmp  = $words;
		$sql = '';
		$aWords  = explode(' ',$wordsTmp);
		foreach ($aWords as $word) {
			if (strlen($word) > 0) {
				if ($sql) 
					$sql .= ' OR ';
				$sql .= "$field LIKE '%$word%'" ;
			}
		}
		if ($sql)
			return " AND ($sql)";
	}
	function isLnc($lnc) {
		$LNC_WEIGHTS = array(21,19,17,13,11,9,7,3,1);
		if (!preg_match('/^\d{10}$/',$lnc))
			return false;
		$checksum = substr($lnc,9,1);
		$lncsum = 0;
		for ($i=0;$i<9;$i++)
			$lncsum += substr($lnc,$i,1) * $LNC_WEIGHTS[$i];
		$valid_checksum = $lncsum % 10;
		if ($checksum == $valid_checksum)
			return true;
		else
			return false;
	}
	function isEgn($egn) {
		$EGN_WEIGHTS=array(2,4,8,5,10,9,7,3,6);
		if (!preg_match('/^\d{10}$/',$egn))
			return false;
		$year = substr($egn,0,2);
		$mon  = substr($egn,2,2);
		$day  = substr($egn,4,2);
		if ($mon > 40) {
			if (!checkdate($mon-40, $day, $year+2000)) return false;
		} else
		if ($mon > 20) {
			if (!checkdate($mon-20, $day, $year+1800)) return false;
		} else {
			if (!checkdate($mon, $day, $year+1900)) return false;
		}
		$checksum = substr($egn,9,1);
		$egnsum = 0;
		for ($i=0;$i<9;$i++)
			$egnsum += substr($egn,$i,1) * $EGN_WEIGHTS[$i];
		$valid_checksum = $egnsum % 11;
		if ($valid_checksum == 10)
			$valid_checksum = 0;
		//echo $checksum == $valid_checksum.' '.$egn;
		if ($checksum == $valid_checksum)
			return true;
		else
			return false;
	}
	function gzcompressfile($source,$level=false) {
	   $dest=$source.'.'.date('YmdHis').'.gz';
	   $mode='wb'.$level;
	   $error=false;
	   if($fp_out=gzopen($dest,$mode)){
		   if($fp_in=fopen($source,'rb')){
			   while(!feof($fp_in))
				   gzwrite($fp_out,fread($fp_in,1024*512));
			   fclose($fp_in);
			   }
			 else $error=true;
		   gzclose($fp_out);
		   }
		 else $error=true;
	   if($error) return false;
		 else return $dest;
	}
	function addPage($page) {
		$page=basename($page);
		if (!array_key_exists('_pages',$_SESSION)) {
			$_SESSION['_pages']=$page.durationHumanU1(microtime(true)-$GLOBALS['startAt']);
		} else {
			$_SESSION['_pages'].='<br/>'.$page.durationHumanU1(microtime(true)-$GLOBALS['startAt']);
		}
	}
	function dumpPage($page, $leave=false) {
		if(IS_DEBUG) {
			$page=basename($page);
			echo '<span style="font-size:9px;">';
			if (array_key_exists('_pages',$_SESSION)) {
				echo $_SESSION['_pages'];
				if(!$leave) {
					unset($_SESSION['_pages']);
				}
			}
			$duration = microtime(true) - $GLOBALS['startAt'];
			$durationStr = $page.durationHumanU1($duration);
			if($duration > 3) {
				$fromPrevious = durationHumanU1($GLOBALS['startAt'] - floatval(Session::get('lastFinishedAt')));
				Str::log1("duration $durationStr interval from previous execution $fromPrevious");
			}
			Session::set('lastFinishedAt',microtime(true));
			echo "<br />$durationStr</span><br/>";
		}
		 session_write_close();
		if(COMPUTER != DEVELOP)getRemoteData2('https://127.0.0.1/c/check_replication.php?session_id=' . session_id());
	}
	function dateAdd($interval,$number,$dateTime) {
		$dateTime = (strtotime($dateTime) != -1) ? strtotime($dateTime) : $dateTime;   
		$dateTimeArr=getdate($dateTime);
		$yr=$dateTimeArr[year];
		$mon=$dateTimeArr[mon];
		$day=$dateTimeArr[mday];
		$hr=$dateTimeArr[hours];
		$min=$dateTimeArr[minutes];
		$sec=$dateTimeArr[seconds];
		switch($interval) {
			case "s"://seconds
				$sec += $number; 
				break;
			case "n"://minutes
				$min += $number; 
				break;
			case "h"://hours
				$hr += $number; 
				break;
			case "d"://days
				$day += $number; 
				break;
			case "ww"://Week
				$day += ($number * 7); 
				break;
			case "m": //similar result "m" dateDiff Microsoft
				$mon += $number; 
				break;
			case "yyyy": //similar result "yyyy" dateDiff Microsoft
				$yr += $number; 
				break;
			default:
				$day += $number; 
		}   
		$dateTime = mktime($hr,$min,$sec,$mon,$day,$yr);
		$dateTimeArr=getdate($dateTime);
		$nosecmin = 0;
		$min=$dateTimeArr[minutes];
		$sec=$dateTimeArr[seconds];
		if ($hr==0){$nosecmin += 1;}
		if ($min==0){$nosecmin += 1;}
		if ($sec==0){$nosecmin += 1;}
		if ($nosecmin>2){ return(date("Y-m-d",$dateTime));} else { return(date("Y-m-d G:i:s",$dateTime));}
	}
	function dateDiff($interval,$dateTimeBegin,$dateTimeEnd) {
		//Parse about any English textual datetime
		//$dateTimeBegin, $dateTimeEnd
		$dateTimeBegin=strtotime($dateTimeBegin);
		if($dateTimeBegin === -1) {
			return("..begin date Invalid");
		}
		$dateTimeEnd=strtotime($dateTimeEnd);
		if($dateTimeEnd === -1) {
			return("..end date Invalid");
		}
		$dif=$dateTimeEnd - $dateTimeBegin;
		switch($interval) {
			case "s"://seconds
			   return($dif);
			case "n"://minutes
			   return(round($dif/60)); //60s=1m
			case "h"://hours
			   return(round($dif/3600)); //3600s=1h
			case "d"://days
			   return(round($dif/86400)); //86400s=1d
			case "ww"://Week
			   return(round($dif/604800)); //604800s=1week=1semana
			case "m": //similar result "m" dateDiff Microsoft
			   $monthBegin=(date("Y",$dateTimeBegin)*12)+date("n",$dateTimeBegin);
			   $monthEnd=(date("Y",$dateTimeEnd)*12)+date("n",$dateTimeEnd);
			   $monthDiff=$monthEnd-$monthBegin;
			   return($monthDiff);
			case "yyyy": //similar result "yyyy" dateDiff Microsoft
				return(date("Y",$dateTimeEnd) - date("Y",$dateTimeBegin));
			default:
				return(round($dif/86400)); //86400s=1d
		}
	}
	// $subject = '=?UTF-8?B?'.base64_encode($subject).'?=';
}
?>