<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

$inData = getRequestInfo();

$userId = isset($inData["userId"]) ? intval($inData["userId"]) : 0;
$search = isset($inData["search"]) ? trim($inData["search"]) : "";

if ($userId <= 0)
{
	returnWithError("Missing or invalid userId");
}

$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
if ($conn->connect_error)
{
	returnWithError("Database connection failed");
}

$stmt = $conn->prepare("SELECT Name FROM Colors WHERE UserID=? AND Name LIKE ? ORDER BY Name");
if (!$stmt)
{
	$conn->close();
	returnWithError("Database prepare failed");
}

$like = "%" . $search . "%";
$stmt->bind_param("is", $userId, $like);
$stmt->execute();
$result = $stmt->get_result();

$colors = array();
if ($result)
{
	while ($row = $result->fetch_assoc())
	{
		$colors[] = $row["Name"];
	}
}

$stmt->close();
$conn->close();

returnWithInfo($colors);

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
	$retValue = array("results" => array(), "error" => $err);
	sendResultInfoAsJson($retValue);
	exit();
}

function returnWithInfo($colors)
{
	$retValue = array("results" => $colors, "error" => "");
	sendResultInfoAsJson($retValue);
}
?>
