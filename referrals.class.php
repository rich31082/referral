<?
class referral_reservation_check
	{	
		var $resid;
		var $booker;
		var $date_aggressor;
		var $mysqli;
		//Array of passenger ids on reservation
		var $passengers;
		function __construct($resid,$mysqli)
			{	$this->passengers=array();
				$this->resid=$resid;
				$this->mysqli=$mysqli;	
				$this->get_reseller($this->resid);
			}
		 function get_reseller()	
			{	
				$q="SELECT r.`reservation_date`,`resellerID` FROM `reservations` r, `reseller_agents` a where r.`reseller_agentID`=a.`reseller_agentID` and r.`reservationID`=$this->resid order by r.`reservation_date` desc";
				$result = ($this->mysqli->query($q));
					$result=$result->fetch_assoc();
					if($result['resellerID']==19)
							{
								$this->booker=$result['resellerID'];
								$this->date_aggressor=$result['reservation_date'];
								$this->get_passengers();
							}
					else
							{
								$this->booker=$result['resellerID'];
							}
			}
		function get_passengers()
			{
				$q="Select i.`passengerID` from `inventory` i where i.`reservationID`=$this->resid";
				$q=$this->mysqli->query($q);
					while($row=mysqli_fetch_assoc($q))
							{	//print_r($row);
								if($row['passengerID']!='0')
								array_push($this->passengers,$row);
							}		

			}	

	}
	
class referral
	{	var $inventory_id;
		var $contactID;
		var $aggressor_date;
		var $reseller_date;
		var $reseller;
		var $reseller_name;
		var $aggressor_res;
		var $reseller_res;
		var $valid;
		var $mysqli;
		var $status;
		//how far apart we want the reservations ie: 3 years in months
		var $distance;
		var $amount;
		var $percentage;
		var $months_apart;
		var $final_payment_id;
			function __construct($passenger,$aggressor_res,$aggressor_date,$mysqli,$distance,$percentage)
				{
					$this->mysqli=$mysqli;
					$this->contactID=$passenger;	
					$this->aggressor_date=$aggressor_date;
					$this->aggressor_res=$aggressor_res;
					$this->distance=$distance;
					$this->get_last_reseller_res();
					$this->percentage=($percentage/100);
					$this->status='New';
					$this->get_final_payment_id();
				}
			function get_final_payment_id()
				{

					$q="Select `reservation_paymentID` from `reservation_payments` where `reservationID`=$this->aggressor_res and `final_payment_marker`=1";
					$row=$this->mysqli->query($q);
						if($row->num_rows==0)
								{
									$this->valid=false;
								}
						else
								{
									$row=$row->fetch_assoc();
									$this->final_payment_id=$row['reservation_paymentID'];
								}		
					
				}	
			function get_last_reseller_res()
				{	$q="SELECT i.`reservationID`,i.`inventoryID`,r.`reseller_agentID`,s.`resellerID`,r.`reservation_date`,a.`company` FROM `inventory` i, `reservations` r,`reseller_agents` s,`resellers` a WHERE a.`resellerID`=s.`resellerID` and i.`reservationID` = r.`reservationID` and s.`reseller_agentID`=r.`reseller_agentID` and `passengerID`= $this->contactID and s.`resellerID`!=19 and i.`reservationID`!=$this->aggressor_res order by r.`reservation_date` desc limit 1";
					$row=$this->mysqli->query($q);
					if($row->num_rows>0)
					{
						$row=$row->fetch_assoc();
						$this->reseller=$row['resellerID'];
						$this->reseller_res=$row['reservationID'];
						$this->reseller_date=$row['reservation_date'];
						$this->inventory_id=$row['inventoryID'];
						$this->reseller_name=$row['company'];
						$this->check_distance();
					}
					else
					{
						$this->valid=FALSE;
					}
				}		
			function check_distance()
				{

				 $gap=(int)abs((strtotime($this->aggressor_date) - strtotime($this->reseller_date))/(60*60*24*30));
				  	
				 if($this->distance>=$gap)
				 	{	
				 		$this->valid=true;
						$this->months_apart=$gap;
						$this->get_amount();
						
					}
					else
					{
						$this->valid=false;
						$this->amount=0;
					}	
				}
			function get_amount()
				{	$q="SELECT i.`bunk_price`-i.`manual_discount`-i.`DWC_discount`-i.`voucher`-i.`passenger_discount` as total FROM `inventory` i where i.`inventoryID`=$this->inventory_id";
					$row=$this->mysqli->query($q);
					$row=$row->fetch_assoc();
					$this->amount=($row['total'])*@$this->percent;
				}
			function get_referral($refID)
				{
					$q="Select * from `referrals` where `referral_ID` = $refID";
					$row=$mysqli->query($q);
					$row=$row->fetch_assoc();
					extract($row);
					$this->aggresor_res=$aggressor_res_number;
					$this->reseller_res=$reseller_res_number;
					$this->months_apart=$months_apart;
					$this->amount=$voucher_amount;
					$this->percentage=$percentage_at_time;
					$this->status=$status;
				}
			function referral_email($status,$email_text)
				{
					$q="Select `email` form `resellers` where `resellerID`=$this->reseller";
					$row=$this->mysli->query($q);
					$row=$row->fetch_assoc();
					$email=$row['email'];
				}				

	}		
			
	
	
?>
