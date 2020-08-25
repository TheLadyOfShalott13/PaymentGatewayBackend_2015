<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
		<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	</head>

	<body style="margin-top: 50px;">
		<div class="container">
			<div style="text-align: center; width: 50%; margin-left: 25%">
				<form method="post" action="decryption2.php" role="form">
					<div class="form-group">
						<label>Username</label>
						<input type="text" name="username" class="form-control" required />
					</div>
					<div class="form-group">
						<label>Password</label>
						<input type="password" name="password" class="form-control" required />
					</div>
					<button type="submit" class="btn btn-success">Log-In</button>
				</form>
			</div>
		</div>
	</body>


</html>
<?php
	require('connect_enc.php');
	
	function user_exist($username){
		return (mysql_result(mysql_query("SELECT COUNT(`pass`) FROM `password` WHERE `username` = '$username' "), 0) == 1) ? true : false;
	}

	function gen_len($stored_pass)
	{
		$a=$stored_pass;
		$len=fmod($a,10);
		return $len;
	}

	function gen_e($len,$stored_pass)
	{
		$a=strlen($stored_pass);
		$index=$a-1-$len;
		$b=substr($stored_pass, $index);
		$c=strrev($b);
		$d=substr($c, 1);
		$e=strrev($d);
		return $e;
	}

	function gen_pass($len, $stored_pass, $password, $n)
	{
		$e=gen_e($len, $stored_pass);
		$a=0;
		for ($i=0; $i < strlen($password); $i=$i+1)
		{
			$z=ord($password{$i});
			$a=$a+(ord($password{$i})); //taking each char of the password, finding ASCII value and adding to itself
		}
		//echo '$a is : '.$a.'<br>';
		//echo '$e is : '.$e.'<br>';
		$d=bcpowmod($a, $e, 1000000000000); // taking only last 16 bits as key
		//echo 'This is $d :'.$d.'<br>';
		$c=fmod($d,$n);
		//echo 'This is $c before concat'.$c.'<br>';
		$len=strlen($e);
		$c= $c.$e.$len; //concatenating $e at the end with its length
		return $c;
	}

	function check_pass($len, $stored_pass, $password, $n)
	{
		$newpass=gen_pass($len, $stored_pass, $password, $n);
		if ($newpass == $stored_pass)
		{
			$retval= 1;
		}
		else
		{
			$retval = 0;
		}
		return $retval;
	}

	if(empty($_POST) === false)
	{
		$username = $_POST['username'];
		$password = $_POST['password'];
		
		if (!user_exist($username))
		{
			echo "Username does not exist.";
		}		
		else
		{
			if (strlen($password)!=8)
			{
				header("Location:http://localhost/dbms1/pass1.php");
			}
			else
			{
				if (!preg_match_all('$\S*(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\W])\S*$',$password)) 
				{
					header("Location:http://localhost/dbms1/pass2.php");
				}
				else
				{
					$username = $_POST['username'];
					$password = $_POST['password'];
					if (!user_exist($username))
					{
						echo "Sorry, user does not exist.";

					}
					else
					{
						$query = "SELECT * FROM password WHERE `username` = '$username' ";
						$result = mysql_query($query, $conn);
						while ($row = mysql_fetch_array($result)) //get the stuff from the database
						{
							$username1=$row['username'];
							$n=$row['salt'];
							$stored_pass=$row['pass'];
						}

						$len=gen_len($stored_pass); //generate length reqd for getting e parameter
						$retval=check_pass($len, $stored_pass, $password, $n);
						if ($retval == 0)
						{
							echo "PASSWORD INCORRECT";
						}
						else
						{
							echo "ACCESS GRANTED";
						}
					
					}
				}
			}
		}
		
	}


?>