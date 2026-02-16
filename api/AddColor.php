<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

$inData = getRequestInfo();

$userId = isset($inData["userId"]) ? intval($inData["userId"]) : 0;
$color = isset($inData["color"]) ? trim($inData["color"]) : "";

if ($userId <= 0 || $color === "")
{
	returnWithError("Missing or invalid userId/color");
}

$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
if ($conn->connect_error)
{
	returnWithError("Database connection failed");
}

$stmt = $conn->prepare("INSERT INTO Colors (Name, UserID) VALUES (?, ?)");
if (!$stmt)
{
	$conn->close();
	returnWithError("Database prepare failed");
}

$stmt->bind_param("si", $color, $userId);
if (!$stmt->execute())
{
	$stmt->close();
	$conn->close();
	returnWithError("Insert failed");
}

$newId = $stmt->insert_id;

$stmt->close();
$conn->close();

returnWithInfo($newId);

function getRequestInfo()
{
	$raw = file_get_contents("php://input");
	if ($raw === false || $raw === "")
	{
		return array();
	}
	return json_decode($raw, true);
}

function sendResultInfoAsJson($obj)
{
	echo json_encode($obj);
}

function returnWithError($err)
{
	$retValue = array("id" => 0, "error" => $err);
	sendResultInfoAsJson($retValue);
	exit();
}

function returnWithInfo($id)
{
	$retValue = array("id" => $id, "error" => "");
	sendResultInfoAsJson($retValue);
}
?>
