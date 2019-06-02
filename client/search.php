<?php
    session_start();
    require_once("config/config_rest.php");
    require_once("libs/RESTInterface.php");
    $GLOBALS['host'] = $host;
    $GLOBALS['success_code'] = $success_code;
    $GLOBALS['results'] = null;
    $GLOBALS['userData'] = null;
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
                $GLOBALS['token'] = $_SESSION["token"];
				$GLOBALS['userData'] = $response;
			}
        } else {
            echo $make_call;
            return;
        }
	}
    
    if(
        isset($_GET["tags"]) && isset($_GET["cates"]) &&
        isset($_GET["keyword"]) && $_GET["keyword"] != ""&&
        isset($_GET["searchFrom"]) && $_GET["searchFrom"] != "" &&
        isset($_GET["orderBy"]) && $_GET["orderBy"] != ""
      ){
        [$info, $make_call] = call("GET", $host."/search/".$_GET["keyword"]."/".$_GET["tags"]."/".$_GET["cates"]."/".$_GET["orderBy"]."/".$_GET["searchFrom"], false, false, false);
        if(in_array($info["http_code"], $success_code)){
            $response = json_decode($make_call, true);
            if(sizeof($response) > 0)
                $GLOBALS['results'] = $response;
            else
                $GLOBALS['results'] = "Not found";
        } else {
            echo $host."/search/".$_GET["keyword"]."/".$_GET["tags"]."/".$_GET["cates"]."/".$_GET["orderBy"]."/".$_GET["searchFrom"];
            return;
        }
    }

    if(isset($_GET["quickSearch"]) && $_GET["quickSearch"] != ""){
        [$info, $make_call] = call("GET", $host."/search/keyword/".$_GET["quickSearch"], false, false, false);
        if(in_array($info["http_code"], $success_code)){
            $response = json_decode($make_call, true);
            if(sizeof($response) > 0)
                $GLOBALS['results'] = $response;
            else
                $GLOBALS['results'] = "Not found";
        } else {
            echo $make_call;
            return;
        }
    }

    function searchResult(){
        $switchCol = 0;
        $openHTML1 = '<div class="col-sm-6" style="margin-bottom: 10px;">
                     <div class="search_result_col_1">';
        $openHTML2 = '<div class="col-sm-6" style="margin-bottom: 10px;">
                      <div class="search_result_col_2">';
        $closeHTML = '</div></div>';
        $card1 = '';
        $card2 = '';
        if($GLOBALS['results'] == null) return;
        if($GLOBALS['results'] != "Not found"){
            foreach($GLOBALS['results'] as $result){
                if(!isset($result["description"])) $result["description"] = "No description.";
                $cardData = '
                    <div class="dashboard_element_popular">
                        <div class="card" style="margin-bottom: 10px;position: relative;box-shadow: 3px 3px 4px 0px rgba(50, 50, 50, .5); ">
                            <div class="row">
                                <div class="col-sm-4">
                                <a href="show.php?uid='.$result["uid"].'" ><img src="'.$GLOBALS['host'].'/'.$result["path"].'" alt="" style="position: relative;display:block;margin:auto;max-width: 300px;max-height: 300px;padding: 10px;"></a>
                                </div>
                                <div class="col-sm-8">
                                    <div class="content" style="margin: 20px">
                                        <a href="show.php?uid='.$result["uid"].'" ><h5>'.$result["name"].'</h5></a>
                                        <a href="profile.php" ><p>'.$result["illustratorPenname"].'</p></a>
                                        <p>'.$result["description"].'</p>
                                        <hr style="color: #c9c8d4; border: solid 1.5px;">

                                        <div class="showInlineBlock pull-right" style="font-size: 2vw; text-align: left; margin-bottom: 0px;">
                                            <p class="showInlineBlock height1Line view">
                                                <span>
                                                    <a href="addPop.php?illustID='.$result["uid"].'" style="cursor: pointer; padding-bottom: 0px; margin-bottom: 0px;">
                                                        <i class="fas fa-heart" id="countFav" style="font-size:2vw;color: #9e0b0f;"></i>
                                                    </a>
                                                    <span id="countFav" style="color: #9e0b0f;">&nbsp;'.(int)$result["popularity"].'&nbsp;</span>
                                                </span>

                                                <span>
                                                    <i class="far fa-eye" id="countView" style=" color: #636363;"></i>
                                                    <span id="countView" style=" color: #636363;">&nbsp;'.$result["views"].'&nbsp;</span>
                                                </span>

                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                ';
                if($switchCol%2 == 0)
                    $card1 .= $cardData;
                else
                    $card2 .= $cardData;
                $switchCol++;
            }
            echo $openHTML1;
            echo $card1;
            echo $closeHTML;
            echo $openHTML2;
            echo $card2;
            echo $closeHTML;
        } else {
            echo '<div class="col-sm-12" style="margin-bottom: 10px;"> <h3>Not found.</h3> </div>';
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <title>Search</title>
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
            <input class="reset" type="search" id="quickSearch" name="quickSearch" placeholder="Search...">
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
                    <a class="nav-item nav-link btn" href="./index.php">Home</a>
                    <a class="nav-item nav-link btn" href="./popular.php">Popular</a>
                    <a class="nav-item nav-link btn" href="./ranking.php">Ranking</a>
                    <a class="nav-item nav-link btn" href="#">Search</a>
                </nav>

                <!-- put code structure -->

                <!-- searching bookmark -->
                <div class="page_structure_searching_bookmark">
                <div class="option">
        <h2 style="margin: 20px;" >Options</h2>
        <div class="card"style="margin: 20px;">
            <div class="row" style="margin: 20px;">
                <div class="col-sm-3">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                  <label class="input-group-text" for="searchFrom">Search</label>
                                </div>
                                <select class="custom-select" id="searchFrom" name="searchFrom">
                                  <option selected>Choose...</option>
                                  <option value="0">Work name</option>
                                  <option value="1">Penname</option>
                                </select>
                            </div>
                </div>

                    <div class="col-sm-3">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                <label class="input-group-text" for="orderBy">OrderBy</label>
                                </div>
                                <select class="custom-select" id="orderBy" name="orderBy">
                                <option selected>Choose...</option>
                                <option value="0">Release date</option>
                                <option value="1">Popularity</option>
                                </select>
                            </div>
                </div>

                <div class="col-sm-3">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                            <label class="input-group-text" for="iTag">Tags</label>
                            </div>
                            <input type="text" id="iTag" name="iTag" class="form-control input-lg" placeholder="Tag1, Tag2, ... ( * for select all )">
                        </div>
                 </div>
                <div class="col-sm-3">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                            <label class="input-group-text" for="iCate">Categories</label>
                            </div>
                            <input type="text" id="iCate" name="iCate" class="form-control input-lg" placeholder="Category1, Category2, ... ( * for select all )">
                        </div>
                </div>
            </div>
        </div>
    </div>

                    <div class="content_searching">
                        <div class="card" style="margin: 20px;">
                            <div class="header" style="text-align: center">
                                <h3>Searching Result</h3>
                                <hr style="width: 90%; height: 2px; background-color: black; text-align: center;margin-bottom: 30px;">
                            </div>
                            <div class="search_result" style="margin: 10px;">
                                <div class="row">
                                    <?php searchResult() ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- .content-wrapper -->
    </main>
    <script>
        $(document).ready(() => {
            $('#quickSearch').bind("enterKey",function(e){
                const keyword = $('#quickSearch').val();
                const searchFrom = $('#searchFrom').val();
                const orderBy = $('#orderBy').val();
                const iTag = $('#iTag').val();
                const iCate = $('#iCate').val();
				if( 
					keyword === undefined || keyword === "" || 
					searchFrom === undefined || searchFrom === "" || 
					orderBy === undefined || orderBy === "" ||
					iTag === undefined || iTag === "" ||
					iCate === undefined || iCate === ""
				)
				{window.location.href = `${window.location.pathname}?quickSearch=${keyword}`;}
				else{window.location.href = `${window.location.pathname}?keyword=${keyword}&searchFrom=${searchFrom}&orderBy=${orderBy}&tags=${iTag}&cates=${iCate}`;}
            });
            $('#quickSearch').keyup(function(e){
            if(e.keyCode == 13)
            {
                $(this).trigger("enterKey");
            }
            });
        })
    </script>
    <!-- .cd-main-content -->
    <script src="assets/js/util.js"></script>
    <!-- util functions included in the CodyHouse framework -->
    <script src="assets/js/menu-aim.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>