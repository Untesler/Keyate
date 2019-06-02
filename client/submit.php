<?php
    session_start();
    require_once("config/config_rest.php");
    require_once("libs/RESTInterface.php");
    $GLOBALS['host'] = $host;
    $GLOBALS['success_code'] = $success_code;
    $GLOBALS['userData'] = null;
    $GLOBALS['token'] = null;
    if(isset($_SESSION["token"])){
		[$info, $make_call] = call("POST", $GLOBALS['host']."/users/verify", false, $_SESSION["token"], false);
        if(in_array($info["http_code"], $GLOBALS['success_code'])){
            $response = json_decode($make_call, true);
            if(isset($response["status"])){
				$_SESSION["token"] = null;
                $GLOBALS['token'] = null;
                header("location: signin.php");
                exit(0);
			}
            else{
                $GLOBALS['token'] = $_SESSION["token"];
				$GLOBALS['userData'] = $response;
			}
        } else {
            echo $make_call;
            return;
        }
	}
    if(isset($_POST["quickSearch"]) && $_POST["quickSearch"] != ""){
        header("location: search.php?quickSearch=".$_POST["quickSearch"]);
        exit(0);
    }
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        document.getElementsByTagName("html")[0].className += " js";
    </script>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="css/fonts.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <title>Home</title>
    <style>
        a {
            font-size: 16px;
            font-family: Kanit-Bold;
            color: #000000;
            margin: 0px;
            transition: all 0.4s;
            -webkit-transition: all 0.4s;
            -o-transition: all 0.4s;
            -moz-transition: all 0.4s;
            text-decoration: none;
        }
        .form-check ,.form-check-inline{
    margin: auto;
    display: block;
}
    </style>
</head>

<body style="font-family: Kanit-Bold;">
<header class="cd-main-header js-cd-main-header" style="background-color: #ff9800ad;box-shadow: 3px 3px 4px 0px rgba(50, 50, 50, .5);">
        <div class="cd-logo-wrapper">
            <h2>Keyate</h2>
        </div>

        <div class="cd-search js-cd-search">
            <!-- require php -->
            <form action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="POST">
                <input class="reset" type="search" name="quickSearch" placeholder="Search...">
            </form>
            <!-- require php -->
        </div>

        <button class="reset cd-nav-trigger js-cd-nav-trigger" aria-label="Toggle menu"><span></span></button>

        <!-- this section will hide ultil user logged in -->
        <?php
        if($GLOBALS['token'] != null){
            echo '
            <ul class="cd-nav__list js-cd-nav__list">
            <li class="cd-nav__item cd-nav__item--has-children cd-nav__item--account js-cd-item--has-children">
                <a href="#0" style="    margin-top: 16px;">
                    <img src="'.$GLOBALS["host"]."/".$GLOBALS['userData']['avatar'].'" alt="avatar">
                    <span>'.$GLOBALS['userData']['penname'].'</span>
                </a>
                <ul class="cd-nav__sub-list">
                    <li class="cd-nav__sub-item"><a href="profile.php">My Profile</a></li>
                    <li class="cd-nav__sub-item"><a href="setting.php?uid='.$GLOBALS['userData']['uid'].'">Edit Account</a></li>
                    <li class="cd-nav__sub-item"><a href="signout.php">Logout</a></li>
                </ul>
            </li>
        </ul>
            ';
        } else {
            echo '
            <ul class="cd-nav__list js-cd-nav__list">
            <li class="cd-nav__item cd-nav__item--has-children cd-nav__item--account js-cd-item--has-children">
                <a href="#0" style="    margin-top: 16px;">
                    <img src="assets/img/cd-avatar.svg" alt="avatar">
                    <span>Account</span>
                </a>
                <ul class="cd-nav__sub-list">
                    <li class="cd-nav__sub-item"><a href="signin.php">SignIn</a></li>
                    <li class="cd-nav__sub-item"><a href="signup.php">Register</a></li>

                </ul>
            </li>
        </ul>
            ';
        }
        ?>
        <!-- End hidden section -->

    </header>
    <!-- .cd-main-header -->

    <main class="cd-main-content">
        <nav class="cd-side-nav js-cd-side-nav" style=" background-color: #ff9800ad;box-shadow: 3px 3px 4px 0px rgba(50, 50, 50, .5);">
            <ul class="cd-side__list js-cd-side__list">
            <a href="./index.php"><li class="cd-side__label"><span><center><img src="./images/logo.png" alt="Keyate"></center></span></li></a>
                <li class="cd-side__item cd-side__item--has-children cd-side__item--overview js-cd-item--has-children">
                    <a href="./index.php">Home</a>
                </li>

                <li class="cd-side__item cd-side__item--has-children cd-side__item--overview js-cd-item--has-children">
                    <a href="./gallery.php">Gallery</a>
                </li>

                <!-- this section will hide ultil user logged in -->
                <?php
        if($GLOBALS['token'] != null){
            echo '
            <li class="cd-side__item cd-side__item--has-children cd-side__item--notifications cd-side__item--selected js-cd-item--has-children">
            <a href="./profile.php">Profile</a>
            <ul class="cd-side__sub-list">
                <li class="cd-side__sub-item"><a href="manage.php?uid='.$GLOBALS['userData']['uid'].'">Manage work</a></li>
                <li class="cd-side__sub-item"><a href="setting.php?uid='.$GLOBALS['userData']['uid'].'">Edit Profile</a></li>
                <li class="cd-side__sub-item"><a href="signout.php">Logout</a></li>
            </ul>
        </li>
            <ul class="cd-side__sub-list">
                <li class="cd-side__sub-item"><a href="manage.php?uid='.$GLOBALS['userData']['penname'].'">Manage work</a></li>
                <li class="cd-side__sub-item"><a href="setting.php>uid='.$GLOBALS['userData']['penname'].'">Edit Profile</a></li>
                <li class="cd-side__sub-item"><a href="signout.php">Logout</a></li>
            </ul>
        </li>
            ';
        }
        ?>
                <!-- end hidden section -->

            </ul>

        </nav>

        <div class="content">
            <div class="text-component text--center">
                <nav class="nav nav-pills nav-fill navbar navbar-expand-lg navbar-light bg-light">
                    <a class="nav-item nav-link btn" href="./gallery.php">Gallery</a>
                    <a class="nav-item nav-link btn" href="./manage.php">Submit & Manage</a>
                    <a class="nav-item nav-link btn" href="./bookmarks.php">Bookmarks</a>
                </nav>

                <!-- put code structure -->

                <div class="content">
      <div class="text-component ">
        <!-- profile -->
        <div class="profile">
                <nav class="navbar-light bg-dark">
                    <h1 style="text-align: center;margin: 0px;padding: 30px; color: white;">Submit Illustration</h1>
                </nav>
            
        </div>

        <div class="main_save" style="margin-top:100px;">
            <div class="show_img" >
                <div class="card" style="margin: 20px;height: 300px;max-height:300px;">
                    <div class="btn_upload" style="display: block;margin:auto;">
                            <form action="" method="post" enctype="multipart/form-data" name="submitWork" id="submitWork">
                                    <div class="form-group">
                                      <label for="work">file input</label>
                                      <input type="file" name="work" id="work">
                                    </div>
                            </form>
                    </div>
                </div>
            </div>

            <div class="comment_box" style="height: 100%;margin:10px; ">
                <div class="container">
                                <div class="row">
										<div class="col-md-12">
											<div class="form-check form-check-inline">
												<input class="form-check-input form-control input-lg" type="text" name="nanme" id="name" value="" placeholder="Illustration name">
											</div>
										</div>
                                </div>
								<br>
                            <div class="form-group">
                                    <label for="description">Descriptions</label>
                                    <textarea rows="6" class="form-control" id="description" rows="3"></textarea>
                                </div>

                                <div class="row">
										<div class="col-md-2">
											<label for="tag"> Tag :</label>
										</div>
										<div class="col-md-10">
											<div class="form-check form-check-inline">
												<input class="form-check-input form-control input-lg" type="text" name="tag" id="tag" value="" placeholder="Tag1, Tag2, Tag3, ...">
											</div>
										</div>
                                </div>
								<br>
                                <div class="row">
									<div class="col-md-2">
                                        <label for="category"> Category :</label>
									</div>
									<div class="col-md-10">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input form-control input-lg" type="text" name="category" id="category" value="" placeholder="Category1, Category2, Category3, ...">
                                        </div>
									</div>
                                </div>
								<br>
                                <div class="row">
									<div class="col-12">
                                        <input type="submit" id="submitBtn" name="Submit" value="Submit" class="btn btn-outline-primary btn-block">
									</div>
                                </div>
                </div>
            </div>             
        </div>
                
                <!-- End insert -->

            </div>
        </div>
        <!-- .content-wrapper -->
    </main>
    <script>
       $(document).ready( () => {
           const token = "<?php echo $GLOBALS["token"]; ?>";
           if(token){
                $.ajaxSetup({
                    headers: {
                        'Authorization': "Bearer " + token
                    },
                    processData: false,
                    contentType: false,
                    cache: false
                });
               let userData = false;
                $.post("<?php echo $GLOBALS["host"]; ?>/users/verify", (data) => {
                    userData = data;
                })
                .done(() => {
                    if(!userData){
                        localStorage.removeItem("token");
                        window.location.href = "index.php";
                    }
                })
                .fail((data, txtStatus, xhr) => {
                    console.log(data.status);
                    window.location.href = "index.php";
                });
           }else{
                window.location.href = "signin.php";
           }
           
           $("#submitBtn").on("click", (e) => {
               const wName = $("#name").val(),
                     wFile = $("#work").get(0).files[0],
                     wTag = $("#tag").val(),
                     wCategory = $("#category").val(),
                     wDescription = $("#description").val();
                
                if (wName === "" || wName === undefined || wFile === undefined){
                    window.location.href = "submit.php";
                } else {
                    fd = new FormData();
                    fd.append("name", wName);
                    fd.append("work", wFile);
                    if(wTag !== "" || wTag !== undefined) fd.append("tag", wTag);
                    if(wCategory !== "" || wCategory !== undefined) fd.append("category", wCategory);
                    if(wDescription !== "" || wDescription !== undefined) fd.append("description", wDescription);
                    $.post("<?php echo $GLOBALS["host"]; ?>/illusts/user", fd)
                    .done(() =>{
                        window.location.href = "manage.php?status=success";
                    })
                    .fail((data, txtStatus, xhr) => {
                        console.log(data.status);
                        window.location.href = `submit.php?status=fail?code=${data.status}`;
                    });
                }
           })
       });
   </script>
    <!-- .cd-main-content -->
    <script src="assets/js/util.js"></script>
    <!-- util functions included in the CodyHouse framework -->
    <script src="assets/js/menu-aim.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>