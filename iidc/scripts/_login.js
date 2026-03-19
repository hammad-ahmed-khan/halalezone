<!--
$(document).ready(function(e) {
    $("#loginTable").height($(this).height())
	$("#loginDiv").css({visibility:"visible"})
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
	obj.type="password";
	}

	if(obj.name =="email" && obj.value=="Email address")
	obj.value="";
}

if (onOff=="off"){
	if(obj.name =="username" && obj.value=="")
	obj.value="Username";
	
	if(obj.name =="password" && obj.value==""){
	obj.type="text";
	obj.value="Password";
	}

	if(obj.name =="email" && obj.value=="")
	obj.value="Email address";}

}

function doLogIn()
{
if ($("#username").val() == "Username" || $("#password").val() == "Password")
{
alert("Please fill in the username and  password");
}
else
{
		var time= new Date().getTime();
		$.post("login_out.php?tm="+time, {act: "logIn",username:$("#username").val(),password:$("#password").val()},
		function(data) {
			if (data!=""){
				if(data == "success"){
					if($("#username").val()=="admin" || $("#username").val()=="invoice")
					document.location.replace("admin")
					else
					document.location.replace("company")
					} else {
					alert(data);
				}
				};
			 });
}
}

function snedMeThePass()
{
if (($("#email").val().indexOf("@") <= 0)||($("#email").val().indexOf(".") <= 0))
{
alert("Please enter a valid email address");
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
}

-->