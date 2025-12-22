<?PHP
function make_query($query_tbl,$query_act,$query_fld2skip)
{	
foreach ($GLOBALS as $key => $value)
	{
	$$key = str_replace(array("'","‘"),array("\'","\‘"),$value);
	}
$query_fld2skip = explode(',',$query_fld2skip);
$query_result = mysql_query("SHOW COLUMNS FROM $query_tbl");
for($i=0;$i<mysql_num_rows($query_result);$i++){
	$query_fld_name =  mysql_result($query_result, $i);
if(!in_array($query_fld_name,$query_fld2skip))
{
$query_expo_key[] = $query_fld_name;
if (!isset($$query_fld_name))
	{
	$$query_fld_name ="";
	}
	$query_expo_key_value[]="'".str_replace("'","\'",$$query_fld_name)."'";
	$query_expo_edit_value[]="$query_fld_name='".str_replace("'","\'",$$query_fld_name)."'";
}
}

if ($query_act=='insert' and count($query_expo_key_value)>0)
	{
	$query_expo2insert = "(".implode(', ',$query_expo_key).") VALUES (".implode(', ',$query_expo_key_value).")";
	return $query_expo2insert;
	}
	
if ($query_act=='update' and count($query_expo_edit_value)>0)
	{
	$query_expo2update = implode(',',$query_expo_edit_value);
	return $query_expo2update;
    }
}	

function dublicate($query_tbl,$whr,$query_fld2skip)
{
$query_fld2skip = explode(',',$query_fld2skip);
$query_result = mysql_query("SHOW COLUMNS FROM $query_tbl");
for($i=0;$i<mysql_num_rows($query_result);$i++){
$query_fld_name =  mysql_result($query_result, $i);
if(!in_array($query_fld_name,$query_fld2skip))
{
$query_key[] = $query_fld_name;
}
}
if (count($query_key)>0)
	{
	$select_from = implode(",",$query_key);
	MYSQL_QUERY("insert into $query_tbl select $select_from from $query_tbl WHERE $whr");
	}
}
?>