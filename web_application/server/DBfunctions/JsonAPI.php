<?php
require("../DB/Db.class.php");
$db = new Db();


$functionName    =   $_GET["functionName"];


//get all locations for dropdown list
function getLocationList(){
    global $db;
    $db->beginTransaction();
    // SELECT `eloc_data`.`location` AS ValeID , `eloc_data`.`location` AS LableID , lat AS Lat, lon AS Lon FROM `eloc_data` INNER JOIN `location` ON `eloc_data`.`location` = `location`.`location` WHERE 1 GROUP BY `eloc_data`.`location`
    // $locations = $db->query("SELECT location AS ValeID , location AS LableID FROM eloc_data GROUP BY location");
    $locations = $db->query("SELECT `eloc_data`.`location` AS ValeID , `eloc_data`.`location` AS LableID , lat AS Lat, lon AS Lon FROM `eloc_data` INNER JOIN `location` ON `eloc_data`.`location` = `location`.`location` WHERE 1 GROUP BY `eloc_data`.`location`");
    $db->commit();
    return json_encode($locations);
}


function getLocationTimestamps(){
    global $db;
    $location    =   $_GET["location"];
    $db->beginTransaction();
    $Timestamps = $db->query("SELECT time_stamp AS ValeID, time_stamp AS LableID  FROM eloc_data WHERE location = :location ",array("location" => $location));
    $db->commit();
    return json_encode($Timestamps);
}


function getAudio(){
    global $db;
    $location    =   $_GET["location"];
    $time_stamp    =   $_GET["time_stamp"];
    $db->beginTransaction();
    $filePath = $db->query("SELECT data_file,angle FROM eloc_data WHERE location = :location AND time_stamp = :time_stamp ",array("location" => $location,"time_stamp" => $time_stamp));
    $db->commit();
    return json_encode($filePath);
}



switch ($functionName) {
    case "getLocationList":
        echo getLocationList();
        break;
    case "getLocationTimestamps":
        echo getLocationTimestamps();
        break;
    case "getAudio":
        echo getAudio();
        break;
}
