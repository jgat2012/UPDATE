<?php
foreach($results as $result){
};

?>
<!DOCTYPE html>
<html lang="en">
	<head>
            <style>
                @media screen{
                    .button-bar{
                        width: 10.7%;
                    }
                }
                    .btn_positioning{
                    float:right; position:fixed; bottom:70px; right:40px;
                    margin: 10px;
                }
                    .button_size{
                    height:38px; font-size:1em; font-weight: bold;
                }
                
                
            </style>
		<script type="text/javascript">
		$(document).ready(function(){
			$("#drug_prophylaxis").trigger("multiselectclick");
			//Function to Check Patient Numner exists
			var base_url="<?php echo base_url();?>";
		    $("#patient_number").change(function(){
				var patient_no=$("#patient_number").val();
				var original_patient_no=$("#original_patient_number").val();
				var link=base_url+"patient_management/checkpatient_no/"+patient_no;
				$.ajax({
				    url: link,
				    type: 'POST',
				    success: function(data) {
				       if(data==1 && original_patient_no !=patient_no){
				          bootbox.alert("<h4>Repeated Entry</h4>\n\<hr/><center>Patient Number Matches an existing record</center>");
				          $(".btn").attr("disabled","disabled");
				        }else{
				        	$(".btn").attr("disabled",false);
				        }
				    }
				});
	        });
	        
	        //Attach date picker for date of birth
	        $("#dob").datepicker({
					yearRange : "-120:+0",
					maxDate : "0D",
					dateFormat : $.datepicker.ATOM,
					changeMonth : true,
					changeYear : true
			});
			
			$("#medical_record_number").val("<?php echo $result['medical_record_number'];?>");
			$("#patient_number").val("<?php echo $result['patient_number_ccc'];?>");
			$("#original_patient_number").val("<?php echo $result['patient_number_ccc'];?>");
			$("#last_name").val("<?php echo $result['last_name'];?>");
			$("#first_name").val("<?php echo $result['first_name'];?>");
			$("#other_name").val("<?php echo $result['other_name'];?>");
			$("#dob").val("<?php echo $result['dob'];?>");
			$("#pob").val("<?php echo $result['pob'];?>");
			$("#gender").val("<?php echo $result['gender'];?>");
			$("#who_stage").val("<?php echo $result['who_stage'] ?>");
			
			//Display Gender Tab
			if($("#gender").val()==2){
				$("#pregnant_view").show();
			}
			$("#pregnant").val("<?php echo $result['pregnant'];?>");
			
			
			$('#start_age').val(getStartAge("<?php echo $result['dob'];?>","<?php echo $result['date_enrolled'];?>"));
			$('#age').val(getAge("<?php echo $result['dob'];?>"));
	        $('#start_weight').val("<?php echo $result['start_weight'];?>");
	        $('#start_height').val("<?php echo $result['start_height'];?>");
	        $('#start_bsa').val("<?php echo $result['start_bsa'];?>");
	        $('#current_weight').val("<?php echo $result['weight'];?>");
	        $('#current_height').val("<?php echo $result['height'];?>");
	        $('#current_bsa').val("<?php echo $result['sa'];?>");
	        $('#phone').val("<?php echo $result['phone'];?>");
	        
	        //To Check Sms Consent
			var sms_consent="<?php echo $result['sms_consent'];?>";
			if(sms_consent==1){
			$("#sms_yes").attr("checked", "true");	
			}else if(sms_consent==0){
			$("#sms_no").attr("checked", "true");	
			}
	        
	        
	        $('#physical').val("<?php echo $result['physical'];?>");
	        $('#alternate').val("<?php echo $result['alternate'];?>");
	        
	        $('#partner_status').val("<?php echo $result['partner_status'];?>");
	        $('#disclosure').val("<?php echo $result['disclosure'];?>");
	        
	        //Function to configure multiselect in family planning and other chronic illnesses
			$("#family_planning").multiselect().multiselectfilter();
			$("#other_illnesses").multiselect().multiselectfilter();
			$("#drug_allergies").multiselect().multiselectfilter();
			$("#drug_prophylaxis").multiselect().multiselectfilter();
			
			//On Select Drug Prophylaxis
			$("#drug_prophylaxis").on("multiselectclick", function(event, ui) { 
				var array_of_checked_values = $("select#drug_prophylaxis").multiselect("getChecked").map(function(){
				   return this.value;    
				}).get();
				
				$("select#drug_prophylaxis").multiselect("widget").find("input[value='1']").attr("disabled",false); 
				$("select#drug_prophylaxis").multiselect("widget").find("input[value='2']").attr("disabled",false); 
				//loop through values
				$.each(array_of_checked_values,function(i,v){
					if(v==1){
						//disable 2
						$("select#drug_prophylaxis").multiselect("widget").find(":checkbox[value='1']").each(function(){
						  $("select#drug_prophylaxis").multiselect("widget").find("input[value='2']").attr("disabled",true);
						});
					}else if(v==2){
						//disable 1
						$("select#drug_prophylaxis").multiselect("widget").find(":checkbox[value='1']").each(function(){
						   $("select#drug_prophylaxis").multiselect("widget").find("input[value='1']").attr("disabled",true); 
						});
					}
				});
			});
			
			//Select Family Planning Methods Selected
			var family_planning="<?php echo $result['fplan'];?>";
			
				if(family_planning != null || family_planning != " ") {
					var fplan = family_planning.split(',');
					for(var i = 0; i < fplan.length; i++) {
						$("select#family_planning").multiselect("widget").find(":checkbox[value='" + fplan[i] + "']").each(function() {
	                       $(this).click();
	                    });
					}
				}
				
			//To Disable Textareas
			$("textarea[name='other_chronic']").not(this).attr("disabled", "true");
			$("textarea[name='other_drugs']").not(this).attr("disabled", "true");
			$("textarea[name='other_allergies_listing']").not(this).attr("disabled", "true");
			$("textarea[name='support_group_listing']").not(this).attr("disabled", "true");
			
			//Select Other Illnesses Methods Selected
			var my_illnesses=<?php echo $result['other_illnesses'];?>;
			var other_illnesses='';
			
			$.each(my_illnesses, function(i, v){
				other_illnesses +=v+","
			});
			
			if (other_illnesses.indexOf(',') == -1) {
              other_illnesses=other_illnesses+",";
            }else{
              other_illnesses=other_illnesses;
            }
			var other_sickness="";
			if(other_illnesses != null || other_illnesses != " ") {
				var other_ill = other_illnesses.split(',');
				for(var i = 0; i < other_ill.length; i++) {
					$("select#other_illnesses").multiselect("widget").find(":checkbox[value='" + other_ill[i] + "']").each(function() {
                       $(this).click();
                    });
                   if(other_ill[i].charAt(0) !="-"){
                   	other_sickness+=","+other_ill[i];
                   }
				}
				$("#other_chronic").val(other_sickness.substring(1));
			}
			if($("#other_chronic").val()){
				$("input[name='other_other']").not(this).attr("checked", "true");
			    $("textarea[name='other_chronic']").not(this).removeAttr("disabled");		
			}

            $("#other_drugs").val("<?php echo  $other_drugs=str_replace(array("\n"," ","/"),array(" \ ","","-"),$result['other_drugs']);?>");

            if($("#other_drugs").val()){
				$("input[name='other_drugs_box']").not(this).attr("checked", "true");
			    $("textarea[name='other_drugs']").not(this).removeAttr("disabled");		
			}
			
			
			
			//Select Other Drug Allergies
			var other_drug_allergies='<?php echo  $adr=str_replace(array("\n"," ","/"),array(" \ ","","-"),$result['adr']);?>';
			
			if (other_drug_allergies.indexOf(',') == -1) {
              other_drug_allergies=other_drug_allergies+",";
            }else{
              other_drug_allergies=other_drug_allergies;
            }
			var other_drug_allergy="";
			if(other_drug_allergies != null || other_drug_allergies != " ") {
				var other_all = other_drug_allergies.split(',');
				for(var i = 0; i < other_all.length; i++) {
					$("select#drug_allergies").multiselect("widget").find(":checkbox[value='" + other_all[i] + "']").each(function() {
                       $(this).click();
                    });
                   if(other_all[i].charAt(0) !="-"){
                   	other_drug_allergy+=","+other_all[i];
                   }
				}
				$("#other_allergies_listing").val(other_drug_allergy.substring(1));
			}

			if($("#other_allergies_listing").val()){
				$("input[name='other_allergies']").not(this).attr("checked", "true");
			    $("textarea[name='other_allergies_listing']").not(this).removeAttr("disabled");		
			}
			
			//To Check Disclosure
			var disclosure="<?php echo $result['disclosure'];?>";
			if(disclosure==1){
			$("#disclosure_yes").attr("checked", "true");	
			}else if(disclosure==0){
			$("#disclosure_no").attr("checked", "true");	
			}
			
			
			
			
			 $("#support_group_listing").val("<?php echo $result['support_group']?>");

            if($("#support_group_listing").val()){
				$("input[name='support_group']").not(this).attr("checked", "true");
			    $("textarea[name='support_group_listing']").not(this).removeAttr("disabled");		
			}
			$('#tested_tb').val("<?php echo $result['tb_test'];?>");
			$('#pep_reason').val("<?php echo $result['pep_reason'];?>");
			$('#smoke').val("<?php echo $result['smoke'];?>");
			$('#alcohol').val("<?php echo $result['alcohol'];?>");	
			
			$("#tb").val("<?php echo $result['tb']; ?>");
			
			if($("#tb").val()==1){
				$("#tbphase_view").show();
				$("#tbphase").val("<?php echo $result['tbphase']; ?>");
				$("#fromphase").val("<?php echo $result['startphase']; ?>");
				$("#tophase").val("<?php echo $result['endphase']; ?>");
				
				 if($("#tbphase").val() ==3) {
		   	     	$("#fromphase_view").hide();
				    $("#tophase_view").show();
				 } 
				 else if($("#tbphase").val()==0){
				 	$("#fromphase_view").hide();
				 	$("#tophase_view").hide();
				 }else {
					$("#fromphase_view").show();
				    $("#tophase_view").show();
					$("#transfer_source").attr("value",'');
			     }
			}

				

			//Function to display tb phases
		   $(".tb").change(function() {
		   	    var tb = $(this).val();
		   	     if(tb == 1) {
				    $("#tbphase_view").show();
				 } 
				 else {
					$("#tbphase_view").hide();
					$("#fromphase_view").hide();
				 	$("#tophase_view").hide();
					$("#tbphase").attr("value",'0');
					$("#fromphase").attr("value",'');
		   	        $("#tophase").attr("value",'');
			     }
		   });
		   
		   $("#current_status").change(function(){
		   	 $("#status_started").datepicker('setDate', new Date());
		   });
		   
		   //Function to display tbphase dates
		   $(".tbphase").change(function() {
		   	    var tbpase = $(this).val();
		   	    $("#fromphase").attr("value",'');
		   	    $("#tophase").attr("value",'');
		   	     if(tbpase ==3) {
		   	     	$("#fromphase_view").hide();
				    $("#tophase_view").show();
				    $("#tb").val(0);
				 } 
				 else if(tbpase==0){
				 	$("#fromphase_view").hide();
				 	$("#tophase_view").hide();
				 }else {
					$("#fromphase_view").show();
				    $("#tophase_view").show();
			     }
		   });
		   
		   //Function to display datepicker for tb fromphase
		   $("#fromphase").datepicker({
					maxDate : "0D",
					dateFormat : $.datepicker.ATOM,
					changeMonth : true,
					changeYear : true
			});
			
			//Function to display datepicker for tb tophase
			$("#tophase").datepicker({
					dateFormat : $.datepicker.ATOM,
					changeMonth : true,
					changeYear : true
			});
			
			//Function to calculate date ranges for tb stages
			$("#fromphase").change(function(){
				  var from_date=$(this).val();
				  var new_date=new Date(from_date);
				  var to_date=new Date();
				  var tbphase=$(".tbphase").val();
				  if(tbphase==1){
				  	//Intensive
				  	 var numberOfDaysToAdd=56;
				  }else if(tbphase==2){
				  	//Continuation
				  	 var numberOfDaysToAdd=112;
				  }
				  to_date.setDate(new_date.getDate() + numberOfDaysToAdd);
				  $("#tophase").datepicker('setDate', new Date(to_date));
			});
			
			//Function to enable textareas for other chronic illnesses
			$("#other_other").change(function() {
					var other = $(this).is(":checked");
					if(other){
						$("textarea[name='other_chronic']").not(this).removeAttr("disabled");
					}else{
						$("textarea[name='other_chronic']").not(this).attr("disabled", "true");
					}
			});
			
			//Function to enable textareas for other allergies
			$("#other_drugs_box").change(function() {
					var other = $(this).is(":checked");
					if(other){
						$("textarea[name='other_drugs']").not(this).removeAttr("disabled");
					}else{
						$("textarea[name='other_drugs']").not(this).attr("disabled", "true");
					}
			});
			
			//Function to enable textareas for other allergies
			$("#other_allergies").change(function() {
					var other = $(this).is(":checked");
					if(other){
						$("textarea[name='other_allergies_listing']").not(this).removeAttr("disabled");
					}else{
						$("textarea[name='other_allergies_listing']").not(this).attr("disabled", "true");
					}
			});
			
			//Function to enable textareas for support group
			$("#support_group").change(function() {
					var other = $(this).is(":checked");
					if(other){
						$("textarea[name='support_group_listing']").not(this).removeAttr("disabled");
					}else{
						$("textarea[name='support_group_listing']").not(this).attr("disabled", "true");
					}
			});
			
			//Attach date picker for date of enrollment
			$("#enrolled").datepicker({
					yearRange : "-30:+0",
					maxDate : "0D",
					dateFormat : $.datepicker.ATOM,
					changeMonth : true,
					changeYear : true
			});
			$("#enrolled").val("<?php echo $result['date_enrolled'] ?>");
			$("#current_status").val("<?php echo $result['current_status'] ?>");
			$("#status_started").val("<?php echo $result['status_change_date'] ?>");
			$("#source").val("<?php echo $result['source'] ?>");			
			
			//Select drug prophylaxis selected
			var drug_prophylaxis="<?php echo $result['drug_prophylaxis'];?>";
			
				if(drug_prophylaxis != null || drug_prophylaxis != " ") {
					var prophylaxis = drug_prophylaxis.split(',');
					for(var i = 0; i < prophylaxis.length; i++) {
						$("select#drug_prophylaxis").multiselect("widget").find(":checkbox[value='" + prophylaxis[i] + "']").each(function() {
	                       $(this).click();
	                    });
					}
				}
                                
                                $.each(drug_prophylaxis,function(i,v){
					if(v==1){
						//disable 2
						$("select#drug_prophylaxis").multiselect("widget").find(":checkbox[value='1']").each(function(){
						  $("select#drug_prophylaxis").multiselect("widget").find("input[value='2']").attr("disabled",true);
						});
					}else if(v==2){
						//disable 1
						$("select#drug_prophylaxis").multiselect("widget").find(":checkbox[value='1']").each(function(){
						   $("select#drug_prophylaxis").multiselect("widget").find("input[value='1']").attr("disabled",true); 
						});
					}
				});
                                
			
			var service_name="<?php echo $result['service_name'];?>";
			if(service_name==="PEP"){
				$("#pep_reason_listing").show();
				$("#who_listing").hide();
			}
			
			$("#service").val("<?php echo $result['service'] ?>");
			$("#service_started").val("<?php echo $result['start_regimen_date'] ?>");
			
			$("#regimen").val("<?php echo $result['start_regimen'] ?>");
			$("#current_regimen").val("<?php echo $result['current_regimen'] ?>");
			
			//Attach date picker for date of status change
			$("#status_started").datepicker({
					yearRange : "-30:+0",
					dateFormat : $.datepicker.ATOM,
					changeMonth : true,
					maxDate : "0D",
					changeYear : true
			});
			// remove the validator class error
	             $("input,select").on('change',function(i,v){
                                var value=$(this).val();
                                var id=this.id;
                                if(value !=''){
                                $('#'+id).validationEngine('hide');
                            }
                        });
			//Attach date picker for date of start regimen 
			$("#service_started").datepicker({
					yearRange : "-30:+0",
					dateFormat : $.datepicker.ATOM,
					changeMonth : true,
					changeYear : true,
					maxDate : "0D"
			});
			
			//Function to display transfer from list if patient source is(transfer in)
				$("#source").change(function() {
					var selected_value = $(this).val();
					if(selected_value == 3) {
						$("#patient_source_listing").show();
					} else {
						$("#patient_source_listing").hide();
						$("#transfer_source").attr("value",'');
					}
				});
				
		   //Function to display Regimens in this line
		   $("#service").change(function() {
		   	$("#regimen option").remove();
		   	$("#current_regimen option").remove();
                          var service_line = $(this).val();
                          if($("#service option[value='"+service_line+"']").text()==="ART" || $("#service option[value='"+service_line+"']").text()==="PMTCT"){
                               $("#servicestartedcontent").show();
                          }else{
                          $("#service_started").val("<?php echo date('Y-m-d');?>");
		   	  $("#servicestartedcontent").show();  
                      }
		   	  if($("#service option[value='"+service_line+"']").text()==="PEP"){
		   	  	$("#pep_reason_listing").show();
		   	  	$("#who_listing").hide();
		   	  }else if($("#service option[value='"+service_line+"']").text()==="OI Only"){
		   	  	//$("#service_started").val("");
		   	  	 $("#service_started").val("<?php echo date('Y-m-d');?>");

		   	  	$("#servicestartedcontent").show();
                                $("#pep_reason_listing").hide();
                            }else{
		   	  	$("#pep_reason_listing").hide();
		   	  	$("#pep_reason").val(0);
		   	  	$("#who_listing").show();
		   	  	$("#who_stage").val(0);   
		   	  }
                          
                      
		   	  var link=base_url+"regimen_management/getRegimenLine/"+service_line;
				$.ajax({
				    url: link,
				    type: 'POST',
				    dataType: "json",
				    success: function(data) {
				        $("#regimen,#current_regimen").append($("<option></option>").attr("value",'').text('--Select One--'));	
				    	$.each(data, function(i, jsondata){
				    		$("#regimen,#current_regimen").append($("<option></option>").attr("value",jsondata.id).text(jsondata.Regimen_Code+" | "+jsondata.Regimen_Desc));
				    	});
				    }
				 });
		   });
		   
		   $("#next_appointment_date").datepicker({
	         yearRange : "-30:+0",
	         dateFormat : $.datepicker.ATOM,
	         changeMonth : true,
	         changeYear : true
	       });
	       
	       $("#next_appointment_date").val("<?php echo $result['nextappointment'];?>");
	       $("#prev_appointment_date").val("<?php echo $result['nextappointment'];?>");
	       
	       var appointment=$("#next_appointment_date").val();
	       var days = getDays(appointment);
	       //if(days>=0){
	       $('#days_to_next').attr("value", days);
	      // }
	       
	       $("#next_appointment_date").change(function(){
	       	    var appointment=$(this).val();
	       	    var days = getDays(appointment);
	       	    $('#days_to_next').attr("value", days);
	       });
	       
	       $("#days_to_next").change(function() {
	           var days = $("#days_to_next").attr("value");
	           var base_date = new Date();
	           var appointment_date = $("#next_appointment_date");
	           var today = new Date(base_date.getFullYear(), base_date.getMonth(), base_date.getDate());
	           var today_timestamp = today.getTime();
	           var appointment_timestamp = (1000 * 60 * 60 * 24 * days) + today_timestamp;
	           appointment_date.datepicker("setDate", new Date(appointment_timestamp));
	       });
	       
	       
	       //Function to display tranfer From	
	       if($("#source").val()==3){
	       	$("#patient_source_listing").show();
	       }       
	       $("#transfer_source").val("<?php echo $result['transfer_from']; ?>");
	       
	       //Function to check if female is pregnant
			$("#gender").change(function() {
					var selected_value = $(this).attr("value");
					//if female, display the prengancy selector
					if(selected_value == 2) {
						//If female show pregnant container
						$('#pregnant_view').slideDown('slow', function() {

						});
					} else {
						//If male do not show pregnant container
						$('#pregnant_view').slideUp('slow', function() {

						});
					}
			});
				
		});
			function getMSQ() {
			  var weight = $('#current_weight').attr('value');
			  var height = $('#current_height').attr('value');
			  var MSQ = Math.sqrt((parseInt(weight) * parseInt(height)) / 3600);
			  $('#current_bsa').attr('value', MSQ);
			}
		
			function getStartMSQ() {
			  var weight = $('#start_weight').attr('value');
			  var height = $('#start_height').attr('value');
			  var MSQ = Math.sqrt((parseInt(weight) * parseInt(height)) / 3600);
			  $('#start_bsa').attr('value', MSQ);
			}
		
			function getDays(dateString) {
		        var base_date = new Date();
		        var today = new Date(base_date.getFullYear(), base_date.getMonth(), base_date.getDate());
		        var today_timestamp = today.getTime();
		        var one_day = 1000 * 60 * 60 * 24;
		        var appointment_timestamp = new Date(Date.parse(dateString, "YYYY/MM/dd")).getTime();
		        var difference = appointment_timestamp - today_timestamp;
		        var days_difference = Math.ceil(difference / one_day);
		        return (days_difference-1);
		    }

		
		
			function getStartAge(dateString, baseDate) {
	            var today = new Date(baseDate);
	            var birthDate = new Date(dateString);
	            var age = today.getFullYear() - birthDate.getFullYear();
	            var m = today.getMonth() - birthDate.getMonth();
	                if(m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
	                 age--;
	                }
	                if(isNaN(age)) {
	                 return "N/A";
	                }
	                return age;
	        }
	        
	        function getAge(dateString) {
	           var today = new Date();
	           var birthDate = new Date(dateString);
	           var age = today.getFullYear() - birthDate.getFullYear();
	           var m = today.getMonth() - birthDate.getMonth();
	              if(m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
	                age--;
	              }
	              if(isNaN(age)) {
	                return "N/A";
	              }
	              return age;
	        }
                
	        //Function to validate required fields
	        function processData(form) {
	          var form_selector = "#" + form;
	          var validated = $(form_selector).validationEngine('validate');
	            var family_planning = $("select#family_planning").multiselect("getChecked").map(function() {
					return this.value;
				}).get();
				var other_illnesses = $("select#other_illnesses").multiselect("getChecked").map(function() {
					return this.value;
				}).get();
				var drug_allergies=$("select#drug_allergies").multiselect("getChecked").map(function() {
					return this.value;
				}).get();
				var drug_prophylaxis=$("select#drug_prophylaxis").multiselect("getChecked").map(function() {
					return this.value;
				}).get();
				$("#family_planning_holder").val(family_planning);
				$("#other_illnesses_holder").val(other_illnesses);
				$("#drug_allergies_holder").val(drug_allergies);
				$("#drug_prophylaxis_holder").val(drug_prophylaxis);
				
				if($("#original_patient_number").val()!=$("#patient_number").val()){
	            	    var base_url="<?php echo base_url();?>";
						var link=base_url+"patient_management/update_visit";
						$.ajax({
						    url: link,
						    type: 'POST',
						    dataType : 'json',
						    data: {
						    	original_patient_number : $('#original_patient_number').val(),
						    	patient_number : $('#patient_number').val()
						     },
						    success: function() {
						       bootbox.alert("<h4>Updates</h4>\n\<hr/><center>Patient Visits Updated</center>");					         
						    }
						});	    	
	             }
				
	            if(!validated) {
                   return false;
	            }else{
	            	$(".btn").attr("disabled","disabled");
	            	return true;
	            }  
	         }
		</script>
	</head>
	<body>
<div class="full-content" style="background:#FF9">
	<div id="sub_title" >
		<a href="<?php  echo base_url().'patient_management ' ?>">Patient Listing </a> <i class=" icon-chevron-right"></i><a href="<?php  echo base_url().'patient_management/viewDetails/'.$result['id'] ?>"><?php echo strtoupper($result['first_name'].' '.$result['other_name'].' '.$result['last_name']) ?></a> <i class=" icon-chevron-right"></i><strong>Edit details</strong>
		<hr size="1">
	</div>
	<h3>Edit Patient Details
	<div style="float:right;margin:5px 40px 0 0;">
		(Fields Marked with <b><span class='astericks'>*</span></b> Asterisks are required)
	</div></h3>

	<form id="edit_patient_form" method="post"  action="<?php $record=$result['id']; echo base_url() . 'patient_management/update/'.$record; ?>" onsubmit="return processData('edit_patient_form')" >
	<div class="column" id="columnOne">
		<fieldset>
			<legend>
				Patient Information &amp; Demographics
			</legend>
			<div class="max-row">
				<div class="mid-row">
					<label> Medical Record No.</label>
					<input type="text" name="medical_record_number" id="medical_record_number" value="">
				</div>
				<div class="mid-row">
					<label> <span class='astericks'>*</span>Patient Number CCC </label>
					<input type="text" name="patient_number" id="patient_number" class="validate[required]">
					<input type="hidden" name="original_patient_number" id="original_patient_number">
				</div>
			</div>
			<div class="max-row">
				<label><span class='astericks'>*</span>Last Name</label>
				<input  type="text"name="last_name" id="last_name" class="validate[required]">
			</div>
			<div class="max-row">
				<div class="mid-row">
					<label><span class='astericks'>*</span>First Name</label>
					<input type="text"name="first_name" id="first_name" class="validate[required]">
				</div>

				<div class="mid-row">
					<label>Other Name</label>
					<input type="text"name="other_name" id="other_name">
				</div>
			</div>
			<div class="max-row">
				<div class="mid-row">
					<label><span class='astericks'>*</span>Date of Birth</label>
					<input type="text"name="dob" id="dob" class="validate[required]">
				</div>
				<div class="mid-row">
					<label> Place of Birth </label>
					<select name="pob" id="pob">
						<option value=" ">--Select--</option>
						<?php
						foreach ($districts as $district) {
							echo "<option value='" . $district['id'] . "'>" . $district['Name'] . "</option>";
						}
						?>
					</select>
				</div>
			</div>

			<div class="max-row">
				<div class="mid-row">
					<label><span class='astericks'>*</span>Gender</label>
					<select name="gender" id="gender" class="validate[required]">
						<option value=" ">--Select--</option>
						<?php
						foreach ($genders as $gender) {
							echo "<option value='" . $gender['id'] . "'>" . $gender['name'] . "</option>";
						}
						?>
					</select>
				</div>
				<div id="pregnant_view" class="mid-row" style="display:none;">
					<label id="pregnant_container"> Pregnant?</label>
					<select name="pregnant" id="pregnant">
						<option value="0">No</option><option value="1">Yes</option>
					</select>
				</div>
			</div>
			<div class="max-row">
				<div class="mid-row">
					<label >Start Age(Years)</label>
					<input type="text" id="start_age" disabled="disabled"/>
				</div>
				<div class="mid-row">
					<label >Current Age(Years)</label>
					<input type="text" id="age" disabled="disabled"/>
				</div>
			</div>
			<div class="max-row">
				<div class="mid-row">
					<label >Start Weight (KG)</label>
					<input type="text"name="start_weight" id="start_weight">
				</div>
				<div class="mid-row">
					<label>Current Weight (KG) </label>
					<input type="text"name="current_weight" id="current_weight">
				</div>
			</div>
			<div class="max-row">
				<div class="mid-row">
					<label > Start Height (CM)</label>
					<input type="text"name="start_height" id="start_height" onblur="getStartMSQ()">
				</div>
				<div class="mid-row">
					<label > Current Height (CM)</label>
					<input  type="text"name="current_height" id="current_height" onblur="getMSQ()">
				</div>
			</div>
			<div class="max-row">
				<div class="mid-row">
					<label > Start Body Surface Area  <br/> (MSQ)</label>
					<input type="text" name="start_bsa" id="start_bsa" value="" >
				</div>
				<div class="mid-row">
					<label > Current Body Surface Area (MSQ)</label>
					<input type="text" name="current_bsa" id="current_bsa" value="" >
				</div>
			</div>
			<div class="max-row">
				<div class="mid-row">
				<label> Patient's Phone Contact(s)</label>
				<input  type="text"  name="phone" id="phone" value="" class="phone" placeholder="e.g 0722123456">
			    </div>
				<div class="mid-row">
				<label > Receive SMS Reminders</label>
				<input  type="radio"  name="sms_consent" value="1" id="sms_yes">
				    Yes
				  <input  type="radio"  name="sms_consent" value="0" id="sms_no">
					No
				</div>
			</div>

			<div class="max-row">
				<label> Patient's Physical Contact(s)</label>
				<textarea name="physical" id="physical" value=""></textarea>
			</div>
			<div class="max-row">
				<label> Patient's Alternate Contact(s)</label>
				<textarea name="alternate" id="alternate" value=""></textarea>
			</div>
			<div class="max-row">
				<label>Does Patient belong to any support group?</label>
				<label>Yes
					<input type="checkbox" name="support_group" id="support_group" value="">
				</label>

				<div class="list">
					List Them
				</div>
				<textarea class="list_area" name="support_group_listing" id="support_group_listing"></textarea>
			</div>

	</div>

	<div class="column" id="colmnTwo">
		<fieldset>
			<legend>
				Program History
			</legend>
			<div class="max-row">
				<label  id="tstatus"> Partner Status</label>
				<select name="partner_status" id="partner_status" >
					<option value="0" selected="selected">No Partner</option>
					<option value="1" > Concordant</option>
					<option value="2" > Discordant</option>
				</select>

			</div>
			<div class="max-row">
				<div class="mid-row">
					<label id="dcs" >Disclosure</label>
					<input  type="radio"  name="disclosure" value="1" id="disclosure_yes">
					Yes
					<input  type="radio"  name="disclosure" value="0" id="disclosure_no">
					No
				</div>
			</div>
			<div class="max-row">
				<label>Family Planning Method</label>
				<input type="hidden" id="family_planning_holder" name="family_planning_holder" />
				<select name="family_planning" id="family_planning" multiple="multiple" style="width:400px;"  >
					<?php
					foreach ($family_planning as $fplan) {
						echo "<option value='" . $fplan['indicator'] . "'>" ." ".$fplan['name'] . "</option>";
					}
					?>
				</select>

			</div>
			<div class="max-row">
				<label>Does Patient have other Chronic illnesses</label>
				<input type="hidden" id="other_illnesses_holder" name="other_illnesses_holder" />
				<select name="other_illnesses" id="other_illnesses"  multiple="multiple"  style="width:400px;" >
					<?php
					foreach ($other_illnesses as $other_illness) {
						echo "<option value='" . $other_illness['indicator'] . "'>" ." ".$other_illness['name'] . "</option>";
					}
					?>
				</select>
			</div>
			<div class="max-row">
				If <b>Other Illnesses</b>
					<br/>
					Click Here
					<input type="checkbox" name="other_other" id="other_other" value="">
					<br/>
					List Them Below (Use Commas to separate)
				<textarea  name="other_chronic" id="other_chronic"></textarea>
			</div>
			<div class="max-row">
				<label> List Other Drugs Patient is Taking </label>
				<label>Yes
					<input type="checkbox" name="other_drugs_box" id="other_drugs_box" value="">
				</label>

				<label>List Them</label>
				<textarea name="other_drugs" id="other_drugs"></textarea>
			</div>
			<div class="max-row">
				<label>Drugs Allergies/ADR</label>
				<input type="hidden" id="drug_allergies_holder" name="drug_allergies_holder" />
				<select name="drug_allergies" id="drug_allergies"  multiple="multiple" style="width:400px;">
					<?php
					    foreach($drugs as $drug){
							echo "<option value='-".$drug['id']."-'>"." ".$drug['Drug']."</option>";
						}
					?>	
				</select>
			</div>
			<div class="max-row">
				<label>Does Patient have any other Drugs Allergies/ADR not listed?</label>

				<label>Yes
					<input type="checkbox" name="other_allergies" id="other_allergies" value="">
				</label>

				<label>List Them</label>
				<textarea class="list_area" name="other_allergies_listing" id="other_allergies_listing"></textarea>
			</div>

			<div class="max-row">
				<div class="mid-row">
					<label > Does Patient Smoke?</label>
					<select name="smoke" id="smoke">
						<option value="0" selected="selected">No</option>
						<option value="1">Yes</option>
					</select>
				</div>
				<div class="mid-row">
					<label> Does Patient Drink Alcohol?</label>
					<select name="alcohol" id="alcohol">
						<option value="0" selected="selected">No</option>
						<option value="1">Yes</option>
					</select>
				</div>
			</div>
				<div class="max-row">
					<div class="mid-row">
						<label> Has Patient been <br/>tested for TB?</label>
						<select name="tested_tb" id="tested_tb" class="tested_tb">
							<option value="0">No</option>
							<option value="1">Yes</option>
						</select>
					</div>
				</div>

			<div class="max-row">
				<div class="mid-row">
					<label> Does Patient Have TB?</label>
					<select name="tb" id="tb" class="tb">
						<option value="0" selected="selected">No</option>
						<option value="1">Yes</option>
					</select>
				</div>
				<div class="mid-row" id="tbphase_view" style="display:none;">
					<label id="tbstats"> TB Phase</label>
					<select name="tbphase" id="tbphase" class="tbphase">
						<option value="0" selected="selected">--Select One--</option>
						<option value="1">Intensive</option>
						<option value="2">Continuation</option>
						<option value="3">Completed</option>
					</select>
				</div>
			</div>
			<div class="max-row">
				<div class="mid-row" id="fromphase_view" style="display:none;">
					<label id="ttphase">Start of Phase</label>
					<input type="text" name="fromphase" id="fromphase" value=""/>
				</div>
				<div class="mid-row" id="tophase_view" style="display:none;">
					<label id="endp">End of Phase</label>
					<input type="text" name="tophase" id="tophase" value=""/>
				</div>
			</div>
			<div class="max-row">
				<div class="mid-row">
				<label> Date of Next Appointment</label>
				<input type="text" name="next_appointment_date" id="next_appointment_date"  style="color:red"/>
				<input type="hidden" name="prev_appointment_date" id="prev_appointment_date" />
				</div>
				<div class="mid-row">
				<label> Days to Next Appointment</label>
				<input  type="text"name="days_to_next" id="days_to_next" style="color:red">
				</div>								
			</div>
		</fieldset>
	</div>
	<div class="column" id="columnThree">
		<fieldset>
			<legend>
				Patient Information
			</legend>
			<div class="max-row">
				<label><span class='astericks'>*</span>Date Patient Enrolled</label>
				<input type="text" name="enrolled" id="enrolled" value="" class="validate[required]">
			</div>
			<div class="max-row">
				<label><span class='astericks'>*</span>Current Status</label>
				<select name="current_status" id="current_status" class="validate[required] red">
					<option value="">--Select--</option>
					<?php
					foreach ($statuses as $status) {
						echo "<option value='" . $status['id'] . "'>" . $status['Name'] . "</option>";
					}
					?>
				</select>
			</div>
			<div class="max-row">
				<label class="status_started" >Date of Status Change</label>
				<input type="text" name="status_started" id="status_started" value="" >
			</div>
			<div class="max-row">
				<label><span class='astericks'>*</span>Source of Patient</label>
				<select name="source" id="source" class="validate[required]">
					<option value="">--Select--</option>
					<?php
					foreach ($sources as $source) {
						echo "<option value='" . $source['id'] . "'>" . $source['Name'] . "</option>";
					}
					?>
				</select>
			</div>
			<div id="patient_source_listing" class="max-row" style="display:none;">
				<label> Transfer From</label>
				<select name="transfer_source" id="transfer_source" >
					<option value="">--Select--</option>
					<?php
					foreach ($facilities as $facility) {
						echo "<option value='" . $facility['facilitycode'] . "'>" . $facility['name'] . "</option>";
					}
					?>
				</select>
			</div>
			<div class="max-row">
				<label><span class='astericks'>*</span>Type of Service</label>
				<select name="service" id="service" class="validate[required]">
					<option value="">--Select--</option>
					<?php
					foreach ($service_types as $service_type) {
						echo "<option value='" . $service_type['id'] . "'>" . $service_type['Name'] . "</option>";
					}
					?>
				</select> </label>
				</select>
			</div>
			 <div class="max-row" id="pep_reason_listing" style="display:none;">
							<label>PEP Reason</label>
							<select name="pep_reason" id="pep_reason">
								<option value="">--Select--</option>
								<?php
								    foreach($pep_reasons as $reason){
										echo "<option value='".$reason['id']."'>".$reason['name']."</option>";
									}
								?>	
							</select> </label>
							</select>
			</div>
			<div class="max-row">
				<label id="start_of_regimen"><span class='astericks'>*</span>Start Regimen </label>
				<select name="regimen" id="regimen" class="validate[required] start_regimen" >
					<option value=" ">--Select One--</option>
					<?php
					foreach ($regimens as $regimen) {
						echo "<option value='" . $regimen['id'] . "'>".$regimen['Regimen_Code'] ." | " . $regimen['Regimen_Desc'] . "</option>";
					}
					?>
				</select>
			</div>
			<div class="max-row" id="servicestartedcontent">
				<label id="date_service_started">Start Regimen Date</label>
				<input type="text" name="service_started" id="service_started" value="">
			</div>
			<div class="max-row">
				<label style="color:red;font-weight:bold;">Current Regimen</label>
				<select type="text"name="current_regimen" id="current_regimen" class="validate[required] red">
					<option value="">--Select--</option>
					<?php
					foreach ($regimens as $regimen) {
						echo "<option value='" . $regimen['id'] . "'>".$regimen['Regimen_Code'] ." | " . $regimen['Regimen_Desc'] . "</option>";
					}
					?>
				</select>
			</div>
			<div class="max-row" id="who_listing">
				<label>WHO Stage</label>
					<select name="who_stage" id="who_stage" class="who_stage" >
					   		<option value="">--Select One--</option>
								<?php
								    foreach($who_stages as $stages){
										echo "<option value='".$stages['id']."'>".$stages['name']."</option>";
									}
								?>				
					</select>
			</div>
			<div class="max-row">
				<label>Drug Prophylaxis</label>
				  <input type="hidden" id="drug_prophylaxis_holder" name="drug_prophylaxis_holder" />		
					<select name="drug_prophylaxis" id="drug_prophylaxis" multiple="multiple" style="width:300px;" >
						 <?php
							foreach($drug_prophylaxis as $prohylaxis){
								echo "<option value='".$prohylaxis['id']."'>".$prohylaxis['name']."</option>";
							}
						  ?>	
				   </select>
			</div>
		</fieldset>
	</div>
            <div class="button-bar btn_positioning " style="float: right;">
                 <input form="edit_patient_form" type="submit" class="btn btn-block button_size " value="Update Patient Info" name="save"/>
                
			
	</div>

</form>
</div>
</body>
</html>
