<?php
include('settings.php');
include('referrals.class.php');
$months_apart=36;
$percentage=7;
$ref=new referral_reservation_check(70281,$mysqli);
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
