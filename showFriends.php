<?php

require("common.php");

$query = "
        SELECT
            username
        FROM users
    ";


try
{
	// These two statements run the query against your database table.
	$stmt = $db->prepare($query);
	$stmt->execute();
}
catch(PDOException $ex)
{
	// Note: On a production website, you should not output $ex->getMessage().
	// It may provide an attacker with helpful information about your code.
	die("Failed to run query: " . $ex->getMessage());
}

$rows = $stmt->fetchAll();
foreach ($rows as $r){
	$a[] = $r['username'];
}
// get the q parameter from URL
$q = $_REQUEST["q"];

$hint = "";

// lookup all hints from array if $q is different from "" 
if ($q !== "") {
    $q = strtolower($q);
    $len=strlen($q);
    if($q == '*')
    {
    	foreach ($a as $name)
    		$hint .= "$name,";
    }else foreach($a as $name) {
        if (stristr($q, substr($name, 0, $len))) {
        	if ($hint === "") {
                $hint = $name;
            } else {
                $hint .= ", $name";
            }
        }
    }
}

// Output "no suggestion" if no hint was found or output correct values 
echo $hint === "" ? "no suggestion" : $hint;
?>