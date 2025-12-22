<?php
$prog_path = dirname($_SERVER['SCRIPT_FILENAME']);
$prog_www = str_replace($_SERVER['DOCUMENT_ROOT'],"",$prog_path);

/*website root path*/
$root_path = str_replace(strrchr($prog_path,"/"),"",$prog_path);
$website = str_replace($_SERVER['DOCUMENT_ROOT'],"",$root_path);

$path_final = "<?php\n";
$path_final .= "\$prog_www = \"$prog_www\";\n";
$path_final .= "\$prog_path = \"$prog_path\";\n";
$path_final .= "\$website = \"$website\";\n";
$path_final .= "\$root_path = \"$root_path\";\n";
$path_final .= "?>";

$fl = "config/paths.inc.php";
$fls = fopen($fl,"w");
fwrite($fls,$path_final, strlen($path_final));
fclose($fls);
?>
