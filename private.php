<?php

// First we execute our common code to connection to the database and start the session
require ("common.php");

// At the top of the page we check to see whether the user is logged in or not
if (empty ( $_SESSION ['user'] )) {
	// If they are not, we redirect them to the login page.
	header ( "Location: login.php" );
	
	// Remember that this die statement is absolutely critical. Without it,
	// people can view your members-only content without logging in.
	die ( "Redirecting to login.php" );
}

$query = "
        SELECT
			id,
			username,
            post,
			time,
			images,
			isPrivate
		FROM posts ORDER BY time DESC LIMIT 8
    ";

$commentQuery = "
		SELECT
			time,
			id,
			commentName,
			textOfComment
		FROM comments 
		";

try{
	// These two statements run the query against your database table.
	$stmt = $db->prepare ( $query );
	$stmt->execute ();
} catch ( PDOException $ex ) {
	// Note: On a production website, you should not output $ex->getMessage().
	// It may provide an attacker with helpful information about your code.
	die ( "Failed to run query: " . $ex->getMessage () );
}

$rows = $stmt->fetchAll ();

try {
	// These two statements run the query against your database table.
	$stmt = $db->prepare ( $commentQuery );
	$stmt->execute ();
} catch ( PDOException $ex ) {
	// Note: On a production website, you should not output $ex->getMessage().
	// It may provide an attacker with helpful information about your code.
	die ( "Failed to run query: " . $ex->getMessage () );
}

$taggedFriends = array ();
$comments = $stmt->fetchAll ();
?>

<!DOCTYPE html>
<html>
<head>
<script
	src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
</head>

<script type="text/javascript">

function fillComboBox(jason){
	var option = '';
	var suggestions = jason.split(',');

	for(var i = 0; i < suggestions.length; i++)
		option += '<option value="'+suggestions[i]+'" />';
	return option;
}

function showFriends(str) {
    if (str.length == 0) { 
        document.getElementById("browsers").innerHTML = "";
        return;
    } else {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                //document.getElementById("txtHint").innerHTML = this.responseText;
                document.getElementById("browsers").innerHTML = fillComboBox(this.responseText); 
            }
        };
        xmlhttp.open("GET", "showFriends.php?q=" + str, true);
        xmlhttp.send();
    }
}

function addFriend(tag){
	if(tag != ''){
		var str = $("input[type=hidden][name=friendsList]").val();
		str += tag + ',';
		$("input[type=hidden][name=friendsList]").val(str);
	}
}

$(document).ready(function(){  
      $('#uploadForm').on('submit', function(e){  
          e.preventDefault();  
           $.ajax({  
                url: "upload.php",  
                type: "POST",  
                data: new FormData(this),  
                contentType: false,  
                processData:false,  
                success: function(data)  
                {  
                    location.reload();
                }  
           });  
      });  
 });
function sendComment(me,postId,value,event){
	if(event.keyCode == '13'){
		var fd = new FormData();
		fd.append('commentName',me);
		fd.append('postId',postId);
		fd.append('text',value);
		$.ajax({  
        	url: "addComment.php",  
        	type: "POST",  
    	    data: fd,  
        	contentType: false,  
        	processData:false,  
        	success: function(data)  
        	{  
            	alert(data);
            	location.reload();
        	}  
		   }); 
		}
}
</script>

<body>  
    Hello <?php echo htmlentities($_SESSION['user']['username'], ENT_QUOTES, 'UTF-8'); ?>
           <br />
	<a href="memberlist.php">Userslist</a>
	<br />
	<a href="edit_account.php">Edit Account</a>
	<br />
	<a href="logout.php">Logout</a>
	<br />
	<br />
	<div class="container">
		<form id="uploadForm" action="upload.php" method="post">
			Enter your post: <input type="text" name="msg"> Tag some Friends <input
				id="friends" list="browsers" name="browser"
				onkeyup="showFriends(this.value);">
			<datalist id="browsers">
			</datalist>
			<input type='button' name='okTags' onclick="addFriend(browser.value)">
			<div id="gallery"></div>
			<div style="clear: both;"></div>
			<br /> <br /> <input type="hidden" name="username" value=<?php echo $_SESSION['user']['username']?>>
	<input type="hidden" name="friendsList" value=''>
	<input type="checkbox" name="private">Is private?
	<div class="col-md-4">
		<input name="files[]" type="file" multiple />
	</div>
	<div class="col-md-4">
		<input type="submit" value="Submit" />
	</div>
	<div style="clear: both"></div>
	</form>
	</div>
	<table id="postList" border="1">
		<tr>
			<th>Time
			<th>UserName
			<th>Post
			<th>Images
			<th>Comment</th>
    <?php foreach ( $rows as $row ) :
					if (($row ['username'] != $_SESSION ['user'] ['username']) and ($row ['isPrivate'] == '1'))
						continue;
					?> 		
		<tr>
			<td><?php echo $row['time']?>			
			<td><?php echo htmlentities($row['username'], ENT_QUOTES, 'UTF-8');?> 
   			<td><?php echo htmlentities($row['post'], ENT_QUOTES, 'UTF-8'); ?></td>
			<td><?php $im = explode ( "@", $row ['images'] );
					foreach ( $im as $i ) :
						if ($i != '')
							echo "<img src = 'upload/" . $i . "' width='150px' height='180px'>";
					endforeach;?></td>
			<td><input type="text" name="comment" onkeypress="sendComment('<?php echo $_SESSION['user']['username'] ?>','<?php echo htmlentities($row['id'], ENT_QUOTES, 'UTF-8')?>',this.value,event)"></td>
		</tr>
		<?php foreach ($comments as $c):
				if($c['id'] == $row['id'])
					echo "<tr><td></td><td>" . $c['time'] . "</td><td>" . $c['commentName'] . "</td><td>" . $c['textOfComment'] . "</td><td></td></tr>"; 
				endforeach;?> 
    <?php endforeach; ?>  
</table>
</body>
</html>