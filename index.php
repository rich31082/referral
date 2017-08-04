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
	{	//study mode
		$year=14;
		while($year<=17)
		{
		$month=1;
			while($month<=12)
				{
				if($month<10)
					{
						$state_month='0'.$month;
					}	
					else
					{
						$state_month=$month;
					}				
		$start_date='20'.$year.$state_month.'01';
		//study mode*/
		//$start_date='20150101';
		echo($start_date);
		$q="Select a.* from `reservation_payments` a where not exists (Select * from `referral_study` where a.`reservation_paymentID`=`final_payment_id`) and a.`final_payment_marker`=1 and a.`payment_date`>=$start_date";
	
		$row=$mysqli->query($q);
		while($value=$row->fetch_assoc())
					{
						process_reservation($value['reservationID'],$mysqli,$months_apart,$percentage);
					}
		//study mode
		
	//	referral_study($start_date,$mysqli);			
				$month++;
				}//month loop
		$year++;
		}//year_loop
		
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
						if($passenger->valid&&$passenger->first_pass)
						{	
							//array_push($referral_array,$passenger);
							
							//echo('insert'.$passenger);
							$passenger->insert();

						}
				}
				
			}
		}
	}
function referral_study($start_date,$mysqli)
	{
		$q="Select sum(`voucher_amount`) amount from `referral_study`";
		$row=$mysqli->query($q);
		$row=$row->fetch_assoc();
		$line=array($row['amount'],$start_date);
		$handle = fopen("study.csv", "a");
		fputcsv($handle, $line);
		$q="Truncate `referral_study` ";
		$mysqli->query($q);
	}	
?>