<?php
//$rec _Rcords_ must be asigned in the parent file
//$lmt _Search limit_ must be asigned in the parent file
if (($rec != 0) and ($rec > $lmt)){
print "<table width=100% border=0><tr><td width=100% align=center>";
if($q){$q="&q=$q";};
if ($act) $act="&act=$act";
if($srch_in){$srch_in="&srch_in=$srch_in";};
if ($opt){$opt="&opt=$opt";};
if ($pg){$pg="&pg=$pg";};
for ($i = 0; $i < ($rec / $lmt); $i++) {
$str=($i * $lmt);
if ($st == $str){print " <font color=red>$i</font>";}
else
{
print " <a href=\"$PHP_SELF?inc=$inc&st=$str&rec=$rec$q$opt$pg$srch_in$act\">$i</a>";};
}

print "</td></tr></table>";
}
?>
