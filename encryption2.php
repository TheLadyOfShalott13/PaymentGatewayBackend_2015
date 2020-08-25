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
				<form method="post" action="encryption2.php" role="form">
					<div class="form-group">
						<label>Username</label>
						<input type="text" name="username" class="form-control" required />
					</div>
					<div class="form-group">
						<label>Password</label>
						<input type="password" name="password" class="form-control" required />
					</div>
					<button type="submit" class="btn btn-success">Insert</button>
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
function gen_prime_no() //recursive function to generate a random prime number
	{
		$a=rand(1,1000);
		$b=$a/2;
		$result=1;
		for ($i=2; $i<=$b; $i=$i+1)
		{
			if (fmod($a,$i)==0)
			{
				$result=0;
			}
		}
		if ($result==0)
		{
			gen_prime_no();
		}
		else
		{
			//return $a;
		}
		return $a;
	}

function gcd($a, $b)
	{
    	while ($b != 0)
    	{
    	    $m = $a % $b;
    	    $a = $b;
    	    $b = $m;
   		}
    	return $a;
	}

	function gen_e_para($n, $PQ)
	{
		$e=rand(1,$PQ);
		$result=gcd($e, $n);
		if ($result!=1)
		{
			$e=gen_e_para($n,$PQ); //call again until condition is satisfied
		}
		else
		{
			//return $e;
		}
		return $e;

	}

function gen_pass($n, $PQ, $password)
	{
		$e=gen_e_para($n,$PQ);
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


	if(empty($_POST) === false)
	{
		$username = $_POST['username'];
		$password = $_POST['password'];
		
		if (user_exist($username))
		{
			echo "Username exists. Enter another one.";
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
					$p=gen_prime_no();
					$q=gen_prime_no();
					$n=$p * $q; //salt
					$PQ= ($p - 1)*($q - 1);
					//echo 'This is $p: '.$p.'<br>';
					//echo 'This is $q: '.$q.'<br>';
					//echo 'This is $PQ:'.$PQ.'<br>';
					$newpass=gen_pass($n, $PQ, $password);
					$query = "INSERT INTO password (username, salt, pass) VALUES ('$username', '$n', '$newpass')";
					if(!user_exist($username))
					{
						$retval = mysql_query($query, $conn);
						echo '<br><h3>The entry has been successfully inserted. </h3>';
					} 
					else
					{
						echo '<h3>The entry exists. Please check the desired information.</h3>';
					}
				}
			}
		}
		
	}


?>