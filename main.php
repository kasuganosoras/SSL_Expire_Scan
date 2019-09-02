<?php
/**
 *
 *  SSL Expire Scan Tool by Akkariin
 *
 *  本代码使用 GPL v3 协议开源
 *
 */
$logo = <<<EOF
 ____ ____  _       _____           _ 
/ ___/ ___|| |     |_   _|__   ___ | |
\___ \___ \| |       | |/ _ \ / _ \| |
 ___) |__) | |___    | | (_) | (_) | |
|____/____/|_____|   |_|\___/ \___/|_|
                                      

EOF;

// 获取 SSL 证书到期时间
function get_ssl_expire_time($file) {
	$cert = openssl_x509_parse(file_get_contents($file));
	return $cert['validTo_time_t'] ?? 0;
}

// 扫描目录下的所有 SSL 证书
function scan_ssl_files($dir, $extensionName = "crt") {
	if(!file_exists($dir)) return Array();
	if(is_file($dir)) return Array(basename($dir) => get_ssl_expire_time($dir));
	$abspath = realpath($dir);
	$expiretime = Array();
	if($abspath) {
		$list = scandir($dir);
		foreach($list as $file) {
			if($file !== "." && $file !== "..") {
				$ext = pathinfo("{$dir}{$file}");
				if($ext['extension'] == $extensionName) {
					$expiretime["{$file}"] = get_ssl_expire_time("{$dir}{$file}");
				}
			}
		}
	}
	return $expiretime;
}

// 程序开始
echo $logo;
echo "SSL expire time scan tool by Akkariin\n";
if($argv[1] == "--help" || !isset($argv[1])) {
	exit("Usage: php {$argv[0]} <Directory or File> [extension name]\n\n");
}

// 参数存到变量
$scanpath = $argv[1] ?? "/usr/local/nginx/conf/ssl/";
$scanexts = $argv[2] ?? "crt";

// 扫描 SSL 证书
$exps = scan_ssl_files($scanpath, $scanexts);
if(count($exps) == 0) {
	echo "未扫描到 SSL 证书，请检查目录是否存在，以及目录下是否有证书文件\n";
}

// 获取最长的域名
$max_length = 0;
foreach($exps as $key => $value) {
	if(mb_strwidth($key) > $max_length) {
		$max_length = mb_strwidth($key);
	}
}

// 输出内容
$result = "";
$line_length = 0;
$time_length = 0;

// 遍历数组
foreach($exps as $key => $value) {
	$expired = "正常";
	$exptime = $value - time();
	$dateime = $value !== 0 ? date("Y-m-d H:i:s", $value) : "无效的证书";
	$padding = str_repeat(" ", $max_length - mb_strwidth($key));
	if($exptime <= 2592000 && $exptime > 0) {
		$expired = "一个月内将会过期";
	} elseif($value == 0) {
		$expired = "无效";
	} elseif($exptime < 0) {
		$expired = "已经过期";
	}
	if(mb_strwidth($dateime) > $time_length) {
		$time_length = mb_strwidth($dateime);
	}
	$padding2 = str_repeat(" ", 16 - mb_strwidth($expired));
	$padding3 = str_repeat(" ", 19 - mb_strwidth($dateime));
	$data = "| {$key}{$padding} | {$dateime}{$padding3} | {$expired}{$padding2} |";
	$line_length = mb_strwidth($data);
	$result .= $data . "\n";
}

// 格式
echo "+-" . str_repeat("-", $max_length) . "-+-" . str_repeat("-", $time_length) . "-+------------------+\n";
echo "| 名称" . str_repeat(" ", $max_length - 4) . " | 到期时间" . str_repeat(" ", $time_length - 8) . " | 证书状态         |\n";
echo "+-" . str_repeat("-", $max_length) . "-+-" . str_repeat("-", $time_length) . "-+------------------+\n";
echo $result;
echo "+-" . str_repeat("-", $max_length) . "-+-" . str_repeat("-", $time_length) . "-+------------------+\n\n";
