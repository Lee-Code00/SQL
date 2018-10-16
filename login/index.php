<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Login-FunnySQL</title>
	<link rel="shortcut icon" href="http://10.242.8.182/funnysql/res/favicon.png">
	<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Abril+Fatface">
</head>
<?php  unset($_COOKIE['funnysql']); ?>
<style>
    *{box-sizing:border-box}body{background-image:url("https://imageshack.com/v2_images/auth/bg1.jpg");background-size:cover;background-repeat:no-repeat;background-position:center;background-attachment:fixed;width:100%}.main{position:absolute;margin:auto;top:0;left:0;bottom:0;right:0;height:500px;text-align:center}a{text-decoration:none;color:black}header{width:100%;text-align:center;font-size:40px;font-family:'Abril Fatface',cursive;margin-bottom:110px}form{max-width:360px;margin:0 auto}.input{width:100%;text-align:center}input{height:40px;width:100%;border:0;font-size:12px;background:rgba(255,255,255,.7);padding:8px 12px;margin-bottom:10px}input[type='submit']{background:#f5bc22}input[type='submit']:active{background:red}input[type='focus']:focus{background:blue}.error{width:360px;text-align:center;color:white;background:red;font-size:12px;z-index:1;position:absolute;padding:12px 0;top:110px;display:none}.errorShow{animation-name:fadeInUp;animation-duration:1s;display:block}@keyframes fadeInUp{0%{opacity:0;transform:translateY(20px)}100%{opacity:1;transform:translateY(0)}}
</style>
<body>
	<div class="main">
		<header class="header"><a href="http://10.242.8.182/funnysql">FunnySQL</a></header>
		<form onsubmit="return false">
			<div class="error">something wrong!</div>
			<div class="input"><input type="text" name="host" placeholder="IP地址"></div>
			<div class="input"><input type="text" name="port" placeholder="端口"></div>
			<div class="input"><input type="text" name="userName" placeholder="用户名"></div>
			<div class="input"><input type="password" name="password" placeholder="密码"></div>
			<input type="submit" class="submit">
		</form>
	</div>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script>
		$(document).ready(function(){
			let errorMessage;
			$(".submit").click(function(){
				let host = $("input[name='host']").val();
				let port = $("input[name='port']").val();
				let userName = $("input[name='userName']").val();
				let password = $("input[name='password']").val();
				if(host === '')
					errorMessage = "IP地址不能为空！";
				else {
					let patternHost = /^(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)$/g;
					if(!patternHost.test(host))
						errorMessage = "IP地址错误，请重新输入!";
				}
				if(errorMessage == null)
					if(port === '')
						errorMessage = "端口不能为空！";
					else {
						const patternPort = /^(\d|[1-9]\d{1,3}|[1-5]\d{4}|6[0-4]\d{3}|65[0-4]\d{2}|655[0-2]\d|6553[0-5])$/g;
						if(!patternPort.test(port))
							errorMessage = "端口错误，请重新输入！";
					}
				if(errorMessage == null)
					if(userName === '')
						errorMessage = "用户名不能为空！";
				if(errorMessage == null)
					if(password === '')
						errorMessage = "密码不能为空！";
				if(errorMessage == null)
					$.ajax({
						url: "http://10.242.8.182/funnysql/test.php",
						type: "post",
						dataType: "json",
						data: {'host':host,'port':port,'userName':userName,'password':password},
                        timeout: 3000,
						success:function(data){
						    if(data.success  === false){
                                errorMessage = "连接错误，请检查输入！";
                                if(errorMessage != null) {
                                    $('.error').addClass("errorShow");
                                    $('.error').text(errorMessage);
                                    $('.error').show(2);
                                    setTimeout(function(){
                                        $('.error').removeClass("errorShow");
                                        $(".error").hide();
                                        errorMessage = null;
                                    }, 3000)
                                }
                            } else {
                                let msg = data.msg;
                                let date = new Date();
                                date.setTime(date.getTime() + 24 * 60 * 60 * 1000);
                                let setCookie = "funnysql=" + msg + ';expires=' + date.toString() + ';path=/funnysql/';
                                document.cookie = setCookie;
                                window.location.replace("http://10.242.8.182/funnysql/");
                            }

						},
						error: function(){
							errorMessage = '连接错误，请检查输入！';
                            if(errorMessage != null) {
                                $('.error').addClass("errorShow");
                                $('.error').text(errorMessage);
                                $('.error').show(2);
                                setTimeout(function(){
                                    $('.error').removeClass("errorShow");
                                    $(".error").hide();
                                    errorMessage = null;
                                }, 3000)
                            }
						}
					});
				if(errorMessage != null) {
					$('.error').addClass("errorShow");
					$('.error').text(errorMessage);
					$('.error').show(2);
					setTimeout(function(){
						$('.error').removeClass("errorShow");
						$(".error").hide();
						errorMessage = null;
					}, 3000)
				}
				
			});
		});
	</script>
</body>
</html>