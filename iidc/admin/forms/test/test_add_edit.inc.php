<?php
if (!defined("__HQC__")){exit();};
error_reporting(E_ALL);
ini_set('display_errors', 1);
if(!isset($_REQUEST['foid'])){exit();};
include dirname(__DIR__)."/forms.class.php";

$uploadDir = hqc_path ."/data/temp/forms";
define ('this_url',this_url());
function getDirContents($dir,&$results = array()) {

	if(!is_dir($dir))
	return;

	$files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
			$url = hqc_url.str_replace(array(hqc_path,'\\'),array('','/'),$path);
            $results[basename($dir)][] = $url;
        } else if ($value != "." && $value != "..") {
            getDirContents($path,$results);
        }
    }
    return $results ;
}
?>
<?php if($form = $hqcdb->get_row("SELECT * FROM hqc_forms WHERE foid='$_GET[foid]'")){?>
<script>
<?php
if(($_GET['act']=='view' or $_GET['act']=='update') and isset($_GET['tstid'])){
	if($form_files = getDirContents($uploadDir."/".$_GET['tstid'])){?>
			var formDocs = '[<?php echo json_encode($form_files);?>]';
			<?php };?>
			function makeFileTag(url){
				urlParts = url.split('/');
				fileName = urlParts[urlParts.length-1]
				tag = '<div class="uploaded"><a href="'+url+'" target="_blank">'+fileName+'</a><i class="fa fa-trash-alt"></i></div>';
				return tag;
			}

			function deleteDocument(obj){
				url = jQuery(obj).parent('div').find('a').attr('href');
				urlParts = url.split('/');
				conf = 'Are you sure? <br/>Delete '+urlParts[urlParts.length-1]
				alert_confirm(conf);
				jQuery("button#alertYesBtn").click(function(){
					jQuery.post('<?php echo cur_dir;?>_save.php', {act:'deleteFile',url:url,foid:'<?php echo $_GET['foid'];?>'}).done(function(data) {
					if (data){
						if (data.indexOf("error:")>-1){
							alert_message(data.replace('error:',''));
							return false;
						}

						jQuery(obj).parent('div').remove();
						close_alert();
					}

					})
				})

			}

			function insertDocuments(){
				if(typeof formDocs != 'undefined'){
				var obj = jQuery.parseJSON(formDocs);
				jQuery(obj).each(function(objkey, fields) {
					jQuery.each( fields, function( key, urls ) {
						 if(urls.length>1){
							jQuery.each( urls, function(k,url) {
							jQuery("#formHohder input[name='"+key+"[]'],#formHohder input[name='"+key+"']").after(makeFileTag(url));
							});
						 } else {
							jQuery("#formHohder input[name='"+key+"[]'],#formHohder input[name='"+key+"']").after(makeFileTag(urls[0]));
						 }
						});
					});

					jQuery("#formHohder .fa-trash-alt").click(function(e) {
                        deleteDocument(this);
                    });
				}

			}
<?php
}
?>
</script>
<h3 style="text-align:center;color:red">Test <?php echo $form['form_name'];?> &raquo; <?php echo $form['form_id'];?></h3>
<?php }?>
<?php if($_GET['act']=='insert'){
//insert an new record -------------------------------------------------------------
?>
	<div style="padding:20px;width:1000px;margin:0 auto" id="formHolder">
	<?php echo $amform->get_form($_REQUEST['foid']);?>
    </div>
<?php } elseif($_GET['act']=='view' and isset($_GET['tstid'])){

// view form-------------------------------------------------------------------------
		if($form = $hqcdb->get_row("SELECT * FROM hqc_forms_test WHERE tstid='$_GET[tstid]'")){?>
        <?php if($data = json_decode(str_replace("\r\n",'\n',$form['form_content']),true)){

			$data['date_of_application'] = date ("d F Y",strtotime($form['inserted_on']));?>
			<style>
            #formHohder td strong {margin: 2px 0}
			.checkRadioImg {width:16px;height:16px;margin:0 5px;}
			label {display: inline-block;}
			label:hover{color:default}
			.fa-trash-alt{display:none}
			info,.info{display:none}
            </style>

            <div id="formHohder" style="width:1000px; margin:0 auto">
            <?php echo $amform->view_form($_REQUEST['foid'],$data);?>
            <center><a class="button" href="?inc=test&foid=<?php echo $_REQUEST['foid'];?>">Cancel</a> <a class="button" href="?inc=test_add_edit&foid=<?php echo $_REQUEST['foid'];?>&tstid=<?php echo $_REQUEST['tstid'];?>&act=update">Edit</a> <a class="button" href="pdf.php?foid=<?php echo $_REQUEST['foid'];?>&tstid=<?php echo $_REQUEST['tstid'];?>" target="pdfIframe">Download</a></center>
            </div>
            <script>
            jQuery("#formHohder input[type='text'],#formHohder textarea").each(function(index, element) {
               val = jQuery(this).val().trim();

			   if (val!='')
			   val = '<span class="viewEl">'+val+'</span>'
			   else
			   val = '<span style="color:#900">(Not filled by the applicant)</span>';
			    jQuery(this).replaceWith(val);
            });

            jQuery("#formHohder select").each(function(index, element) {
                val = jQuery(this).children("option").filter(":selected").text();
				if (val!='')
			   val = '<span class="viewEl">'+val+'</span>'
			   else
			   val = '<span style="color:#900">(Not filled by the applicant)</span>';
				jQuery(this).replaceWith(val)
            });

            jQuery("#formHohder input[type='checkbox']:checked,#formHohder input[type='radio']:checked").replaceWith('<img src="<?php echo hqc_url;?>/images/check-square-checked.svg" class="checkRadioImg"/>');
            jQuery("#formHohder input[type='checkbox'],#formHohder input[type='radio']").replaceWith('<img src="<?php echo hqc_url;?>/images/square.svg" class="checkRadioImg"/>');
            jQuery("#formHohder input[type='hidden'],#formHohder input[type='button'],#formHohder input[type='submit'],#formHohder input[type='reset']").remove();
			insertDocuments();
			jQuery("#formHohder input[type=file]").remove();
           jQuery("#formHohder").css("visibility","visible");
            </script>
		<?php
		};
	};
} elseif($_GET['act']=='update' and isset($_GET['tstid'])){
//update record------------------------------------------------------------------------
	if($data = $hqcdb->get_row("SELECT * FROM hqc_forms_test WHERE tstid='$_GET[tstid]'")){?>
    <style>
	#formHohder .fa-trash-alt{font-size: 14px !important; margin-left: 20px;}
	#formHohder .uploaded {margin:5px}
	</style>
	<div id="formHohder" style="width:1000px; margin:0 auto">
    <?php echo $amform->edit_form($_REQUEST['foid'],json_decode($data['form_content'],true));?>
    </div>
     <script>
            jQuery("#formHohder form").prepend('<input type="hidden" name="tstid" value="<?php echo $_GET['tstid'];?>"/>');
			insertDocuments();
	</script>
    <?php };
};?>