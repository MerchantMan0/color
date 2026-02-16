<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

$inData = getRequestInfo();

$login = isset($inData["login"]) ? trim($inData["login"]) : "";
$password = isset($inData["password"]) ? trim($inData["password"]) : "";

if ($login === "" || $password === "")
{
	returnWithError("Missing login or password");
}

if (!isMd5Hash($password))
{
	$password = md5($password);
}

$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
if ($conn->connect_error)
{
	returnWithError("Database connection failed");
}

$stmt = $conn->prepare("SELECT ID, FirstName, LastName FROM Users WHERE Login=? AND Password=?");
if (!$stmt)
{
	$conn->close();
	returnWithError("Database prepare failed");
}

$stmt->bind_param("ss", $login, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc())
{
	$stmt->close();
	$conn->close();
	returnWithInfo($row["FirstName"], $row["LastName"], $row["ID"]);
}

$stmt->close();
$conn->close();
returnWithError("Invalid credentials");

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
	$retValue = array("id" => 0, "firstName" => "", "lastName" => "", "error" => $err);
	sendResultInfoAsJson($retValue);
	exit();
}

function returnWithInfo($firstName, $lastName, $id)
{
	$retValue = array("id" => $id, "firstName" => $firstName, "lastName" => $lastName, "error" => "");
	sendResultInfoAsJson($retValue);
	exit();
}

function isMd5Hash($value)
{
	return preg_match("/^[a-f0-9]{32}$/i", $value) === 1;
}
?>
