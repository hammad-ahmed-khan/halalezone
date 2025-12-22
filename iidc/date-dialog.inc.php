<script>
	$(function() {
		$( "#datepicker" ).datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat:'dd/mm/yy',
			altField: "#actualDate"
		});
	});
	
	function showDateDialog(ttl,h){
		if(h==null)
		h=300;
		//$( "#dateDialog" ).dialog( "destroy" );
		$( "#dateDialog" ).attr({"title":ttl});
		$( "#dateDialog" ).dialog({
			resizable: false,
			height:h,
			width:250,
			modal: true,
			buttons: {
				"Ok": function() {
					if(typeof(getDateData)!='undefined')
					getDateData($("#actualDate").val());
					$( this ).dialog( "close" );
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			}
		});		
	}
</script>

<div id="dateDialog" title="" style="text-align:center;display:none;overflow:hidden">
<b>Date:</b> <input type="text" id="actualDate" disabled style="border:0px;background:#FFF;padding:0px;margin:0px;width:150px"/>
<div id="datepicker"></div>
</div>

    