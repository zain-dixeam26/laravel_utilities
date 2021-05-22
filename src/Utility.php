<?php
namespace Dixeam\Classes;
use Excel;
class Utility {
	private static $datetime_view_format = 'j, F Y g:i a';
    private static $time_view_format = 'g:i a';
    private static $date_view_format = 'j, F Y';
    /*
    DATES TIME METHODS
    */
    public static function dateFormat($date) {
        return date(self::$date_view_format, strtotime($date));
    }
    public static function dateTimeFormat($date) {
        return date(self::$datetime_view_format, strtotime($date));
    }
	public static function getMonthDates($month,$year){
		$dates      =	[];
		$start_date =	new Carbon($year.'-'.$month.'-1');
		$end 		=	$year.'-'.$month.'-'.date('t', strtotime($start_date));
        $end_date   = 	new Carbon($end);
        for ($date  = $start_date; $date->lte($end_date); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }
        return $dates;
    }
    public static function timeago($date) {
       $timestamp = strtotime($date);   
       
       $strTime = array("second", "minute", "hour", "day", "month", "year");
       $length = array("60","60","24","30","12","10");

       $currentTime = time();
       if($currentTime >= $timestamp) {
            $diff     = time()- $timestamp;
            for($i = 0; $diff >= $length[$i] && $i < count($length)-1; $i++) {
            $diff = $diff / $length[$i];
            }

            $diff = round($diff);
            return $diff . " " . $strTime[$i] . "(s) ago ";
       }
    }
    public static function subtractDays($old_date,$days) {
        return date('Y-m-d H:i:s', strtotime('-'.$days.' day', strtotime($old_date)));
    }
    public static function addDays($old_date,$days) {
        return date('Y-m-d H:i:s', strtotime('+'.$days.' day', strtotime($old_date)));
    }
    public static function timeDifference($date,$resolved) {
       $timestamp = strtotime($date);   
       $resolved = strtotime($resolved);   
       
       $strTime = array("second", "minute", "hour", "day", "month", "year");
       $length = array("60","60","24","30","12","10");
       // echo $timestamp.'<br />'.$resolved;die();
       $currentTime = $resolved;
       if($currentTime >= $timestamp) {
            $diff     = $currentTime - $timestamp;
            for($i = 0; $diff >= $length[$i] && $i < count($length)-1; $i++) {
            $diff = $diff / $length[$i];
            }

            $diff = round($diff);
            return $diff . " " . $strTime[$i] . "(s)";
       }
    }
    /*
    EXPORT METHOD
    */
	public static function export($data, $filename = "filename", $sheetname = "sheetname", $ext = 'xlsx')
    {
    	if(isset($data[0])) {
    		$keys = array_keys($data[0]);
    		$nkeys = [];
    		foreach ($keys as $key => $val) {
    			$nkeys[$val] = $val;
    		}
    		array_unshift($data, $nkeys);

    	}
        \Excel::create($filename, function ($excel) use ($data, $sheetname) {
            $excel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->sheet($sheetname, function ($sheet) use ($data) {
                $sheet->fromArray($data, null, 'A1', true, false);
                //$sheet->setAutoFilter();
                $sheet->setAutoSize(true);
                //$sheet->freezeFirstRowAndColumn();
                /*$sheet->row(1,function($row){
                    $row->setBackground('#A9A9A9'); 
                    $row->setFont(array( 'size' => '12','bold' => true));
                });*/
            });
        })->export($ext);
    }
	public static function arrayFilterRecursive($array) 
	{ 
	   $array = array_map(function($item) {
                return is_array($item) ? self::arrayFilterRecursive($item) : $item;
            }, $array);
	   return array_filter($array, function($item) {
	   	return $item !== "" && $item !== null && (!is_array($item) || count($item) > 0);
	   });
	}
	public static function removeQSArr($url, $key) { 
		
		$url = preg_replace('/(.*)(?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&'); 
		$url = substr($url, 0, -1); 

		parse_str($url, $url);
		$unset = ['q','sort_by','page','order_by','_','limit','amp'];
		foreach ($unset as $key => $value) {
			if(isset($url[$value])) unset($url[$value]);
		}
		
		
		
		$url = http_build_query($url, '');
		$url = str_replace("%3F", "", $url);

		return $url; 
	}
	public static function arrayFilterWithNULL($array) 
	{ 
	   $array = array_map(function($item) {
                return is_array($item) ? self::arrayFilterWithNULL($item) : $item;
            }, $array);
	   return array_filter($array, function($item) {
	   	return $item !== "" && $item !== null && (!is_array($item) || count($item) > 0);
	   });
	}
	
	public static function encryptDecrypt($action, $string) {
	    $output = false;
	    $encrypt_method = "AES-256-CBC";
	    $secret_key = 'presteegkey';
	    $secret_iv = 'presteegivkey';
	    // hash
	    $key = hash('sha256', $secret_key);
	    
	    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
	    $iv = substr(hash('sha256', $secret_iv), 0, 16);
	    if ( $action == 'encrypt' ) {
	        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
	        $output = base64_encode($output);
	    } else if( $action == 'decrypt' ) {
	        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
	    }
	    return $output;
	}
	public static function unsetReq(&$req,$unset){
        foreach ($unset as $k=>$v) if(isset($req[$v])) unset($req[$v]);
    }
    
    public static function appendRoleArray(&$data,$user){
    	
    }
    public static function appendCreatedUser(&$data){
    	$data['created_user'] = \Auth::user()->id;
    }
	static function  extractEmails($string){

	    $pattern = '/[a-z0-9_\-\+]+@[a-z0-9\-]+\.([a-z]{2,3})(?:\.[a-z]{2})?/i';
	    preg_match_all($pattern, $string, $matches);
	    $email = [];
	    $block = [".jpg",".jpeg",".png"];
	    //$matches[0] = ['10098683_web1_ExpensiveProperties-300x200@2x.jpg','10098683_web1_ExpensiveProperties-300x200@2x.png','test@google.com','test@google.com','test@google.com','test@google.com'];
	    
	    if(@count($matches[0]) > 0) {
	    	
	    	foreach ($matches[0] as $key => $value) {
	    		$find = 0;
	    		foreach ($block as $ikey => $ivalue) {
		    		if(strpos($value, $ivalue) !== false){
		    			$find++;
		    		}
		    	}
		    	if($find == 0)
		    		$email[] = $value;
	    	}
	    	
	    }
	    return array_values(array_unique($email));
	   
	}
	static function extractLinks($string){
		
		preg_match_all('/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $string, $result);
		return $result['href'];
		//return $output;
	}
	
	static function getMozStats( $sites ){
		
		// Get your access id and secret key here: https://moz.com/products/api/keys
		$accessID = "mozscape-1cc9e3ce6a";
		$secretKey = "e33aeef1571eab593a05224c06aa2975";

		// Set your expires times for several minutes into the future.
		// An expires time excessively far in the future will not be honored by the Mozscape API.
		$expires = time() + 300;

		// Put each parameter on a new line.
		$stringToSign = $accessID."\n".$expires;

		// Get the "raw" or binary output of the hmac hash.
		$binarySignature = hash_hmac('sha1', $stringToSign, $secretKey, true);

		// Base64-encode it and then url-encode that.
		$urlSafeSignature = urlencode(base64_encode($binarySignature));

		// Add up all the bit flags you want returned.
		// Learn more here: https://moz.com/help/guides/moz-api/mozscape/api-reference/url-metrics
		$cols = "103616137252";

		// Put it all together and you get your request URL.
		$requestUrl = "http://lsapi.seomoz.com/linkscape/url-metrics/?Cols=".$cols."&AccessID=".$accessID."&Expires=".$expires."&Signature=".$urlSafeSignature;

		// Put your URLS into an array and json_encode them.
		$batchedDomains = $sites;
		$encodedDomains = json_encode($batchedDomains);
//echo $encodedDomains;die;
		// Use Curl to send off your request.
		// Send your encoded list of domains through Curl's POSTFIELDS.
		$options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS     => $encodedDomains
			);

		$ch = curl_init($requestUrl);
		curl_setopt_array($ch, $options);
		$content = curl_exec($ch);
		curl_close( $ch );
		
		return json_decode($content,true);

		// $contents = json_decode($content);
		// print_r($contents);
	}
	static function isImage($string){
		$block = [".jpg",".jpeg",".png"];
		foreach ($block as $key => $value) {
            if(strpos( $string, $value))
                return true;
        }
        return false;
	}
	static function inBlockSite($link){
		$block_sites = ['google.com','youtube.com','facebook.com',"twitter.com",'wikipedia.org','imdb.com','instagram.com','stackoverflow.com','pinterest','linkedin.com'];
        foreach ($block_sites as $key => $value) {
            if(strpos( $link, $value))
                return true;
        }
        return false;
    }
    static function isExpired($domain) {
    	/*$dns = dns_get_record($domain);
    	if(empty($dns))
    		return true;
    	else 
    		return false;*/
    	if ( gethostbyname($domain) != $domain ) {
    		return false;
		}
		else {
		  return true;
		}
    }
    /**
	 * @param string $domain Pass $_SERVER['SERVER_NAME'] here
	 * @param bool $debug
	 *
	 * @debug bool $debug
	 * @return string
	 */
	static function extractRootDomain($link, $debug = false)
	{
		$host = parse_url($link);

		if(!isset($host['host']))
			return $link;
		$domain = $host['host'];
		
		$original = $domain = strtolower($domain);
		if (filter_var($domain, FILTER_VALIDATE_IP)) { return $domain; }
		$debug ? print('<strong style="color:green">&raquo;</strong> Parsing: '.$original) : false;
		$arr = array_slice(array_filter(explode('.', $domain, 4), function($value){
			return $value !== 'www';
		}), 0); //rebuild array indexes
		if (count($arr) > 2)
		{
			$count = count($arr);
			$_sub = explode('.', $count === 4 ? $arr[3] : $arr[2]);
			$debug ? print(" (parts count: {$count})") : false;
			if (count($_sub) === 2) // two level TLD
			{
				$removed = array_shift($arr);
				if ($count === 4) // got a subdomain acting as a domain
				{
					$removed = array_shift($arr);
				}
				$debug ? print("<br>\n" . '[*] Two level TLD: <strong>' . join('.', $_sub) . '</strong> ') : false;
			}
			elseif (count($_sub) === 1) // one level TLD
			{
				$removed = array_shift($arr); //remove the subdomain
				if (strlen($_sub[0]) === 2 && $count === 3) // TLD domain must be 2 letters
				{
					array_unshift($arr, $removed);
				}
				else
				{
					// non country TLD according to IANA
					$tlds = array(
						'aero',
						'arpa',
						'asia',
						'biz',
						'cat',
						'com',
						'coop',
						'edu',
						'gov',
						'info',
						'jobs',
						'mil',
						'mobi',
						'museum',
						'name',
						'net',
						'org',
						'post',
						'pro',
						'tel',
						'travel',
						'xxx',
					);
					if (count($arr) > 2 && in_array($_sub[0], $tlds) !== false) //special TLD don't have a country
					{
						array_shift($arr);
					}
				}
				$debug ? print("<br>\n" .'[*] One level TLD: <strong>'.join('.', $_sub).'</strong> ') : false;
			}
			else // more than 3 levels, something is wrong
			{
				for ($i = count($_sub); $i > 1; $i--)
				{
					$removed = array_shift($arr);
				}
				$debug ? print("<br>\n" . '[*] Three level TLD: <strong>' . join('.', $_sub) . '</strong> ') : false;
			}
		}
		elseif (count($arr) === 2)
		{
			$arr0 = array_shift($arr);
			if (strpos(join('.', $arr), '.') === false
				&& in_array($arr[0], array('localhost','test','invalid')) === false) // not a reserved domain
			{
				$debug ? print("<br>\n" .'Seems invalid domain: <strong>'.join('.', $arr).'</strong> re-adding: <strong>'.$arr0.'</strong> ') : false;
				// seems invalid domain, restore it
				array_unshift($arr, $arr0);
			}
		}
		$debug ? print("<br>\n".'<strong style="color:gray">&laquo;</strong> Done parsing: <span style="color:red">' . $original . '</span> as <span style="color:blue">'. join('.', $arr) ."</span><br>\n") : false;
		return join('.', $arr);
	}
	static public function arrayUniqueByKey($array,$key){
		$tempArr = array_unique(array_column($array, $key));
		return array_intersect_key($array, $tempArr);
	}
}
