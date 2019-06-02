<?php
    session_start();
    require_once("config/config_rest.php");
    require_once("libs/RESTInterface.php");
    $GLOBALS['host'] = $host;
    $GLOBALS['success_code'] = $success_code;
    $GLOBALS['userData'] = null;
    $GLOBALS['token'] = null;
    
    $GLOBALS['result'] = null;
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
    if(isset($_POST["quickSearch"]) && $_POST["quickSearch"] != ""){
        header("location: search.php?quickSearch=".$_POST["quickSearch"]);
        exit(0);
    }
    if(!isset($_GET["uid"])){
        header("location: gallery.php");
        exit(0);
    }else{
        [$info, $make_call] = call("GET", $GLOBALS['host']."/illusts/".$_GET["uid"], false, $GLOBALS['token'], false);
        if(in_array($info["http_code"], $GLOBALS['success_code'])){
            $response = json_decode($make_call, true);
            if(sizeof($response) > 0)
            $GLOBALS['result'] = $response;
            else
            $GLOBALS['result'] = "Not found";
            $GLOBALS["Avatar"] = $result["illustratorAvatar"];
        }else {
            echo $make_call;
            return;
        }
    }

    function showIllust(){
        if($GLOBALS['result'] != "Not found"){
                $tags = sizeof($GLOBALS['result']['tag']) > 0 ? implode(', ', $GLOBALS['result']['tag']) : "Have no a single tag.";
                $categories = sizeof($GLOBALS['result']['tag']) > 0 ? implode(', ', $GLOBALS['result']['category']) : "Have no category.";
                $description = isset($GLOBALS['result']["description"]) ? $GLOBALS['result']["description"] : "No description.";
                echo '
                <div class="profile">
                    <nav class=" navbar-light bg-dark bg-image parent" style="">
                        <div class="row child">
                            <div class="col-sm-4">
                                <img class="bg-text" src="'.$GLOBALS["host"].'/'.$GLOBALS['result']["illustratorAvatar"].'" alt="" style="display: block;margin:auto;max-width:200px;max-height: 100%;padding: 10px;">
                                <div class="content" style="text-align: center">
                                <a href="profile.php?uid='.$GLOBALS['result']["illustratorId"].'" style="cursor: pointer; padding-bottom: 0px; margin-bottom: 0px;">
                                    <i class="fas fa-user-circle" style="margin-bottom: 10px;color: white">'.$GLOBALS['result']["illustratorPenname"].'</i>
                                </a>
                                </div>
                            </div>
                            <div class="col-sm-8">
                                <div class="profile_content" style="margin: 10px;color: white">
                                    <h2>'.$GLOBALS['result']["name"].'</h2>
                                    <p style="color: white;">Description</p>
                                    <p style="color: white;">'.$description.'</p>
                                </div>
                                    
                                <div>
                                    <span>
                                        <i class="fas fa-tags" style="color: white"></i>
                                        <span style="color: white" >'.$tags.'</span>
                                    </span>
                                </div>
                                <div>
                                    <span>
                                        <i class="fas fa-table" style="color: white"></i>
                                        <span style="color: white" >'.$categories.'</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                    </nav>

                </div>

                <div class="main_save" style="background-color: bisque;height: 100%;">
                    <div class="show_img">
                        <img id="illustImg" src="'.$GLOBALS["host"].'/'.$GLOBALS['result']["path"].'" alt="" style="display: block;margin:auto;max-width:80%;max-height: 100%;padding: 10px;">
                    </div>
                    <div class="row">
                        <div style="  display: block;margin: auto;position: relative;">
                            <span>
                                    <a href="'.$GLOBALS["host"].'/'.$GLOBALS['result']["path"].'" download="'.preg_split('/[\/]+/', $GLOBALS['result']["path"])[2].'" id="saveClick" style="cursor: pointer; padding-bottom: 0px; margin-bottom: 0px;">
                                    <i class="far fa-save" style="color: #4bec7c"></i>
                                    <span id="countFav" style="color: #4bec7c; margin-right: 20px;">Save</span>
                                    </a>
                            </span>

                            <span>
                                    <a style="cursor: pointer; padding-bottom: 0px; margin-bottom: 0px;" id="favClick" >
                                    <i class="fas fa-heart" id="countFav" style="color: #9e0b0f;"></i>
                                    <span id="countView" style=" color: #9e0b0f;">Favorite</span>
                                    </a>
                            </span>

                            </p>
                        </div>
                    </div>
                ';
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
    <title>Show</title>
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
echo $part1.$GLOBALS["host"].'/'.$GLOBALS["Avatar"].$part2; 
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

        <!-- <nav class=" navbar-light bg-dark" style="background-image: url('./images/cover1.jpg'); filter: blur(8px);z-index:-1;"> -->
        <div class="content">
            <div class="text-component ">
                    <?php showIllust() ?>
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
                                            <img src="<?php echo $GLOBALS['host'].'/'.$GLOBALS['userData']['avatar']; ?>" style="	width: 100%;
                    height: auto!important;
                    max-width: 80px;
                    max-height: 100px;">
                                        </div>
                                    </div>
                                    <!-- the input field -->
                                    <div class="col-sm-9">
                                        <div class="input_comment" style="margin-top:25px;">
                                            <input class="inputField" id="inputField" type="text" placeholder="แสดงความคิดเห็นตรงนี้.." style="width: 70%">
                                            <a id="sendBtn" class="btnSend btn-info btn-lg">
                                                <i class="fa fa-paper-plane" aria-hidden="true" style="font-size: 18px; text-align: center;display: inline-block"></i> ส่ง
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- build comment -->
                        <ul class="user_comment" id="user_comment" >
                            <div class="card" style="margin: 20px;">
                                <div class="header" style="text-align: center;">
                                    <h3>All comemnts</h3>
                                    <hr style="width:90%;">
                                </div>
                                    <!-- comment here -->
                            </div>
                        </ul>

                        <!-- </div> -->

                    </div>

                </div>
            </div>
            <!-- End Comment box -->
        </div>

        <!-- put code structure -->

        </div>
        </div>
        <!-- .content-wrapper -->
    </main>
    <script>
       $(document).ready(() => {
            const getURL = "<?php echo $GLOBALS['host']; ?>";
            const getValue = <?php echo $_GET["uid"]; ?>;
            const token = "<?php echo $GLOBALS['token']; ?>";
            let runOnce = true;
            let illustData;
            let commentsListSize = 0;
            let querySize = 0;
            let loadTime = 0;
            if (runOnce) {
                if (token) {
                    $.ajaxSetup({
                        headers: {
                            'Authorization': "Bearer " + token
                        },
                        processData: false,
                        contentType: false,
                        cache: false
                    });
                }
                $.ajax({
                    url: `${getURL}/illusts/view/${getValue}`,
                    method: 'PUT',
                    success: () => {
                        runOnce = false;
                    },
                    error: (data, txtStatus, xhr) => {
                        console.log(data.status);
                        //window.location.href = `http://locahost:3000/showWork/${getValue}`;
                    }
                });
            }

            $("#favClick").click(() => {
                console.log(1);
                if (token) {
                    $.ajax({
                        url: `${getURL}/illusts/popular/${getValue}`,
                        method: 'PUT',
                        success: () => {
                            window.location.href = "bookmarks.php";
                        },
                        error: (data, txtStatus, xhr) => {
                            console.log(data.status);
                        }
                    });
                }
            });

            const loadComment = commentBoxID => {
                if (loadTime === 0) {
                    $.ajax({
                        url: `${getURL}/comments/${commentBoxID}`,
                        method: 'GET',
                        success: (comments) => {
                            let i = 0;
                            for (let comment of comments) {
                                $("#user_comment").append(`
                                <li>
                                    <div class="clearfix comment" style="margin: 10px;
                                        min-height: 65px;
                                        font-size: .9rem;
                                        color: #555;
                                        background-color: rgb(233, 227, 149);
                                        border-bottom: 2px solid wheat;">
                                        <div class="pic_avatar">
                                            <img class="pic_avatar" src="${getURL}/${comment["commentatorAvatar"]}" style="	display: inline-block;
                                                vertical-align: middle;
                                                float: left;
                                                text-align: center;
                                                margin-right: 10px;
                                                height: 100px;
                                                width: max-width;">
                                        </div>
                                        <a class="user_name" href="profile.php?uid=${comment["commentator"]}" style="text-decoration: none; font-size: 1.1em; font-weight: 15px; background-color: unset; color: #636363; font-family: Kanit-Bold; margin-left: 0px;">
                                            ${comment.commentatorPenname}
                                        </a>
                                        <a href="helpful.php?id=${comment["_id"]}" class="btn btn-info btn-lg" style="margin:10px;text-decoration: none; cursor: pointer; float: right; background-color: #a0a0a0; padding: 3px; border-radius: 5px; font-size: .7em;">
                                            <span><i class="fas fa-vote-yea" aria-hidden="true"></i></span>โหวต
                                        </a>

                                        <!-- star rating -->
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <span style="display: inline; margin-right: 5px; font-size: .75em;">Helpful</span>
                                                <div class="star-rating" style="display: inline;	line-height: 25px;font-size: .75em;cursor: pointer;">
                                                    <span data-rating="1"><i class="fas fa-star" data-rating="1"></i> ${comment["helpful"]}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- show comment -->
                                        <div class="comment_content" style="display: inline;vertical-align: middle;text-align: left;width: 100%;" id="${comment["id"]}">
                                            <span style="font-size: .8em; color: black; font-family: Kanit-SemiBold;">Comment</span>
                                            <br>
                                            <p style="display: block;">${comment["comment"]}</p>
                                            <p style="display: block;">Comment Date: ${comment["date"]}</p>
                                        </div>
                                    </div>
                                </li>
                                `);
                                i++;
                            }
                            commentsListSize = i + 1;
                        },
                        error: (data, txtStatus, xhr) => {
                            console.log(data.status);
                        }
                    });
                } else {
                    $.get(`${getURL}/comments/total/${commentBoxID}`, data => {
                        querySize = data.total;
                    })
                    .done(() => {
                        if(querySize > commentsListSize){
                            $.ajax({
                                url: `${getURL}/comments/latest/${commentBoxID}`,
                                method: 'GET',
                                success: (comments) => {
                                    $("#user_comment").append(`
                                <li>
                                    <div class="clearfix comment" style="margin: 10px;
                                        min-height: 65px;
                                        font-size: .9rem;
                                        color: #555;
                                        background-color: rgb(233, 227, 149);
                                        border-bottom: 2px solid wheat;">
                                        <div class="pic_avatar">
                                            <img class="pic_avatar" src="${getURL}/${comment["commentatorAvatar"]}" style="	display: inline-block;
                                                vertical-align: middle;
                                                float: left;
                                                text-align: center;
                                                margin-right: 10px;
                                                height: 100px;
                                                width: max-width;">
                                        </div>
                                        <a class="user_name" href="profile.php?uid=${comment["commentator"]}" style="text-decoration: none; font-size: 1.1em; font-weight: 15px; background-color: unset; color: #636363; font-family: Kanit-Bold; margin-left: 0px;">
                                            ${comment["commentatorPenname"]}
                                        </a>
                                        <a href="helpful.php?id=${comment["_id"]}" class="btn btn-info btn-lg" style="margin:10px;text-decoration: none; cursor: pointer; float: right; background-color: #a0a0a0; padding: 3px; border-radius: 5px; font-size: .7em;">
                                            <span><i class="fas fa-vote-yea" aria-hidden="true"></i></span>โหวต
                                        </a>

                                        <!-- star rating -->
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <span style="display: inline; margin-right: 5px; font-size: .75em;">Helpful</span>
                                                <div class="star-rating" style="display: inline;	line-height: 25px;font-size: .75em;cursor: pointer;">
                                                    <span data-rating="1"><i class="fas fa-star" data-rating="1"></i> ${comment["helpful"]}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- show comment -->
                                        <div class="comment_content" style="display: inline;vertical-align: middle;text-align: left;width: 100%;" id="${comment.id}">
                                            <span style="font-size: .8em; color: black; font-family: Kanit-SemiBold;">Comment</span>
                                            <br>
                                            <p style="display: block;">${comment["comment"]}</p>
                                            <p style="display: block;">Comment Date: ${comment["date"]}</p>
                                        </div>
                                    </div>
                                </li>
                            `);
                                    commentsListSize += 1;
                                },
                                error: (data, txtStatus, xhr) => {
                                    console.log(data.status);
                                }
                            });
                        }
                        }
                    );
                } 
                loadTime++; 
            };

            const addComment = text => {
            
            const commentDate = new Date(Date.now());
                fd = new FormData();
                fd.append("comment", text);
                $.ajax({
                    url: `${getURL}/comments/p<?php echo $_GET["uid"]; ?>`,
                    method: 'PUT',
                    data: fd,
                    headers: {
                            'Authorization': "Bearer " + token
                    },
                    processData: false,
                    contentType: false,
                    success: () => {
                        $("#user_comment").append(`
                                <li>
                                    <div class="clearfix comment" style="margin: 10px;
                                        min-height: 65px;
                                        font-size: .9rem;
                                        color: #555;
                                        background-color: rgb(233, 227, 149);
                                        border-bottom: 2px solid wheat;">
                                        <div class="pic_avatar">
                                            <img class="pic_avatar" src="${getURL}/<?php echo $GLOBALS['userData']['avatar']; ?>" style="	display: inline-block;
                                                vertical-align: middle;
                                                float: left;
                                                text-align: center;
                                                margin-right: 10px;
                                                height: 100px;
                                                width: max-width;">
                                        </div>
                                        <a class="user_name" href="#" style="text-decoration: none; font-size: 1.1em; font-weight: 15px; background-color: unset; color: #636363; font-family: Kanit-Bold; margin-left: 0px;">
                                            <?php echo $GLOBALS["userData"]["penname"]; ?>
                                        </a>
                                        <a href="#" class="btn btn-info btn-lg" style="margin:10px;text-decoration: none; cursor: pointer; float: right; background-color: #a0a0a0; padding: 3px; border-radius: 5px; font-size: .7em;">
                                            <span><i class="fas fa-vote-yea" aria-hidden="true"></i></span>โหวต
                                        </a>

                                        <!-- star rating -->
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <span style="display: inline; margin-right: 5px; font-size: .75em;">Helpful</span>
                                                <div class="star-rating" style="display: inline;	line-height: 25px;font-size: .75em;cursor: pointer;">
                                                    <span data-rating="1"><i class="fas fa-star" data-rating="1"></i> 0</span>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- show comment -->
                                        <div class="comment_content" style="display: inline;vertical-align: middle;text-align: left;width: 100%;" id="">
                                            <span style="font-size: .8em; color: black; font-family: Kanit-SemiBold;">Comment</span>
                                            <br>
                                            <p style="display: block;">${text}</p>
                                            <p style="display: block;">Comment Date: ${commentDate.toDateString()}</p>
                                        </div>
                                    </div>
                                </li>
                                `);
                        commentsListSize += 1;
                    },
                    error: (data, txtStatus, xhr) => {
                        console.log(data.status);
                    }
                });
            };

            $("#inputField").keypress(e => {
                if (e.keyCode === 13) {
                    const sendStr = $("#inputField").val();
                    addComment(sendStr);
                    $("#inputField").val("");
                }
            });

            $("#sendBtn").click(() => {
                const sendStr = $("#inputField").val();
                addComment(sendStr);
                $("#inputField").val("");
            });
            setInterval(function(){
                const commentBoxID = "p<?php echo $_GET["uid"]; ?>"
                loadComment(commentBoxID);
            },2500);
        });
   </script>
    <!-- .cd-main-content -->
    <script src="assets/js/util.js"></script>
    <!-- util functions included in the CodyHouse framework -->
    <script src="assets/js/menu-aim.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>