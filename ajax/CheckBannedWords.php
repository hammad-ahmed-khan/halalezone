<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
	
	$text = getGetParam('text');
	
	$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	$sql = "SELECT LCASE(value) FROM tsettings WHERE name = 'bannedwords'";
	$rows = $dbo->prepare($sql);
	$rows->execute();
    $bannedWords = $rows->fetchColumn();
	
	if ($bannedWords != "") {
		//$bannedWords = array_map('sanitize', explode(",", $bannedWords));
		$bannedWords = array_filter(array_map('sanitize', explode("\n", $bannedWords)));
		//$allWords = array_filter(array_map('sanitize', explode(" ", $text)));
		//if (count(array_intersect($allWords, $bannedWords))) {
		if (containsBannedWord($text, $bannedWords)) {
			die('1');
		}
	}
	
	die('0');
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}

function containsBannedWord($str, array $arr)
{
    foreach($arr as $a) {
		if (preg_match("/\b$a\b/i", $str)) {
			return 1;
		}
        //if (stripos($str,$a) !== false) return $a;
    }
    return false;
}
?>