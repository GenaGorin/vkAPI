<?php
header('Content-Type: text/html; charset=utf-8');
require "vk.php";
require "params.php";
$from = $_POST['from'];
$to = $_POST['to'];
$res = new VK($from, $to, $params['key']);
$statistic = $res->getStatistic();
echo "Среднее время ответа за период - ".$statistic['averageAnswerTime'].' минут';
echo "<br>";
echo "Moda = ". $statistic['moda'];
echo "<br>";
echo "<h4>Диалоги с временем ответа более 15 минут</h4>";
foreach ($statistic['bigAnswerTimeDialogs'] as $dialog) {
    foreach ($dialog as $messages) {
        if ($messages['out'] == 1) {
            echo "Admin - ".$messages['body']."<br>";
        }else {
            echo "User - ".$messages['body']."<br>";
        }
    }
    echo "---<br>";
}
?>