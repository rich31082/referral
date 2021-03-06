<?
class referral_reservation_check
	{	
		var $resid;
		var $booker;
		var $date_aggressor;
		var $mysqli;
		//Array of passenger ids on reservation
		var $passengers;
		function __construct($resid,$mysqli,$months_apart)
			{	$this->passengers=array();
				$this->resid=$resid;
				$this->mysqli=$mysqli;	
				$this->get_reseller($this->resid);
			}
		 function get_reseller()	
			{	
				$q="SELECT r.`reservation_date`,a.`resellerID` FROM `reservations` r, `reseller_agents` a, `resellers` s where r.`reseller_agentID`=a.`reseller_agentID` and r.`reservationID`=$this->resid and s.`resellerID`=a.`resellerID` and 
				s.`status`='ACTIVE' order by r.`reservation_date` desc";
			//	die($q);
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
								$bad_passengers =array(0,61531879,61531873,61531880,'');
								if(in_array($row['passengerID'],$bad_passengers))
									{

									}
								else	
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
		var $reseller_agent;
		var $valid;
		var $mysqli;
		var $status;
		//how far apart we want the reservations ie: 3 years in months
		var $distance;
		var $amount;
		var $percentage;
		var $months_apart;
		var $final_payment_id;
		var $email;
		var $first_pass;
			function __construct($passenger,$aggressor_res,$aggressor_date,$mysqli,$distance,$percentage)
				{
					$this->mysqli=$mysqli;
					$this->contactID=$passenger;	
					$this->aggressor_date=$aggressor_date;
					$this->aggressor_res=$aggressor_res;
					$this->distance=$distance;
					$this->percentage=($percentage/100);
					$this->status='New';
					$this->get_last_reseller_res();
					$this->get_final_payment_id();
					$this->get_pass_first_res();	
				}
			function get_final_payment_id()
				{

					$q="Select `reservation_paymentID` from `reservation_payments` where `reservationID`=$this->aggressor_res";
					//echo($q);
					$row=$this->mysqli->query($q);
						if($row->num_rows==0)
								{
									$this->valid=false;
								}
						else
								{
									$row=$row->fetch_assoc();
									//echo('<br><br>');
									//print_r($row);
									$this->final_payment_id=$row['reservation_paymentID'];
								}		
					
				}	
			function get_last_reseller_res()
				{	$q="SELECT i.`reservationID`,i.`inventoryID`,r.`reseller_agentID`,s.`resellerID`,r.`reservation_date`,a.`company`,a.`email` FROM `inventory` i, `reservations` r,`reseller_agents` s,`resellers` a WHERE a.`resellerID`=s.`resellerID` and i.`reservationID` = r.`reservationID` and s.`reseller_agentID`=r.`reseller_agentID` and `passengerID`= $this->contactID and s.`resellerID`!=19 and i.`reservationID`!=$this->aggressor_res and `r.reservation_date`<=$this->aggressor_date order by r.`reservation_date` desc limit 1";
				//test query
		/*			$q="SELECT i.`reservationID`,i.`inventoryID`,r.`reseller_agentID`,s.`resellerID`,r.`reservation_date`,a.`company`,s.`email` FROM `inventory` i, `reservations` r,`reseller_agents` s,`resellers` a WHERE a.`resellerID`=s.`resellerID` and i.`reservationID` = r.`reservationID` and s.`reseller_agentID`=r.`reseller_agentID` and `passengerID`= $this->contactID and s.`resellerID`!=19 and i.`reservationID`!=$this->aggressor_res and r.`reservation_date`<=$this->aggressor_date order by r.`reservation_date`desc limit 1";
		*/			$row=$this->mysqli->query($q);
					if($row->num_rows>0)
					{
						$row=$row->fetch_assoc();
						$this->reseller_agent=$row['reseller_agentID'];
						$this->reseller=$row['resellerID'];
						$this->reseller_res=$row['reservationID'];
						$this->reseller_date=$row['reservation_date'];
					//	$this->inventory_id=$row['inventoryID'];
						$this->reseller_name=$row['company'];
						$this->email=$row['email'];

						$this->check_distance();
					}
					else
					{
						$this->valid=FALSE;
					}
				}
			function get_aggressor_inventory_id()
					{
						$q="Select `inventoryid` from `inventory` where `reservationID`=$this->aggressor_res and `passengerID`=$this->contactID";
						//echo($q);
						$row=$this->mysqli->query($q);
						$row=$row->fetch_assoc();
						
						$this->inventory_id=$row['inventoryid'];
					}			
			function check_distance()
				{

				 $gap=(int)abs((strtotime($this->aggressor_date) - strtotime($this->reseller_date))/(60*60*24*30));
				  //die($this->aggressor_date.' '.$this->reseller_date.'gap'.$gap);
				 // echo('here'.$this->distance);	
				 if($this->distance>=$gap)
				 	{	
				 		$this->valid=true;
						$this->months_apart=$gap;
						$this->get_aggressor_inventory_id();
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
					//echo($q);
					$row=$this->mysqli->query($q);
					$row=$row->fetch_assoc();
					//echo('lookg at me'.$this->percentage);
					$this->amount=floor(($row['total']*$this->percentage));
					if($this->amount<0)
							$this->amount=0;
				}
			function get_referral($refID)
				{
					$q="Select * from `referral_study` where `referral_ID` = $refID";
					$row=$mysqli->query($q);
					$row=$row->fetch_assoc();
					extract($row);
					$this->aggresor_res=$aggressor_res_number;
					$this->reseller_res=$reseller_res_number;
					$this->months_apart=$months_apart;
					$this->amount=$voucher_amount;
					$this->percentage=($percentage_at_time);
					$this->status=$status;
				}
			function referral_email($status,$email_text)
				{
					$q="Select `email` form `resellers` where `resellerID`='".$this->reseller."'";
					$row=$this->mysli->query($q);
					$row=$row->fetch_assoc();
					$email=$row['email'];
					mail('rich@aggressor.com','email_test',$email);
				}

			function make_voucher()
				{	
					$query="Select `contactID` from `contacts` where `reseller_agentID`=$this->reseller_agent";
					$row=$this->mysqli->query($query);
					$row=$row->fetch_assoc();
					$today = date("Ymd");
					$end = date("m/d/Y", strtotime('+1 years'));
					$q="Insert into `voucher` (`voucher`,`contactID`,`contact_textfield`,`agency`,`boatID`,`amount`,`issueboatID`,`expiration_date`,`date_added`,`reason1`) Values('Referral Voucher',".$row['contactID'].",' ',"."'".$this->reseller_name."'".",'All Yachts',$this->amount,'Reseller Loyalty Program',"."'".$end."',$today,'Reseller Loyalty Program')";
 				//	echo($q);
 					$q=$this->mysqli->query($q);
 					$voucher=$this->mysqli->insert_id;
 				//	echo('voucher'.$voucher);
 					$q="Update `referrals` Set `voucher`=$voucher, `date_processed`=$today where `aggressor_res_number`=$this->aggressor_res AND `contactID`=$this->contactID";
 					//die($q);
 					$this->mysqli->query($q);
 					
				}	
			function insert()
				{	//echo($this->email);
					$today = date("Ymd");
					$q="Insert into `referral_study` (`contactID`,`resellerID`,`aggressor_res_number`,`aggressor_date`,`reseller_res_number`,`months_apart`,`voucher_amount`,`percentage_at_time`,`status`,`final_payment_id`,`company`,`email`,`date_added`) Values ($this->contactID,$this->reseller,$this->aggressor_res,$this->aggressor_date,$this->reseller_res,$this->months_apart,$this->amount,$this->percentage*100,'".$this->status."',$this->final_payment_id,'".$this->reseller_name."','".$this->email."',$today)";
					//echo($q);
					$this->mysqli->query($q);

				}
			function get_pass_first_res()
				{
					$q="Select i.`reservationID`,r.`reseller_agentID`,a.`resellerID` from `inventory` i,`reservations` r,`reseller_agents` a  where a.`reseller_agentID`=r.`reseller_agentID` and i.`reservationID`= r.`reservationID` and i.`passengerID`=$this->contactID order by r.`reservation_date` ASC limit 1";
					
					$row=$this->mysqli->query($q);
					$row=$row->fetch_assoc();
					if($row['resellerID']!=19)
							{	$q="Select `referral_id` from `referral_study` where `contactID`=$this->contactID";
								
								$result=$this->mysqli->query($q);
								$num=$result->num_rows;
								
									if($num==0)
										{
											$this->first_pass=true;
											
										}
										else
										{
											$this->first_pass=false;
										}	
										
							}
							
				}						

	}		
			
	
	
?>
