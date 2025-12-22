<?php /*
<link rel="stylesheet" href="<?php echo $prog_www;?>/scripts/jquery/themes/base/jquery.ui.all.css">
<script src="<?php echo $prog_www;?>/scripts/jquery/ui/jquery.ui.core.min.js"></script>
<script src="<?php echo $prog_www;?>/scripts/jquery/ui/jquery.ui.widget.min.js"></script>
<script src="<?php echo $prog_www;?>/scripts/jquery/ui/jquery.ui.mouse.min.js"></script>
<script src="<?php echo $prog_www;?>/scripts/jquery/ui/jquery.ui.button.min.js"></script>
<script src="<?php echo $prog_www;?>/scripts/jquery/ui/jquery.ui.draggable.min.js"></script>
<script src="<?php echo $prog_www;?>/scripts/jquery/ui/jquery.ui.position.min.js"></script>
<script src="<?php echo $prog_www;?>/scripts/jquery/ui/jquery.ui.dialog.min.js"></script>
 ?>
<script>
function showPopupDialog(obj,ttl){
		theObj = "#"+obj;	
		$(theObj).attr({"title":ttl});
		$(theObj).dialog({
			resizable: false,
			height:$(theObj).height(),
			width:$(theObj).width()+25,
			modal: true,
			buttons: {
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			}
		});		
	}
</script>
*/ ?>