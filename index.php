<?php
/*
if($_GET['key']!="Xhy8dndmghk9walfvgjfkms8fkgnd")
{
	die();
}
*/
include('referrals.class.php');
include('settings.php');
//$mysqli = new mysqli("mysql", "root", "F7m9dSz0", "reserve");
$months_apart=36;
$percentage=7;
/*
if(!isset($_GET['res']))
{
	die('no res #');	
}
*/
/*
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
			
		}
*/		$res=array(65358);
		check_reservations($res,$mysqli,$months_apart,$percentage);
function check_reservations($res_array,$mysqli,$months_apart,$percentage)
	{	
			foreach($res_array as $res)
			{
					$ref=new referral_reservation_check($res,$mysqli,$months_apart);
					$referral_array=array();
					if($ref->booker==19)
			{
				foreach($ref->passengers as $aggressor_passenger)
				{
					$passenger=new referral($aggressor_passenger['passengerID'],$ref->resid,$ref->date_aggressor,$mysqli,$months_apart,$percentage);
						print_r($passenger);
						if($passenger->valid)
						{	
							//array_push($referral_array,$passenger);
							$passenger->insert();

						}
				}
				
			}
		}
	}
?>