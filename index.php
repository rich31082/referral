<?php
include('referrals.class.php');
$mysqli = new mysqli("mysql", "root", "F7m9dSz0", "reserve");
$months_apart=36;
$percentage=7;
if(!isset($_GET['res']))
{
	die('no res #');	
}

$ref=new referral_reservation_check($_GET['res'],$mysqli);
$referral_array=array();
	if($ref->booker==19)
		{
			foreach($ref->passengers as $aggressor_passenger)
			{
				$passenger=new referral($aggressor_passenger['passengerID'],$ref->resid,$ref->date_aggressor,$mysqli,$months_apart,$percentage);
					if($passenger->valid)
					{	
						array_push($referral_array,$passenger);

					}
			}
			print_r($referral_array);
		}
?>