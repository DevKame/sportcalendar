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
require("../database/db_trainers.php");

/** TRAINERS CAN HAVE DIFFERENT ROLES:
 *  TRAINER
 *          creates students
 *          creates Events
 *          creates groups
 *          creates Trainings
 *          creates Trainers
 *          signs for training an event
 *          editing event info
 *  SENIOR-TRAINER
 *          (all of the above, plus:)
 *          edits students
 *          edits Events
 *          edits groups
 *          edits Trainings
 *          delete trainigns
 *          edits Trainers
 *  ADMIN
 *          (everything)
 */



 //################################################# HANDLES GET REQUESTS:
// RETURNS Boolean INDICATING IF A USER IS LOGGED IN
if($_SERVER["REQUEST_METHOD"] === "GET")
{
    $trainers = getAllTrainers();
    if(is_string($trainers)) {
        $res["reason"] = "connection-problems";
    }
    else {
        $res["success"] = true;
        $res["trainers"] = $trainers;
    }
}
//################################################# HANDLES POST REQUESTS
//################################################# DEPENDANT ON ITS TASK VALUE:
else if($_SERVER["REQUEST_METHOD"] === "POST")
{
    $req = json_decode(file_get_contents("php://input"));
    switch($req->task)
    {
        //################### REMOVES A DELETED GROUP FROM ALL POSSIBLE TRAININGS:
        case "update-event-trainer":
            $allEvents = getTotalEventsWithTrainerID($req->id);
            if(is_string($allEvents))
            {
                $res["reason"] = "connection-problems";
            } else {

                $failedUpdates = 0;
                foreach($allEvents as $event)
                {
                    $resetResult = resetTrainerFromSingleEvent($event["id"]);
                    if(is_string($resetResult))
                    {
                        $failedUpdates++;
                        break;
                    }
                }
                if($failedUpdates === 0)
                {
                    $res["success"] = true;
                } else {
                    $res["reason"] = "connection-problems";
                }
            }
            break;
        //################### FETCHES ALL TRAINERS (ONLY NAME AND ID):
        case "get-name-and-id":
            $trainers = getTrainerNameAndID();
            if(is_string($trainers)) {
                $res["reason"] = "connection-problems";
            }
            else {
                $res["success"] = true;
                $res["trainers"] = $trainers;
            }
            break;
        //################### CHANGES DATA OF A TRAINER:
        case "edit-trainer":
            $affectedRows = editTrainer($req->id, $req->email,$req->firstname, $req->lastname, $req->role, $req->chosengroups);
            if(is_int($affectedRows))
            {
                if($affectedRows === 1)
                {
                    $res["success"] = true;
                } else {
                    if($affectedRows === 0)
                    {
                        $res["reason"] = "no-changes";
                    } else {
                        $res["reason"] = "connection-problems";
                    }
                }
            }
            else {
                $res["reason"] = "connection-problems";
            }
            break;
        //################### VALIDATES DATA USED FOR CHANGING A TRAINER:
        case "validate-trainer-edit":
            // VALIDATION OF EMAIL
            $trimmedEmail = trim($req->email);
            if($trimmedEmail === "")
            {
                $res["reason"] = "invalid-email-value";
                break;
            }
            if(strlen($req->email) > 40)
            {
                $res["reason"] = "email-too-long";
                break;
            }
            if(!filter_var($req->email, FILTER_VALIDATE_EMAIL))
            {
                $res["reason"] = "invalid-email-value";
                break;
            }
            $allEmails = getEmailsExeptOwn($req->email, $req->id);
            if(is_string($allEmails))
            {
                $res["reason"] = "connection-problems";
                break;
            }
            else {
                if($allEmails > 0) {
                    $res["reason"] = "found-double";
                    break;
                }
            }
            // VALIDATION OF FIRSTNAME
            $trimmedFirstname = trim($req->firstname);
            if($trimmedFirstname === "")
            {
                $res["reason"] = "invalid-firstname-value";
                break;
            }
            if(strlen($req->firstname) > 16)
            {
                $res["reason"] = "firstname-too-long";
                break;
            }
            // VALIDATION OF LASTNAME
            $trimmedLastname = trim($req->lastname);
            if($trimmedLastname === "")
            {
                $res["reason"] = "invalid-lastname-value";
                break;
            }
            if(strlen($req->lastname) > 32)
            {
                $res["reason"] = "lastname-too-long";
                break;
            }
            $res["success"] = true;
            break;
        //################### DELETES A TRAINER USING HIS ID:
        case "delete-trainer":
            $result = deleteTrainer($req->id);
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
        //################### CREATES A NEW TRAINER:
        case "create-trainer":
            if(!is_string(createTrainer($req->email, $req->firstname, $req->lastname, $req->role, $req->chosengroups)))
            {
                $res["success"] = true;
            } else {
                $res["reason"] = "connection-problems";
            }
            break;
        //################### VALIDATES DATA FOR CREATING A NEW TRAINER:
        case "validate-trainer":
            // VALIDATION OF EMAIL
            $trimmedEmail = trim($req->email);
            if($trimmedEmail === "")
            {
                $res["reason"] = "invalid-email-value";
                break;
            }
            if(strlen($req->email) > 40)
            {
                $res["reason"] = "email-too-long";
                break;
            }
            if(!filter_var($req->email, FILTER_VALIDATE_EMAIL))
            {
                $res["reason"] = "invalid-email-value";
                break;
            }
            $allEmails = getallEmails($req->email);
            if(is_string($allEmails))
            {
                $res["reason"] = "connection-problems";
                break;
            }
            else {
                if($allEmails > 0) {
                    $res["reason"] = "found-double";
                    break;
                }
            }
            // VALIDATION OF FIRSTNAME
            $trimmedFirstname = trim($req->firstname);
            if($trimmedFirstname === "")
            {
                $res["reason"] = "invalid-firstname-value";
                break;
            }
            if(strlen($req->firstname) > 16)
            {
                $res["reason"] = "firstname-too-long";
                break;
            }
            // VALIDATION OF LASTNAME
            $trimmedLastname = trim($req->lastname);
            if($trimmedLastname === "")
            {
                $res["reason"] = "invalid-lastname-value";
                break;
            }
            if(strlen($req->lastname) > 32)
            {
                $res["reason"] = "lastname-too-long";
                break;
            }
            if($req->role !== "ADMIN" && $req->role !== "TRAINER" && $req->role !== "SENIOR-TRAINER")
            {
                $res["reason"] = "invalid-role";
                break;
            }
            $res["success"] = true;
            break;
    }
}




$res = json_encode($res);
echo $res;