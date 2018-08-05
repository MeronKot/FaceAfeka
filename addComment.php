<?php
require("common.php");

$query = "
            INSERT INTO comments (
                id,
                commentName,
				textOfComment
            ) VALUES (
                :id,
                :commentName,
				:textOfComment
            )
        ";

$query_params = array(
		':id' => $_REQUEST['postId'],
		':commentName' => $_REQUEST['commentName'],
		':textOfComment' => $_REQUEST['text']
);

	try
	{
		// Execute the query to create the user
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
	}
	catch(PDOException $ex)
	{
		// Note: On a production website, you should not output $ex->getMessage().
		// It may provide an attacker with helpful information about your code.
		die("Failed to run query: " . $ex->getMessage());
	}


echo 'done!';