<?php
/*
if($_GET['key']!="Xhy8dndmghk9walfvgjfkms8fkgnd")
{
	die();
}
*/
include('referrals.class.php');
include('settings.php');
$months_apart=36;
$percentage=5;
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
*/
		scan($mysqli,$months_apart,$percentage);
function scan($mysqli,$months_apart,$percentage)
	{
		$q="Select a.* from `reservation_payments` a where not exists (Select * from `referrals` where a.`reservation_paymentID`=`final_payment_id`) and a.`final_payment_marker`=1 and a.`payment_date`>20170101";
		//echo($q);
		$row=$mysqli->query($q);
		while($value=$row->fetch_assoc())
					{
						process_reservation($value['reservationID'],$mysqli,$months_apart,$percentage);
					}

	}		
function process_reservation($res,$mysqli,$months_apart,$percentage)
	{
		$res=array($res);

		check_reservations($res,$mysqli,$months_apart,$percentage);
	}

function check_reservations($res_array,$mysqli,$months_apart,$percentage)
	{	
			foreach($res_array as $res)
			{
					$ref=new referral_reservation_check($res,$mysqli,$months_apart);
					$referral_array=array();
					if($ref->booker==19)
			{
				foreach($ref->passengers as $aggressor_passenger)
				{//	die('months_apart'.$months_apart);
					$passenger=new referral($aggressor_passenger['passengerID'],$ref->resid,$ref->date_aggressor,$mysqli,$months_apart,$percentage);
						//print_r($passenger);
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