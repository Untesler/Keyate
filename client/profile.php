<?php
    session_start();
    require_once("config/config_rest.php");
    require_once("libs/RESTInterface.php");
    $GLOBALS['host'] = $host;
    $GLOBALS['success_code'] = $success_code;
    $GLOBALS['userData'] = null;
    $GLOBALS['token'] = null;
    $GLOBALS['publicProfile'] = null;
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
    if (isset($_GET["uid"]) && $_GET["uid"] != $GLOBALS["userData"]["uid"]){
        [$info, $make_call] = call("GET", $GLOBALS['host']."/users/existance/".$_GET["uid"], false, $_SESSION["token"], false);
        if(in_array($info["http_code"], $GLOBALS['success_code'])){
            $response = json_decode($make_call, true);
            if(isset($response["status"])){
                $GLOBALS['publicProfile'] = null;
                header("location: index.php");
                exit(0);
			}
            else{
				$GLOBALS['publicProfile'] = $response;
			}
        } else {
            echo $make_call;
            return;
        }
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
        .parent{
    width:100%;
    height:100%;
    position:relative;
}
.parent{
    width:100%;
    height:100%;
    position:relative;
}
<?php 
$part1 = ".parent:after{
    content:'';
    background:url('";
$part2 = "');
    width:100%;
    height:100%;
    position:absolute;
    top:0;
    left:0;
    opacity: 0.5;
    filter: blur(8px);
    -webkit-filter: blur(8px);
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
}";
if(isset($GLOBALS['publicProfile'])) echo $part1.$GLOBALS["host"].'/'.$GLOBALS["publicProfile"]["avatar"].$part2; 
else echo $part1.$GLOBALS["host"].'/'.$GLOBALS["userData"]["avatar"].$part2; 
?>

.child{
    position:relative;
    z-index:1;
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

        <!-- put code structure -->
        <div class="content">
      <div class="text-component ">
        <!-- profile -->
        <div class="profile">
        <nav class=" navbar-light bg-dark parent">
                    <div class="row child">
                        <div class="col-sm-4">
                            <img src="
                                <?php
                                    if(isset($GLOBALS['publicProfile'])) echo $GLOBALS["host"].'/'.$GLOBALS['publicProfile']["avatar"];
                                    else echo $GLOBALS["host"].'/'.$GLOBALS["userData"]["avatar"]; 
                                ?>" alt="" style="display: block;margin:auto;max-width:200px;max-height: 100%;padding: 10px;">
                        </div>
                        <div class="col-sm-8">
                            <div class="profile_content" style="margin: 10px;color: white">
                                <h2><?php 
                                    if(isset($GLOBALS['publicProfile'])) echo $GLOBALS['publicProfile']["penname"];
                                    else echo $GLOBALS["userData"]["penname"]; 
                                ?></h2>
                                <p  style="color: white;">Description</p>
                                <p style="color: white;">
                                <?php 
                                if(isset($GLOBALS['publicProfile'])){
                                    if(isset($GLOBALS['publicProfile']["description"]))
                                        echo $GLOBALS['publicProfile']["description"]; 
                                    else
                                        echo "No description";
                                }else{
                                    if(isset($GLOBALS["userData"]["description"]))
                                        echo $GLOBALS["userData"]["description"]; 
                                    else
                                        echo "No description";
                                }
                                    
                                ?></p>
                            </div>

                            <a href="search.php?keyword=<?php 
                                if(isset($GLOBALS['publicProfile'])) echo $GLOBALS['publicProfile']["penname"];
                                else echo $GLOBALS["userData"]["penname"]; 
                            ?>&searchFrom=1&orderBy=0&tags=*&cates=*">
                                <button type="submit" style="margin-bottom: 20px;" class="btn btn-warning"><i class="fas fa-pen-nib"></i> My Illustrations</button>
                            </a>
                        </div>
                    </div>
      
                </nav>
        </div>
        <nav class="nav nav-pills nav-fill navbar navbar-expand-lg navbar-light bg-light">
          <a class="nav-item nav-link btn" href="#">Comments</a>
          <?php 
            if(!isset($GLOBALS['publicProfile'])){
                echo '<a class="nav-item nav-link btn" href="./setting.php">Settings</a>';
            }
          ?>
        </nav>

        <!-- comment box -- >
					<div class="content">
      <div class="text-component ">
        <!-- profile -->
        <div class="profile">
                        <nav class=" navbar-light bg-dark">
                            <div class="header" ;>
                                <h3 style="margin: 0px;padding:10px; color: white;">Comment</h3>
                            </div>
                        </nav>
                    </div>

                    <div class="comment_block">
                        <div class="CommentListBox">
                            <div class="card" style="margin: 20px;display:block;padding: 20px">
                                <div class="row">
                                    <!-- current #{user} avatar -->
                                    <div class="col-sm-3">
                                        <div class="user_avatar">
                                            <img src="images/pic1.png" style="	width: 100%;
                    height: auto!important;
                    max-width: 80px;
                    max-height: 100px;">
                                        </div>
                                    </div>
                                    <!-- the input field -->
                                    <div class="col-sm-9">
                                        <div class="input_comment" style="margin-top:25px;">
                                            <input class="inputField" type="text" placeholder="แสดงความคิดเห็นตรงนี้.." style="width: 70%">
                                            <a href="#" class="btnSend btn-info btn-lg">
                                                <i class="fa fa-paper-plane" aria-hidden="true" style="font-size: 18px; text-align: center;display: inline-block"></i> ส่ง
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- build comment -->
                        <ul class="user_comment">
                            <div class="card" style="margin: 20px;">
                                <div class="header" style="text-align: center;">
                                    <h3>All comemnts</h3>
                                    <hr style="width:90%;">
                                </div>
                                <li>
                                    <div class="clearfix comment" style="margin: 10px;
                      min-height: 65px;
                      font-size: .9rem;
                      color: #555;
                      background-color: rgb(233, 227, 149);
                      border-bottom: 2px solid wheat;">
                                        <!-- current #{user} avatar -->
                                        <div class="pic_avatar">
                                            <img class="pic_avatar" src="images/pic1.png" style="	display: inline-block;
                                 vertical-align: middle;
                                 float: left;
                                 text-align: center;
                                 margin-right: 10px;
                                 height: 100px;
                                 width: max-width;">
                                        </div>
                                        <a class="user_name" href="#" style="text-decoration: none; font-size: 1.1em; font-weight: 15px; background-color: unset; color: #636363; font-family: Kanit-Bold; margin-left: 0px;">
                                 Name
                               </a>

                                        <!-- <button style="float: right;"> -->
                                        <a href="#" class="btn btn-info btn-lg" style="margin:10px;text-decoration: none; cursor: pointer; float: right; background-color: #a0a0a0; padding: 3px; border-radius: 5px; font-size: .7em;">
                                            <span><i class="fas fa-vote-yea" aria-hidden="true"></i></span>โหวต
                                        </a>
                                        <!-- </button> -->

                                        <!-- star rating -->
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <span style="display: inline; margin-right: 5px; font-size: .75em;">Helpful</span>
                                                <div class="star-rating" style="display: inline;	line-height: 25px;font-size: .75em;cursor: pointer;">
                                                    <span data-rating="1"><i class="fas fa-star" data-rating="1"></i></span>
                                                    <span data-rating="2"><i class="fas fa-star"  data-rating="2"></i></span>
                                                    <span data-rating="3"><i class="fas fa-star"data-rating="3"></i></span>
                                                    <span data-rating="4"><i class="fas fa-star" data-rating="4"></i></span>
                                                    <span data-rating="5"><i class="fas fa-star"data-rating="5"></i></span>
                                                    <!-- <input type="hidden" name="whatever1" class="rating-value" value="2.56"> -->
                                                </div>
                                            </div>
                                        </div>
                                        <!-- show comment -->
                                        <div class="comment_content" style="display: inline;vertical-align: middle;text-align: left;width: 100%;">
                                            <span style="font-size: .8em; color: black; font-family: Kanit-SemiBold;">Comment</span>
                                            <br>
                                            <p style="display: block;">ฉันชื่อบุษบา หน่านาน่าน้าหน่านาหน่าน้าน้านานา ชื่อบุษบา หน่านาน่าน้าหน่านาหน่าน้าน้านานา</p>
                                        </div>
                                    </div>
                                </li>

                            </div>
                        </ul>

                        <!-- </div> -->

                    </div>

                </div>
            </div>
            <!-- End Comment box -->

        <!-- End insert -->

        <!-- .content-wrapper -->
    </main>
    <!-- .cd-main-content -->
    <script src="assets/js/util.js"></script>
    <!-- util functions included in the CodyHouse framework -->
    <script src="assets/js/menu-aim.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>