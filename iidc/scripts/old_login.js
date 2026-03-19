<!--
$(document).ready(function(e) {
    $("#loginTable").height($(this).height())
	$("#loginDiv").css({visibility:"visible"})
	
$("#username").keypress(function(event) {
  if ( event.which == 13 ) {
    doLogIn();
	return false;
   }
});

$("#password").keypress(function(event) {
  if ( event.which == 13 ) {
    doLogIn();
	return false;
   }
});

$("#email").keypress(function(event) {
  if ( event.which == 13 ) {
    snedMeThePass();
	return false;
   }
});

});

function switchLoginPass(showDiv){
	$("#doLogin").css({display:"none"})
	$("#doSendPassword").css({display:"none"})
	$("#"+showDiv).css({display:"block"})
}

function inputOnOff(obj,onOff){
if (onOff=="on"){
	$(obj).css('background-color', 'white');
}

if (onOff=="off"){
	if(obj.value=="")
	$(obj).css('background-color', 'transparent').val('');
}
}

function doLogIn()
{
if ($("#username").val() == "Username" || $("#password").val() == "Password")
{
alert("Please fill in the username and  password");
return false;
}
else
{
		var time= new Date().getTime();
		$.post(prog_www+"/login_out.php?tm="+time, {act: "logIn",username:$("#username").val(),password:$("#password").val()},
		function(data) {
			if (data!=""){
				if(data.indexOf("success_admin")>-1)
					document.location.replace(prog_www+"/admin")
				else if(data.indexOf("success_client")>-1)
					document.location.replace(prog_www+"/company")
				else 
					alert(data);
				};
			 });
}
return false;
}

function snedMeThePass()
{
if (($("#email").val().indexOf("@") <= 0)||($("#email").val().indexOf(".") <= 0))
{
alert("Please enter a valid email address");
return false;
}
else
{
		var time= new Date().getTime();
		$.post("login_out.php?tm="+time, {act: "sendPassword",email:$("#email").val()},
		function(data) {
			if (data!=""){
				if(data.indexOf(':')){
				reData = data.split(':');
				if (reData[0]=="success"){
					alert(reData[1]);
					switchLoginPass('doLogin')
					} else {
					alert(data);
				}
				}
				};
			 });
}
return false;
}

-->