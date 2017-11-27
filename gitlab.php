<?php
//gitlab webhook 自动部署脚本

$config = include('./config.php');
//ip验证
if ($config['ip_white_on'] && !in_array($_SERVER['REMOTE_ADDR'], $config['ip_white_list'])) {
    die("Wrongful ip");
}
$requestBody = file_get_contents("php://input");
if (empty($requestBody)) {
    die('send fail');
}
$content = json_decode($requestBody, true);
//区分项目
$path = $config['projects'][$content['repository']['name']];
if (empty($path)) {
    die('name no true');
}

//若是大于0
if ($content['total_commits_count']>0) {
    $res = shell_exec("cd {$path} && git pull");//以www用户运行
    $res_log = '-------------------------'.PHP_EOL;
    $res_log .= $content['user_name'] . ' 在' . date('Y-m-d H:i:s') . '向' . $content['repository']['name'] . '项目的' . $content['ref'] . '分支push了' . $content['total_commits_count'] . '个commit：' . PHP_EOL;
    $res_log .= $res.PHP_EOL;
    file_put_contents("git-webhook.txt", $res_log, FILE_APPEND);//追加写入
    echo 'success';
}