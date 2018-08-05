<?php
require("common.php");
$names = '';

$output = '';

if(is_array($_FILES))
{
	foreach ($_FILES['files']['name'] as $name => $value)
	{
		$file_name = explode(".", $_FILES['files']['name'][$name]);
		$allowed_ext = array("jpg", "jpeg", "png", "gif");
		if(in_array($file_name[1], $allowed_ext))
		{
			$new_name = md5(rand()) . '.' . $file_name[1];
			$names .= $new_name . '@';
			$sourcePath = $_FILES['files']['tmp_name'][$name];
			$targetPath = "upload/".$new_name;
			if(move_uploaded_file($sourcePath, $targetPath))
			{
				$output .= '<img src="'.$targetPath.'" width="150px" height="180px" />';
			}
		}
	}
	//createThumbs("upload/", "thumbs/", 100);
	echo $output;
}

if(!empty($_REQUEST))
{
	
	// Ensure that the user has entered a non-empty username
	if(empty($_REQUEST['msg']))
	{
		// Note that die() is generally a terrible way of handling user errors
		// like this.  It is much better to display the error with the form
		// and allow the user to correct their mistake.  However, that is an
		// exercise for you to implement yourself.
		die("Please enter your post.");
	}
	
	$query = "
            INSERT INTO posts (
                username,
                post,
				images,
				isPrivate
            ) VALUES (
                :username,
                :post,
				:images,
				:isPrivate
            )
        ";
	
	$isPrivate = 0;
	if($_REQUEST['private'] == 'on')
		$isPrivate = 1;

	$tags = '';
	if(!empty($_REQUEST["friendsList"]))
			$tags = " WITH " . $_REQUEST["friendsList"];
	
	$query_params = array(
			':username' => $_REQUEST['username'],
			':post' => $_REQUEST['msg'] . $tags,
			':images' => $names,
			'isPrivate' => $isPrivate
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
}


function createThumbs( $pathToImages, $pathToThumbs, $thumbWidth ) {
	// open the directory
	$dir = opendir( $pathToImages );
	// loop through it, looking for any/all JPG files:
	while (false !== ($fname = readdir( $dir ))) {
		// parse path for the extension
		$info = pathinfo($pathToImages . $fname);
		// continue only if this is a JPEG image
		if ( strtolower($info['extension']) == 'jpg' ) {
			// load image and get image size
			$img = imagecreatefromjpeg( "{$pathToImages}{$fname}" );
			$width = imagesx( $img );
			$height = imagesy( $img );
			// calculate thumbnail size
			$new_width = $thumbWidth;
			$new_height = floor( $height * ( $thumbWidth / $width ) );
			// create a new tempopary image
			$tmp_img = imagecreatetruecolor( $new_width, $new_height );
			// copy and resize old image into new image
			imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
			// save thumbnail into a file
			imagejpeg( $tmp_img, "{$pathToThumbs}{$fname}" );
		}
	}
	closedir( $dir );
}

function createGallery($pathToImages, $pathToThumbs) {
	$output .= "<table cellspacing=\"0\" cellpadding=\"2\" width=\"500\"><tr>";
	// open the directory
	$dir = opendir ( $pathToThumbs );
	$counter = 0;
	// loop through the directory
	while ( false !== ($fname = readdir ( $dir )) ) {
		// strip the . and .. entries out
		if ($fname != '.' && $fname != '..') {
			$output .= "<td valign=\"middle\" align=\"center\"><a href=\"{$pathToImages}{$fname}\">";
			$output .= "<img src=\"{$pathToThumbs}{$fname}\" border=\"0\" />";
			$output .= "</a></td>";
			$counter += 1;
			if ($counter % 4 == 0) {
				$output .= "</tr><tr>";
			}
		}
	}
	// close the directory
	closedir ( $dir );
	$output .= "</tr></table>";
	return $output;
}

?>