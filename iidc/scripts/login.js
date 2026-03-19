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
	if(obj.name =="username" && obj.value=="Username")
	obj.value="";
	if(obj.name =="password" && obj.value=="Password"){
	obj.value="";
	}
	if(obj.name =="email" && obj.value=="Email address")
	obj.value="";
}
if (onOff=="off"){
	if(obj.name =="username" && obj.value=="")
	obj.value="Username";
	if(obj.name =="password" && obj.value==""){
	obj.value="Password";
	}
	if(obj.name =="email" && obj.value=="")
	obj.value="Email address";}
}
function doLogIn()
{
if ($("#username").val() == "Username" || $("#username").val() == "" || $("#password").val() == "Password" || $("#password").val() == "")
{
alert("Please fill in the username and  password");
return false;
}
else
{
		var time= new Date().getTime();
		$.post("/login_out.php?tm="+time, {act: "logIn",username:$("#username").val(),password:$("#password").val()},
		function(data) {
			if (data!=""){
				if(data.indexOf("success_office")>-1)
					location = "https://hqc.iidc.eu/offices/home"
				else if(data.indexOf("success_admin")>-1)
					location = "https://hqc.iidc.eu/admin";
				else if(data.indexOf("success_auditor")>-1)
					location = "https://hqc.iidc.eu/audit";
				else if(data.indexOf("success_client")>-1)
					location = "https://hqc.iidc.eu/company";
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