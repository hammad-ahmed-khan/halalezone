//usage loadFile('external_file_name','results div');
if (typeof($) == 'undefined'){
document.write('<scr'+'ipt src="'+cms_www+'/cms_js/jquery.js"></scri'+'pt>');
}

function loadFile(url,res){
	res="#"+res;
	$(res).html('<div style="text-align:center"><img src="'+cms_www+'/cms_js/images/loading.gif" align="center"/></div>');
	
$.ajax({
  url: url,
  cache: false,
  success: function(html){
    $(res).html(html);
  },
  error: function(html){
    $(res).html(html);
  },
  statusCode: {
    404: function() {
      $(res).html('page not found');
    }
  }
});
}