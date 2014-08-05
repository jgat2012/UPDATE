<?php
//error_reporting(0);
class auto_management extends MY_Controller {
	var $nascop_url = "";
	function __construct() {
		parent::__construct();

		ini_set("max_execution_time", "100000");
		ini_set("memory_limit", '2048M');
		ini_set("allow_url_fopen", '1');

	    $dir = realpath($_SERVER['DOCUMENT_ROOT']);
	    $link = $dir . "\\ADT\\assets\\nascop.txt";
		$this -> nascop_url = file_get_contents($link);
	}

	public function index(){
		$message ="";
		$today = (int)date('Ymd');

		//get last update time of log file for auto_update
		$log=Migration_Log::getLog('auto_update');
		$last_update = (int)$log['last_index'];

		//if not updated today
		if ($today != $last_update) {
			//function to update destination column to 1 in drug_stock_movement table for issued transactions that have name 'pharm'
			$message .= $this->updateIssuedTo();
			//function to update source_destination column in drug_stock_movement table where it is zero
			$message .= $this->updateSourceDestination();
			//function to update ccc_store_sp column in drug_stock_movement table for pahrmacy transactions
			$message .= $this->updateCCC_Store();
			//function to update patients without current_regimen with last regimen dispensed
			$message .= $this->update_current_regimen(); 
			//function to send eid statistics to nascop dashboard
			$message .= $this->updateEid();
			//function to update patient data such as active to lost_to_follow_up	
			$message .= $this->updatePatientData();
			//function to update data bugs by applying query fixes
			$message .= $this->updateFixes();
	        //finally update the log file for auto_update 
	        if ($this -> session -> userdata("curl_error") != 1) {
	        	$sql="UPDATE migration_log SET last_index='$today' WHERE source='auto_update'";
				$this -> db -> query($sql);
				$this -> session -> set_userdata("curl_error", "");
			} 
	    }
	    echo $message;
	}

	public function updateDrugId() {
		//function to update drug_id column in drug_stock_movement table where drug_id column is zero
		//Get batches for drugs which are associateed with those drugs
		$sql = "SELECT batch_number
				FROM  `drug_stock_movement` 
				WHERE drug =0 AND batch_number!=''
				ORDER BY  `drug_stock_movement`.`drug` ";

		$query = $this -> db -> query($sql);
		$res = $query -> result_array();
		$counter = 0;
		foreach ($res as $value) {
			$batch_number = $value['batch_number'];

			//Get drug  id from drug_stock_balance
			$sql = "SELECT drug_id FROM drug_stock_balance WHERE batch_number = '$batch_number' LIMIT 1";
			$query = $this -> db -> query($sql);
			$res = $query -> result_array();
			if (count($res) > 0) {
				$drug_id = $res[0]['drug_id'];
				//Update drug id in drug stock movement
				$sql = "UPDATE drug_stock_movement SET drug = '$drug_id' WHERE batch_number = '$batch_number' AND drug = 0 ";
				$query = $this -> db -> query($sql);
				$counter++;
			}

		}
		$message="";
		if($counter>0){
			$message=$counter . " records have been updated!<br/>";
		}
		return $message;
	}

	public function updateDrugPatientVisit() {
		//function to update drug column in patient_visit table where drug column is zero
		//Get batches for drugs which are associateed with those drugs
		$sql = "SELECT batch_number
				FROM  `patient_visit` 
				WHERE drug_id =0 AND batch_number!=''
				ORDER BY  `patient_visit`.`drug_id` ";

		$query = $this -> db -> query($sql);
		$res = $query -> result_array();
		$counter = 0;
		foreach ($res as $value) {
			$batch_number = $value['batch_number'];

			//Get drug  id from drug_stock_balance
			$sql = "SELECT drug_id FROM drug_stock_balance WHERE batch_number = '$batch_number' LIMIT 1";
			$query = $this -> db -> query($sql);
			$res = $query -> result_array();
			if (count($res) > 0) {
				$drug_id = $res[0]['drug_id'];
				//Update drug id in patient visit
				$sql = "UPDATE patient_visit SET drug_id = '$drug_id' WHERE batch_number = '$batch_number' AND drug_id = '0' ";
				//echo $sql;die();
				$query = $this -> db -> query($sql);
				$counter++;
			}
		}

		$message="";
		if($counter>0){
			$message=$counter . " records have been updated!<br/>";
		}
		return $message;
	}

	public function updateIssuedTo(){
		$sql="UPDATE drug_stock_movement
		      SET destination='1'
		      WHERE destination LIKE '%pharm%'";
		$this->db->query($sql);
		$count=$this->db->affected_rows();
		$message="(".$count.") issued to transactions updated!<br/>";
		$message="";
		if($count>0){
			$message="(".$count.") issued to transactions updated!<br/>";
		}
		return $message;
	}

	public function updateSourceDestination(){
		$values=array(
			      'received from'=>'source',
			      'returns from'=>'destination',
			      'issued to'=>'destination',
			      'returns to'=>'source'
			      );
		$message="";
		foreach($values as $transaction=>$column){
				$sql="UPDATE drug_stock_movement dsm
					  LEFT JOIN transaction_type t ON t.id=dsm.transaction_type
					  SET dsm.source_destination=IF(dsm.$column=dsm.facility,'1',dsm.$column)
				      WHERE t.name LIKE '%$transaction%'
					  AND(dsm.source_destination IS NULL OR dsm.source_destination='' OR dsm.source_destination=0)";
                $this->db->query($sql);
                $count=$this->db->affected_rows();
                $message.=$count." ".$transaction." transactions missing source_destination(".$column.") have been updated!<br/>";
		}
		if($count<=0){
			$message="";
		}
		return $message;
	}

	public function updateCCC_Store(){
        $facility_code=$this->session->userdata("facility");
		$sql="UPDATE drug_stock_movement dsm
		      SET ccc_store_sp='1'
		      WHERE dsm.source !=dsm.destination
		      AND ccc_store_sp='2' 
		      AND (dsm.source='$facility_code' OR dsm.destination='$facility_code')";
        $this->db->query($sql);
        $count=$this->db->affected_rows();
        $message="(".$count.") transactions changed from main pharmacy to main store!<br/>";

        if($count<=0){
			$message="";
		}
		return $message;
	}

	public function update_current_regimen() {
		$count=1;
		//Get all patients without current regimen and who are not active
		$sql_get_current_regimen = "SELECT p.id,p.patient_number_ccc, p.current_regimen ,ps.name
									FROM patient p 
									INNER JOIN patient_status ps ON ps.id = p.current_status
									WHERE current_regimen = '' 
									AND ps.name != 'active'";
		$query = $this -> db -> query($sql_get_current_regimen);
		$result_array = $query -> result_array();
		foreach ($result_array as $value) {
			$patient_id = $value['id'];
			$patient_ccc = $value['patient_number_ccc'];
			//Get last regimen
			$sql_last_regimen = "SELECT pv.last_regimen FROM patient_visit pv WHERE pv.patient_id='" . $patient_ccc . "' ORDER BY id DESC LIMIT 1";
			$query = $this -> db -> query($sql_last_regimen);
			$res = $query -> result_array();
			if (count($res) > 0) {
				$last_regimen_id = $res[0]['last_regimen'];
				$sql = "UPDATE patient p SET p.current_regimen ='" . $last_regimen_id . "'  WHERE p.id = '" . $patient_id . "'";
				$query = $this -> db -> query($sql);
				$count++;
			}
		}        
        $message="(".$count.") patients without current_regimen have been updated with last dispensed regimen!<br/>";
        if($count<=0){
			$message="";
		}
		return $message;
	}

	public function updateEid() {
		$adult_age = 3;
		$facility_code = $this -> session -> userdata("facility");
		$url = $this -> nascop_url . "sync/eid/" . $facility_code;
		$sql = "SELECT patient_number_ccc as patient_no,
		               facility_code,
		               g.name as gender,
		               p.dob as birth_date,
		               rst.Name as service,
		               CONCAT_WS(' | ',r.regimen_code,r.regimen_desc) as regimen,
		               p.date_enrolled as enrollment_date,
		               ps.name as source,
		               s.name as status
				FROM patient p
				LEFT JOIN gender g ON g.id=p.gender
				LEFT JOIN regimen_service_type rst ON rst.id=p.service
				LEFT JOIN regimen r ON r.id=p.start_regimen
				LEFT JOIN patient_source ps ON ps.id=p.source
				LEFT JOIN patient_status s ON s.id=p.current_status
				WHERE p.active='1'
				AND round(datediff(p.date_enrolled,p.dob)/360)<$adult_age";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();

		$json_data = json_encode($results, JSON_PRETTY_PRINT);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('json_data' => $json_data));
		$json_data = curl_exec($ch);
		if (empty($json_data)) {
			$message = "cURL Error: " . curl_error($ch);
			$this -> session -> set_userdata("curl_error", 1);
		} else {
			$messages = json_decode($json_data, TRUE);
			$message = $messages[0];
		}
		curl_close($ch);
		return $message."<br/>";
	}
    
    public function updateSms() {
		$facility_name=$this -> session -> userdata('facility_name');

		/* Find out if today is on a weekend */
		$weekDay = date('w');
		if ($weekDay == 6) {
			$tommorrow = date('Y-m-d', strtotime('+2 day'));
		} else {
			$tommorrow = date('Y-m-d', strtotime('+1 day'));
		}

		$phone_minlength = '8';
		$phone = "";
		$phone_list = "";
		$first_part = "";
		$kenyacode = "254";
		$arrDelimiters = array("/", ",", "+");

		$message = "You have an Appointment on " . date('l dS-M-Y', strtotime($tommorrow)) . " at $facility_name";
		/*Get All Patient Who Consented Yes That have an appointment Tommorow */
		$sql = "SELECT p.phone,p.patient_number_ccc,p.nextappointment,temp.patient,temp.appointment,temp.machine_code as status,temp.id
					FROM patient p
					LEFT JOIN 
					(SELECT pa.id,pa.patient, pa.appointment, pa.machine_code
					FROM patient_appointment pa
					WHERE pa.appointment =  '$tommorrow'
					GROUP BY pa.patient) as temp ON temp.patient=p.patient_number_ccc
					WHERE p.sms_consent =  '1'
					AND p.nextappointment =temp.appointment
					AND char_length(p.phone)>$phone_minlength
					AND temp.machine_code !='s'
					GROUP BY p.patient_number_ccc";

		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$count=$query -> num_rows();
		$alert = "Patients notified (<b>" . $query -> num_rows() . "</b>)";

		if ($results) {
			foreach ($results as $result) {
				$phone = $result['phone'];
				$newphone = substr($phone, -$phone_minlength);
				$first_part = str_replace($newphone, "", $phone);

				if (strlen($first_part) < 7) {
					if ($first_part === '07') {
						$phone = "+" . $kenyacode . substr($phone, 1);
						$phone_list .= $phone;
					} else if ($first_part == '7') {
						$phone = "0" . $phone;
						$phone = "+" . $kenyacode . substr($phone, 1);
						$phone_list .= $phone;
					} else if ($first_part == '+' . $kenyacode . '07') {
						$phone = str_replace($kenyacode . '07', $kenyacode . '7', $phone);
						$phone_list .= $phone;
					}
				} else {
					/*If Phone Does not meet requirements*/

					$phone = str_replace($arrDelimiters, "-|-", $phone);
					$phones = explode("-|-", $phone);

					foreach ($phones as $phone) {
						$newphone = substr($phone, -$phone_minlength);
						$first_part = str_replace($newphone, "", $phone);
						if (strlen($first_part) < 7) {
							if ($first_part === '07') {
								$phone = "+" . $kenyacode . substr($phone, 1);
								$phone_list .= $phone;
								break;
							} else if ($first_part == '7') {
								$phone = "0" . $phone;
								$phone = "+" . $kenyacode . substr($phone, 1);
								$phone_list .= $phone;
								break;
							} else if ($first_part == '+' . $kenyacode . '07') {
								$phone = str_replace($kenyacode . '07', $kenyacode . '7', $phone);
								$phone_list .= $phone;
								break;
							}
						}
					}
				}
				$stmt = "update patient_appointment set machine_code='s' where id='" . $result['id'] . "'";
				$q = $this -> db -> query($stmt);
			}
			$phone_list = substr($phone_list, 1);
		}
		$phone_list = explode("+", $phone_list);
		$message = urlencode($message);
		foreach ($phone_list as $phone) {
			file("http://41.57.109.242:13000/cgi-bin/sendsms?username=clinton&password=ch41sms&to=$phone&text=$message");
		}

		if($count<=0){
			$alert="";
		}
		return $alert;
	}

	public function updatePatientData() {
		$days_to_lost_followup = 90;
		$days_to_pep_end = 30;
		$days_in_year = date("z", mktime(0, 0, 0, 12, 31, date('Y'))) + 1;
		$adult_age = 12;
		$active = 'active';
		$lost = 'lost';
		$pep = 'pep';
		$pmtct = 'pmtct';
		$two_year_days = $days_in_year * 2;
		$adult_days = $days_in_year * $adult_age;
		$message = "";

		//Get Patient Status id's
		$status_array = array($active, $lost, $pep, $pmtct);
		foreach ($status_array as $status) {
			$s = "SELECT id,name FROM patient_status ps WHERE ps.name LIKE '%$status%'";
			$q = $this -> db -> query($s);
			$rs = $q -> result_array();
			$state[$status] = $rs[0]['id'];
		}

		/*Change Last Appointment to Next Appointment*/
		$sql['Change Last Appointment to Next Appointment'] = "(SELECT patient_number_ccc,nextappointment,temp.appointment,temp.patient
					FROM patient p
					LEFT JOIN 
					(SELECT MAX(pa.appointment)as appointment,pa.patient
					FROM patient_appointment pa
					GROUP BY pa.patient) as temp ON p.patient_number_ccc =temp.patient
					WHERE p.nextappointment !=temp.patient
					AND DATEDIFF(temp.appointment,p.nextappointment)>0
					GROUP BY p.patient_number_ccc) as p1
					SET p.nextappointment=p1.appointment";

		/*Change Active to Lost_to_follow_up*/
		$sql['Change Active to Lost_to_follow_up'] = "(SELECT patient_number_ccc,nextappointment,DATEDIFF(CURDATE(),nextappointment) as days
				   FROM patient p
				   LEFT JOIN patient_status ps ON ps.id=p.current_status
				   WHERE ps.Name LIKE '%$active%'
				   AND (DATEDIFF(CURDATE(),nextappointment )) >=$days_to_lost_followup) as p1
				   SET p.current_status = '$state[$lost]'";

		/*Change Lost_to_follow_up to Active */
		$sql['Change Lost_to_follow_up to Active'] = "(SELECT patient_number_ccc,nextappointment,DATEDIFF(CURDATE(),nextappointment) as days
				   FROM patient p
				   LEFT JOIN patient_status ps ON ps.id=p.current_status
				   WHERE ps.Name LIKE '%$lost%'
				   AND (DATEDIFF(CURDATE(),nextappointment )) <$days_to_lost_followup) as p1
				   SET p.current_status = '$state[$active]' ";

		/*Change Active to PEP End*/
		$sql['Change Active to PEP End'] = "(SELECT patient_number_ccc,rst.name as Service,ps.Name as Status,DATEDIFF(CURDATE(),date_enrolled) as days_enrolled
				   FROM patient p
				   LEFT JOIN regimen_service_type rst ON rst.id=p.service
				   LEFT JOIN patient_status ps ON ps.id=p.current_status
				   WHERE (DATEDIFF(CURDATE(),date_enrolled))>=$days_to_pep_end 
				   AND rst.name LIKE '%$pep%' 
				   AND ps.Name NOT LIKE '%$pep%') as p1
				   SET p.current_status = '$state[$pep]' ";

		/*Change PEP End to Active*/
		$sql['Change PEP End to Active'] = "(SELECT patient_number_ccc,rst.name as Service,ps.Name as Status,DATEDIFF(CURDATE(),date_enrolled) as days_enrolled
				   FROM patient p
				   LEFT JOIN regimen_service_type rst ON rst.id=p.service
				   LEFT JOIN patient_status ps ON ps.id=p.current_status
				   WHERE (DATEDIFF(CURDATE(),date_enrolled))<$days_to_pep_end 
				   AND rst.name LIKE '%$pep%' 
				   AND ps.Name NOT LIKE '%$active%') as p1
				   SET p.current_status = '$state[$active]' ";

		/*Change Active to PMTCT End(children)*/
		$sql['Change Active to PMTCT End(children)'] = "(SELECT patient_number_ccc,rst.name AS Service,ps.Name AS Status,DATEDIFF(CURDATE(),dob) AS days
				   FROM patient p
				   LEFT JOIN regimen_service_type rst ON rst.id = p.service
				   LEFT JOIN patient_status ps ON ps.id = p.current_status
				   WHERE (DATEDIFF(CURDATE(),dob )) >=$two_year_days
				   AND (DATEDIFF(CURDATE(),dob)) <$adult_days
				   AND rst.name LIKE  '%$pmtct%'
				   AND ps.Name NOT LIKE  '%$pmtct%') as p1
				   SET p.current_status = '$state[$pmtct]'";

		/*Change PMTCT End to Active(Adults)*/
		$sql['Change PMTCT End to Active(Adults)'] = "(SELECT patient_number_ccc,rst.name AS Service,ps.Name AS Status,DATEDIFF(CURDATE(),dob) AS days
				   FROM patient p
				   LEFT JOIN regimen_service_type rst ON rst.id = p.service
				   LEFT JOIN patient_status ps ON ps.id = p.current_status 
				   WHERE (DATEDIFF(CURDATE(),dob)) >=$two_year_days 
				   AND (DATEDIFF(CURDATE(),dob)) >=$adult_days 
				   AND rst.name LIKE '%$pmtct%'
				   AND ps.Name LIKE '%$pmtct%') as p1
				   SET p.current_status = '$state[$active]'";

				foreach ($sql as $i => $q) {
					$stmt1 = "UPDATE patient p,";
					$stmt2 = " WHERE p.patient_number_ccc=p1.patient_number_ccc;";
					$stmt1 .= $q;
					$stmt1 .= $stmt2;
					$q = $this -> db -> query($stmt1);
					if ($this -> db -> affected_rows() > 0) {
						$message .= $i . "(<b>" . $this -> db -> affected_rows() . "</b>) rows affected<br/>";
					}
				}
		return $message;
	}

	public function updateFixes(){
		//Rename the prophylaxis cotrimoxazole
        $fixes[]="UPDATE drug_prophylaxis
        	      SET name='cotrimoxazole'
        	      WHERE name='cotrimozazole'";
        //Remove start_regimen_date in OI only patients records
        $fixes[]="UPDATE patient p
                  LEFT JOIN regimen_service_type rst ON p.service=rst.id
                  SET p.start_regimen_date='' 
                  WHERE rst.name LIKE '%oi%'
                  AND p.start_regimen_date IS NOT NULL";
        //Update status_change_date for lost_to_follow_up patients
        $fixes[]="UPDATE patient p,
				 (SELECT p.id, INTERVAL 90 DAY + p.nextappointment AS choosen_date
				  FROM patient p
				  LEFT JOIN patient_status ps ON ps.id = p.current_status
				  WHERE ps.Name LIKE  '%lost%') as test 
				 SET p.status_change_date=test.choosen_date
				 WHERE p.id=test.id";
	    //Update patients without service lines ie Pep end status should have pep as a service line
        $fixes[]="UPDATE patient p
			 	  LEFT JOIN patient_status ps ON ps.id=p.current_status,
			 	  (SELECT id 
			 	   FROM regimen_service_type
			 	   WHERE name LIKE '%pep%') as rs
			 	  SET p.service=rs.id
			 	  WHERE ps.name LIKE '%pep end%'
			 	  AND p.service=''";
		//Updating patients without service lines ie PMTCT status should have PMTCT as a service line
        $fixes[]= "UPDATE patient p
				   LEFT JOIN patient_status ps ON ps.id=p.current_status,
				   (SELECT id 
				 	FROM regimen_service_type
				 	WHERE name LIKE '%pmtct%') as rs
				    SET p.service=rs.id
				    WHERE ps.name LIKE '%pmtct end%'
				 	AND p.service=''";
		//Remove ??? in drug instructions
		$fixes[]="UPDATE drug_instructions 
				  SET name=REPLACE(name, '?', '.')
				  WHERE name LIKE '%?%'";


		//Execute fixes
		$total=0;
		foreach ($fixes as $fix) {
			//will exempt all database errors
			$db_debug = $this->db->db_debug;
			$this->db->db_debug = false;
			$this -> db -> query($fix);
			$this->db->db_debug = $db_debug;
			//count rows affected by fixes
			if ($this -> db -> affected_rows() > 0) {
				$total += $this -> db -> affected_rows();
			}
	    }
        
        $message="(".$total.") rows affected by fixes applied!<br/>";
	    if($total>0){
			$message="";
		}
        return $message;
	}
}
?>
