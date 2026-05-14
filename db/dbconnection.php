<?php
/*
function execsqlSRS($sql,$oper,$arr){
$dbc=dbconES();


	if ($oper == "Insert" or $oper=="Update" or $oper=="Delete"){

		#echo $sql;
		$stmt = $dbc->prepare($sql);
			$stmt->execute($arr);

	}else{
		if(!$dbc == 0){
			$stmt = $dbc->prepare($sql);
			$stmt->execute($arr);
			$row = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$dbc = null;

			return $row;
		}else{
			return "No DB";
		}
	}
}

function dbconES(){
	include "../config/config.php";
	try {

	$dbh = new PDO("sqlsrv:Server={$srsServer};Database={$srsDB}", "", "");
	} catch (Exception $e) {
	$dbh->rollBack();
	echo "Failed: " . $e->getMessage();
	$dbh=0;
	}

	$dbh->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_SYSTEM);
	return $dbh;
}
*/

function execsqlSRS($sql, $oper, $arr)
{
	$dbc = dbconES();


	if ($oper == "Insert" or $oper == "Update" or $oper == "Delete") {

		#echo $sql;
		$stmt = $dbc->prepare($sql);
		$stmt->execute($arr);
	} else {
		if (!$dbc == 0) {
			$stmt = $dbc->prepare($sql);
			$stmt->execute($arr);
			$row = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$dbc = null;

			return $row;
		} else {
			return "No DB";
		}
	}
}

function dbconES()
{
	include "../config/config.php";

	try {

		$dbh = new PDO(
			"sqlsrv:Server={$srsServer};Database={$srsDB}",
			$srsUser,
			$srsPass
		);

		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$dbh->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_SYSTEM);
	} catch (PDOException $e) {

		echo "Failed: " . $e->getMessage();
		$dbh = null;
	}

	return $dbh;
}
