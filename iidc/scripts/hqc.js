/* ayoubmedia.com scripts*/

function check_hqc_form(obj){
url = jQuery(obj).attr('action');
jQuery.post(url, jQuery(obj).serialize())
        .done(function(data) {
            if (data.trim().length >0)
            {
				if (data.indexOf("error:")>-1){
					alert(data.replace('error:',''));
				}
				else if(data.indexOf("reload:")>-1)
				{
					location.reload();
				}  
				else if(data.indexOf("url:")>-1){
					document.location = data.replace('url:','')
				}
				else
				{
				jQuery(obj).parent().html(data.replace("html:",""))
				}
            }
            else {
            alert('Something went wrong with the log-in, please try again.');
            }
        });
return false;
}