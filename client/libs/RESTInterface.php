<?php

    function call($method, $url, $data, $auth = false, $headers = false){
        $curl = curl_init();
        switch($method){

            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        if(!$headers){
            if($auth)
                curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    "Authorization: Bearer $auth",
                    'Content-Type: application/json'
                ));
            else
                curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json'
                ));
        }else{
            if($auth)
                curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    "Authorization: Bearer $auth",
                    'Content-Type: application/json',
                    $headers
                ));
            else
                curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    $headers
                ));
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // EXECUTE:
        $result = curl_exec($curl);
        $responseInfo = curl_getinfo($curl);
        if(!$result){die("Connection Failure");}
        curl_close($curl);
        return [$responseInfo, $result];
    }

?>