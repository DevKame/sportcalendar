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
require("../database/db_students.php");

//################################################# HANDLES GET REQUESTS:
// RETURNS Boolean INDICATING IF A USER IS LOGGED IN
if($_SERVER["REQUEST_METHOD"] === "GET")
{
    $students = getAllStudents();
    if(is_string($students)) {
        $res["reason"] = "connection-problems";
    }
    else {
        $res["success"] = true;
        $res["students"] = $students;
    }
}
//################################################# HANDLES POST REQUESTS
//################################################# DEPENDANT ON ITS TASK VALUE:
else if($_SERVER["REQUEST_METHOD"] === "POST")
{
    $req = json_decode(file_get_contents("php://input"));
    switch($req->task)
    {
        //################### REMOVES A DELETED STUDENT FROM ALL POSSIBLE EVENTS:
        case "update-event-students":
            $allEvents = getAllEventStudents();
            if(is_string($allEvents))
            {
                $res["reason"] = "connection-problems";
                break;
            } else {
                // SELECT KEIN FEHLER
                $failedUpdates = 0;
                foreach($allEvents as $event)
                {
                    $students = json_decode($event["students"]);
                    $idx = array_search($req->sid, $students);
                    if(is_int($idx))
                    {
                        //STUDENT ID WAS FOUND IN STUDENTS
                        unset($students[$idx]);
                        $students = array_values($students);
                        $newbooked = count($students);
                        $newJSONStudentString = json_encode($students);
                        $updateResult = updateEventStudents($event["id"], $newJSONStudentString, $newbooked);
                        if(!is_bool($updateResult))
                        {
                            // UPDATING THREW EXEPTION
                            $failedUpdates++;
                            break;
                        }
                        else {
                            // UPDATE DIDNT THREW EXEPTION
                            if(!$updateResult)
                            {
                                // UPDATE WASNT SUCCESSFULL
                                $failedUpdates++;
                                break;
                            }
                        }
                        
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
        //################### FETCHES ONLY ALL STUDENTS (ONLY NAME AND ID):
        case "get-name-and-id":
            $students = getStudentNameAndID();
            if(is_string($students)) {
                $res["reason"] = "connection-problems";
            }
            else {
                $res["success"] = true;
                $res["students"] = $students;
            }
            break;
        //################### CHANGES DATA OF A STUDENT:
        case "edit-student":
            $affectedRows = editStudent($req->id, $req->email,$req->firstname, $req->lastname, $req->chosengroups);
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
        //################### VALIDATES DATA USED FOR CAHNGING A STUDENT:
        case "validate-student-edit":
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
            // VALIDATION OF GROUPS
            if(strlen($req->chosengroups) > 256)
            {
                $res["reason"] = "groups-too-long";
                break;
            }
            $res["success"] = true;
            break;
        //################### DELETES A STUDENT BASED ON ITS ID:
        case "delete-student":
            $result = deleteStudent($req->id);
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
        //################### CREATES A NEW STUDENT:
        case "create-student":
            if(!is_string(createStudent($req->email,$req->firstname,$req->lastname,$req->chosengroups)))
            {
                $res["success"] = true;
            } else {
                $res["reason"] = "connection-problems";
            }
            break;
        //################### VALIDATES DATA FOR CREATING A NEW STUDENT:
        case "validate-student":
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