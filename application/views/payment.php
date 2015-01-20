<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 	<head>
	  	<title> New Document </title>
	  	<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.4/jquery.mobile-1.4.4.min.css">
		<script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
		<script src="http://code.jquery.com/mobile/1.4.4/jquery.mobile-1.4.4.min.js"></script>
 	</head>
<?php
if(isset($_REQUEST) && isset($_REQUEST['first']) && $_REQUEST['first'] == 1)
{
?>
	<script>
		//location.href="https://api.meetup.com/2/groups?&sign=true&photo-host=public&member_id=self&page=20"; 
		location.href="https://secure.meetup.com/oauth2/authorize?client_id=enk4i32l9r6446radpldt54inj&response_type=code&redirect_uri=http://<?=$_SERVER['SERVER_NAME']?>/vchat/imhere/user_meetup_login";
	</script>
<?php
}
if(isset($_REQUEST) && isset($_REQUEST['code']) && $_REQUEST['code'] != "")
{
	//echo "Code received";
?>
	<form method="post" action="https://secure.meetup.com/oauth2/access" style="display:none;">
		<input type="text" name="client_id" value="enk4i32l9r6446radpldt54inj">
		<input type="text" name="client_secret" value="ug5or8i647j1c37d35hndj14rh">
		<input type="text" name="grant_type" value="authorization_code">
		<input type="text" name="code" value="<?=$_REQUEST['code']?>">
		<input type="text" name="redirect_uri" value="http://<?=$_SERVER['SERVER_NAME']?>/vchat/imhere/user_meetup_login">
		<input type="submit" value="submit">
	</form>
<?php
}
	if(isset($_REQUEST) && isset($_REQUEST['access_token']))
	{
		
	}
?>
<body style="margin:0px;">
<div style="background-image:url('/vchat/public/image/login_success.png'); background-size:100% 100%; width:100%; height:100%; position:absolute;">
</div>
</body>
</html>