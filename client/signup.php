<?php
    session_start();
    require_once("config/config_rest.php");
    require_once("libs/RESTInterface.php");
    $GLOBALS['host'] = $host;
    $GLOBALS['success_code'] = $success_code;
    $GLOBALS['token'] = null;
    if(isset($_SESSION["token"])){
		[$info, $make_call] = call("POST", $GLOBALS['host']."/users/verify", false, $_SESSION["token"], false);
        if(in_array($info["http_code"], $GLOBALS['success_code'])){
            $response = json_decode($make_call, true);
            if(isset($response["status"])){
				$_SESSION["token"] = null;
				$GLOBALS['token'] = null;
			}
            else{
				header("location: index.php");
				exit(0);
			}
        } else {
            echo $make_call;
            return;
        }
	}
	if(
		isset($_POST['email']) && $_POST['email'] !== "" &&
		isset($_POST['password']) && $_POST['password'] !== "" &&
		isset($_POST['penname']) && $_POST['penname'] !== ""
	  ){
		  $data = json_encode(["email" => $_POST['email'], "password" => $_POST["password"], "penname" => $_POST["penname"]]);
		  [$info, $make_call] = call("POST", $GLOBALS["host"]."/users/register", $data, false, false);
		  if(in_array($info["http_code"], $GLOBALS['success_code'])){
            $response = json_decode($make_call, true);
            if( null !== $response["status"] ){
				if ($response["status"] == "Registered"){
					header("location: signin.php");
					exit(0);
				} else {
					header("location: signup.php?error=".$response["status"]."&".$make_call);
					exit(0);
				}
			}
            else{
				header("location: index.php");
				exit(0);
			}
        } else {
            echo $make_call;
            return;
        }
	  }

?>

<!DOCTYPE html>
<html>
<head>
	<title>Sign Up</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
<!--===============================================================================================-->	
	<link rel="icon" type="image/png" href="images/icons/favicon.ico"/>
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/iconic/css/material-design-iconic-font.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
<!--===============================================================================================-->	
	<link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/animsition/css/animsition.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
<!--===============================================================================================-->	
	<link rel="stylesheet" type="text/css" href="vendor/daterangepicker/daterangepicker.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="css/util.css">
	<link rel="stylesheet" type="text/css" href="css/sign.css">
	<link rel="stylesheet" type="text/css" href="css/fonts.css">
</head>
<body>

	<div class="limiter">
		<div class="container-login100" style="background-image: url('images/bg-01.png');">
			<div class="wrap-login100 p-l-55 p-r-55 p-t-65 p-b-54" style="background: #f78336">
				<form class="login100-form validate-form" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="POST">
					<p class= "logo-1" style="text-align: center;"><a href="index.php"><img src="images/logo.png" width="130px"></a></p>
					<span class="login100-form-title p-b-49">Sign Up</span>

					<div class="wrap-input100 validate-input m-b-23" data-validate = "E-mail is required">
						<span class="label-input100">E-mail Address</span>
						<input class="input100" type="text" name="email" placeholder="E-mail Address">
						<span class="focus-input100" data-symbol="&#xf206;"></span>
					</div>

					<div class="wrap-input100 validate-input m-b-23" data-validate = "Penname is required">
						<span class="label-input100">Penname</span>
						<input class="input100" type="text" name="penname" placeholder="Penname">
						<span class="focus-input100" data-symbol="&#xf206;"></span>
					</div>

					<div class="wrap-input100 validate-input" data-validate="Password is not match">
						<span class="label-input100">Password</span>
						<input class="input100" type="password" name="password" placeholder="Password">
						<span class="focus-input100" data-symbol="&#xf190;"></span>
					</div>
					<br>
					<div class="wrap-input100 validate-input" data-validate="Password is not match">
						<span class="label-input100">Confirm Password</span>
						<input class="input100" type="password" name="confirmPassword" placeholder="Confirm Password">
						<span class="focus-input100" data-symbol="&#xf190;"></span>
					</div>
					<br>
					<div class="container-login100-form-btn">
						<div class="wrap-login100-form-btn">
							<div class="login100-form-bgbtn"></div>
							<button class="login100-form-btn">
								Submit
							</button>
						</div>
					</div>

					<div class="txt3 text-center p-t-54 p-b-20">
						<span>
							Or<br>Sign Up Using
						</span>
					</div>

					<div class="flex-c-m">
						<a href="#" class="login100-social-item bg1">
							<i class="fa fa-facebook"></i>
							<p class="txt4">&nbsp;&nbsp;&nbsp;Facebook</p>
						</a>

						<a href="#" class="login100-social-item bg3">
							<i class="fa fa-google"></i>
							<p class="txt4">&nbsp;&nbsp;&nbsp;Google</p>
						</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</body>
</html>