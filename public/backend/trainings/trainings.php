<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_name("KDNAPCKIE-sport");
session_start();

$res = [];
$res["success"] = false;
//################################################# GENERAL HEADERS:
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");


//################################################# GENERAL HEADERS:
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

require("../database/general.php");
require("../database/db_trainings.php");

 //################################################# HANDLES GET REQUESTS:
// RETURNS ALL TRAININGS
if($_SERVER["REQUEST_METHOD"] === "GET")
{
    $trainings = getAllTrainings();
    if(is_string($trainings)) {
        $res["reason"] = "connection-problems";
    }
    else {
        $res["success"] = true;
        $res["trainings"] = $trainings;
    }
}
//################################################# HANDLES POST REQUESTS
//################################################# DEPENDANT ON ITS TASK VALUE:
else if($_SERVER["REQUEST_METHOD"] === "POST")
{
    $req = json_decode(file_get_contents("php://input"));
    switch($req->task)
    {
        //################### CHANGES DATA OF A TRAINING:
        case "edit-training":
            $affectedRows = editTraining($req->id, $req->name, $req->chosengroups);
            if(is_int($affectedRows))
            {
                $res["success"] = true;
            }
            else {
                $res["reason"] = "connection-problems";
                $res["at"] = "affectedRows is not an int (so Exeptionw as thrown in db.php)";
            }
            break;
        //################### VALIDATES DATA USED FOR CHANGING A TRAINER:
        case "validate-training-edit":
            // VALIDATION OF NAME
            $trimmedName = trim($req->name);
            if($trimmedName === "" || strlen($trimmedName) > 24)
            {
                $res["reason"] = "invalid-name";
                break;
            }
            // VALIDATION OF GROUPS
            if(strlen($req->chosengroups) > 256)
            {
                $res["reason"] = "groups-too-long";
                break;
            }
            $allTrainingNames = getTrainingNameExeptOwn($req->name, $req->id);
            if(is_string($allTrainingNames))
            {
                $res["reason"] = "connection-problems";
                break;
            }
            else {
                if($allTrainingNames > 0) {
                    $res["reason"] = "found-double";
                    break;
                }
            }
            $res["success"] = true;
            break;
        //################### DELETES A TRAINING USING ITS ID:
        case "delete-training":
            $result = deleteTraining($req->id);
            if(is_bool($result)) {
                if($result) {
                    $res["success"] = true;
                } else {
                    $res["reason"] = "connection-problems";
                }
            } else {
                $res["reason"] = "connection-problems";
            }
            break;
        //################### CREATES A NEW TRAINING:
        case "create-training":
            if(!is_string(createTraining($req->name, $req->chosengroups)))
            {
                $res["success"] = true;
            } else {
                $res["reason"] = "connection-problems";
            }
            break;
        //################### VALIDATES DATA FOR CREATING A NEW TRAINING:
        case "validate-training":
            // VALIDATION OF DOUBLE NAME
            $allTrainingNames = getallTrainingNames($req->name);
            if(is_string($allTrainingNames))
            {
                $res["reason"] = "connection-problems";
                break;
            }
            else {
                if($allTrainingNames > 0) {
                    $res["reason"] = "found-double";
                    break;
                }
            }
            // VALIDATION OF NAME
            $trimmedName = trim($req->name);
            if($trimmedName === "" || strlen($trimmedName) > 24)
            {
                $res["reason"] = "invalid-name";
                break;
            }
            // VALIDATION OF GROUPS
            if(strlen($req->chosengroups) > 256)
            {
                $res["reason"] = "groups-too-long";
                break;
            }
            $res["success"] = true;
            break;
    }
}




$res = json_encode($res);
echo $res;