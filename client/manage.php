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
	} else {
        header("location: signin.php");
        exit(0);
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
                    <a class="nav-item nav-link btn" href="#">Submit & Manage</a>
                    <a class="nav-item nav-link btn" href="./bookmarks.php">Bookmarks</a>
                </nav>

                <!-- put code structure -->
                <div class="content">
                    <div class="text-component ">
                        <!-- mange work -->
                        <div class="manage_work" style="background-color: aliceblue;height:100vh">
                            <nav class="navbar bg-dark" style="color: white">
                                <h3>Manage Work</h3>
                            </nav>

                            <div class="gallery_manage_woek_list table" style="margin-top: 30px;">
                                <div class="container">
                                    <div class=" table-responsive">
                                        <table class="table ">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Work name</th>
                                                    <th scope="col">Date</th>
                                                    <th scope="col">Viwes</th>
                                                    <th scope="col">Favorited</th>
                                                    <th scope="col">Delete</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    [$info, $make_call] = call("GET", $GLOBALS['host']."/search/user/".$GLOBALS["userData"]["uid"]."/illusts", false, $_SESSION["token"], false);
                                                    if(in_array($info["http_code"], $GLOBALS['success_code'])){
                                                        $response = json_decode($make_call, true);
                                                        foreach($response as $illust){
                                                            echo '
                                                            <tr>
                                                                <td><a href="show.php?uid='.$illust["uid"].'">'.$illust["name"].'</a></td>
                                                                <td>'.$illust["release_date"].'</td>
                                                                <td>'.$illust["views"].'</td>
                                                                <td>'.$illust["popularity"].'</td>
                                                                <td><a href="del.php?illustID='.$illust["uid"].'"><i class="fas fa-trash-alt fa-2x"></i></a></td>
                                                            </tr>
                                                            ';
                                                        }
                                                        
                                                    } else {
                                                        echo $make_call;
                                                        return;
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="btn_add" style="display: block;margin:auto;text-align: center;">
                                        <a href="./submit.php"><i class="fas fa-plus-square fa-3x"></i></a>
                                        <p>add a new</p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- .content-wrapper -->
                <!-- End insert -->

            </div>
        </div>
        <!-- .content-wrapper -->
    </main>
    <!-- .cd-main-content -->
    <script src="assets/js/util.js"></script>
    <!-- util functions included in the CodyHouse framework -->
    <script src="assets/js/menu-aim.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>