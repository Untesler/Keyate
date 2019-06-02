<?php
     session_start();
     require_once("config/config_rest.php");
     require_once("libs/RESTInterface.php");
     $token = null;
    if(isset($_GET["illustID"])){
        if(isset($_SESSION["token"])){
            //Authority checking
            [$info, $make_call] = call("POST", $host."/users/verify", false, $_SESSION["token"], false);
            if(in_array($info["http_code"], $GLOBALS['success_code'])){
                $response = json_decode($make_call, true);
                if(isset($response["status"])){
                    $_SESSION["token"] = null;
                    $token = null;
                    header("location: signin.php");
                    exit(0);
                }
                else{
                    $token = $_SESSION["token"];
                }
            } else {
                echo $make_call;
                return;
            }
        } else {
            header("location: signin.php");
            exit(0);
        }
        //do delete
        [$info, $make_call] = call("DELETE", $host."/illusts/user/".$_GET["illustID"]."/", false, $token, false);
        if(in_array($info["http_code"], $GLOBALS['success_code'])){
            header("location: manage.php");
            exit(0);
        }
        else{
            header("location: manage.php?errorCode=".$info["http_code"]);
            exit(0);
        }
    } else {
        header("location: index.php");
        exit(0);
    }
?>
Hold on...