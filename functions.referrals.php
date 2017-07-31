<?php
include("settings.php");
include("referrals.class.php");
$execute=$_GET['f'];
$referral=$_GET['r'];
if($execute=='email')
	{	
		e_mail($referral,$mysqli,$percentage,$months_apart);
	}	
if($execute=='hide')
	{
			hide($mysqli,$referral,$_GET['hide']);
	}		
	
	function hide($mysqli,$referral,$hide)
			{
				$q="Update `referrals` set `hide`=$hide where `referral_id`=$referral";
				$mysqli->query($q);
				header('Location: https://reservations.aggressor.com/referrals_new.php');
			}	
	function e_mail($referral,$mysqli,$percentage,$months_apart)
		{

			$text='You have a new referral';
			$q="Update `referrals` set `status`= 'processed' where `referral_id`=$referral";
			$row=$mysqli->query($q);
			$q="Select * from `referrals` where `referral_id`=$referral";
			$row=$mysqli->query($q);
			$row=$row->fetch_assoc();
			//print_r($row);
			extract($row);
			$referral=new referral($contactID,$aggressor_res_number,$aggressor_date,$mysqli,$months_apart,$percentage);
			//print_r($referral);
			$referral->make_voucher();
			header('Location: https://reservations.aggressor.com/referrals_new.php');
		}

?>