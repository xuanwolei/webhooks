<?php
$jsonData = file_get_contents('php://input');
if (empty($jsonData)) {
    die('send fail');
}
$config = include('./config.php');

//ip验证
if ($config['ip_white_on'] && !in_array($_SERVER['REMOTE_ADDR'], $config['ip_white_list'])) {
    die("Wrongful ip");
}

//签名验证
$key = $config['key'];
list($algo,$sha1) = explode('=',$_SERVER['HTTP_X_HUB_SIGNATURE']);
$sign = hash_hmac($algo,$jsonData,$key);
if ($sign != $sha1) {
	die('sign error');
}

$content = json_decode($jsonData, true);
//区分项目
$path = $config['projects'][$content['repository']['name']];
if (empty($path)) {
    die('name no true');
}

$res = shell_exec("cd {$path} && git pull");//以www用户运行
$res_log = '-------------------------'.PHP_EOL;
$res_log .= $content['pusher']['name'] . ' 在' . date('Y-m-d H:i:s') . '向' . $content['repository']['name'] . '项目的' . $content['ref'] . '分支push了'. PHP_EOL;
$res_log .= $res.PHP_EOL;
file_put_contents("git-webhook.txt", $res_log, FILE_APPEND);//追加写入