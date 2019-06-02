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
echo $part1.$GLOBALS["host"].'/'.$GLOBALS["userData"]["avatar"].$part2; 
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
                            <img src="<?php echo $GLOBALS["host"].'/'.$GLOBALS["userData"]["avatar"]; ?>" alt="" style="display: block;margin:auto;max-width:200px;max-height: 100%;padding: 10px;">
                        </div>
                        <div class="col-sm-8">
                            <div class="profile_content" style="margin: 10px;color: white">
                                <h2><?php echo $GLOBALS["userData"]["penname"]; ?></h2>
                                <p  style="color: white;">Description</p>
                                <p style="color: white;">
                                <?php 
                                    if(isset($GLOBALS["userData"]["description"]))
                                        echo $GLOBALS["userData"]["penname"]; 
                                    else
                                        echo "No description";
                                ?></p>
                            </div>

                            <a href="search.php?keyword=<?php echo $GLOBALS["userData"]["penname"]; ?>&searchFrom=1&orderBy=0&tags=*&cates=*">
                                <button type="submit" style="margin-bottom: 20px;" class="btn btn-warning"><i class="fas fa-pen-nib"></i> My Illustrations</button>
                            </a>
                        </div>
                    </div>
      
                </nav>
        </div>
        <nav class="nav nav-pills nav-fill navbar navbar-expand-lg navbar-light bg-light">
          <a class="nav-item nav-link btn" href="./profile.php">Comments</a>
          <a class="nav-item nav-link btn" href="#">Settings</a>
        </nav>

        <!-- settings -->

        <div class="profile_setting" id="profile_setting">
        <div class="container">
            <div class="card" style="box-shadow: 3px 3px 4px 0px rgba(50, 50, 50, .5);" >
                <div class="header" style="margin: 10px;">
                    <h2>poersonal information | Authenication</h2>
                </div>
                <div class="content"  style="margin: 20px;">
                        <div class="form-group" >
                          <label for="penname">Pen-name :</label>
                          <input type="text" class="form-control" id="penname" name="penname"placeholder="Pen-name">
                          
                        </div>

                        <div class="form-group">
                        <label for="description">Descriptions</label>
                                    <textarea rows="6" class="form-control" id="description" rows="3"></textarea>
                          </div>

                         <div class="row">
                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="birthdate">Birth Date</label>
                                    <input type="date" class="form-control" id="birthdate" name="birthdate" placeholder="dd/mm/yyyy">
                                  </div>
                             </div>

                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="gender">Gender</label>
                                    <select class="form-control" name="gender" id="gender">
                                      <option value="">Unknown</option>
                                      <option value="0">Male</option>
                                      <option value="1">Female</option>

                                    </select>
                                  </div>
                             </div>

                         </div> 

                         <div class="form-group">
                            <label for="avatarID">Upload Avatar</label>
                            <input type="file" name="avatar" class="form-control-file" id="avatarID">
                          </div>


                        <div class="form-group">
                            <label for="exampleInputPassword1">Old Password</label>
                            <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Old Password">
                          </div>

                          <div class="form-group">
                            <label for="exampleInputPassword1">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="New Password">
                          </div>

                          <div class="form-group">
                            <label for="exampleInputPassword1">Confirm Password</label>
                            <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Confirm Password">
                          </div>

                        <input type="button" name="send" id="send" class="btn btn-primary" style="display: block;margin:auto;width: 20%;border-radius: 20px;" value="Save">
                      </form>
                </div>
            </div>
        </div>
    </div>

        <!-- End Setting -->
        <!-- End insert -->

        <!-- .content-wrapper -->
    </main>
    <script>
       $(document).ready( () => {
        const token = "<?php echo $GLOBALS["token"]; ?>";
        let file;
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
           
           $("input:file").change(() =>{
             if($("input:file").val().length > 0){
               file =$("input:file")[0].files[0];
             }
           });

           $("#send").click(() => {
               const penname = $("#penname").val(),
                     password = $("#password").val(),
                     gender = $("#gender").val(),
                     birthdate = $("#birthdate").val(),
                     description = $("#description").val();
                
                fd = new FormData();
                if (file !== undefined){
                    fd.append("avatar", file);
                    //window.location.href = "http://locahost:3000/test/setProfile?error=InvalidInput";
                }
                console.log("1");
                  if(penname !== "" || penname !== undefined) fd.append("penname", penname);
                  if(password !== "" || password !== undefined) fd.append("password", password);
                  if(gender !== "" || gender !== undefined) fd.append("gender", gender);
                  if(birthdate !== "" || birthdate !== undefined) fd.append("birthdate", birthdate);
                  if(description !== "" || description !== undefined) fd.append("description", description);
                  $.ajax({
                    url: "<?php echo $GLOBALS["host"]; ?>/users/setProfile",
                    type: "PUT",
                    data: fd,
                  })
                  .done(() =>{
                      window.location.href = "profile.php?status=success";
                  })
                  .fail((data, txtStatus, xhr) => {
                      console.log(data.status);
                      window.location.href = "setting.php?status=fail";
                  });

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