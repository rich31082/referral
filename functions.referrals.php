<?php
include("settings.php");
$execute=$_GET['f'];
$referral=$_GET['r'];

if($execute=='email')
	{
		e_mail($referral,$mysqli);
	}	
	function e_mail($referral,$mysqli)
		{

			$text='You have a new referral';
			$q="Update `referrals` set `status`= 'commit' where `referral_id`=$referral";
			$row=$mysqli->query($q);
			header('Location: https://reservations.aggressor.com/referrals_new.php');
		}
	
?>