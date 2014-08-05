<script>
	$(document).ready(function() {	
		$(".setting_table").find("tr :first").css("width","380px");
		$(".edit_user").live('click',function(event) {
			$("#regimen_edit_tbl").css("display","none");
				var _id=this.id;
				var request=$.ajax({
		    	url: "regimen_management/edit",
		     	type: 'POST',
		     	data: {"id":_id},
		     	dataType: "json",
		     
		    });
		     request.done(function(msg) {
		     	for (var key in msg){
			     	if (msg.hasOwnProperty(key)){
			     		if(key=="regimens"){
			     			
			     			for(var y in msg[key]) {
			     					if (msg[key].hasOwnProperty(y)) {
			     						$("#edit_regimen_id").val(msg[key][y].id);
			     						$("#edit_regimen_code").val(msg[key][y].Regimen_Code);
			     						$("#edit_regimen_desc").val(msg[key][y].Regimen_Desc);
			     						$("#edit_category").attr("value",msg[key][y].Category);
			     						$("#edit_line").val(msg[key][y].Line);
			     						$("#edit_type_of_service").attr("value",msg[key][y].Type_Of_Service);
			     						$("#edit_remarks").val(msg[key][y].Remarks);
			     						$("#edit_regimen_mapping").attr("value",msg[key][y].map);
							     if(msg[key][y].map==0){
							     	$(".all_mappings").show();
							     	$(".semi_mappings").hide();
							     	$(".semi_mappings").attr("name","");
							     	$(".all_mappings").attr("name","regimen_mapping");
							     	$(".all_mappings").attr("value",msg[key][y].map);
							     }else{
							     	$(".all_mappings").hide();
							     	$(".semi_mappings").show();
							     	$(".all_mappings").attr("name","");
							     	$(".semi_mappings").attr("name","regimen_mapping");
							     	$(".semi_mappings").attr("value",msg[key][y].map);
							     }
			     					}	
			     					break;	
			     			}
			     			//$("#edit_form").dialog("open");
			     			$("#regimen_edit_tbl").css("display","block");
			     		}
			     	}
			    }
		     });
		     
		     request.fail(function(jqXHR, textStatus) {
			  bootbox.alert("<h4>Retrieval Error</h4>\n\<hr/><center>Could not retrieve regimen details: </center>" + textStatus );
			});
		    
			
		});
		
		var opts = {
			"closeButton" : true,
			"positionClass" : "toast-bottom-right",
		};

		//function to check drugs for merging
        var arr = [];
	    $('body').on('click', 'table tr .drugcodes',function(){
	        var value = $(this).val(),
	            isChecked = $(this).is(':checked'),
	            index = arr.indexOf(value);

	        if (isChecked && index === -1) {
	            arr.push(value);
	        } else if (!isChecked && index !== -1){
	            arr.splice(index, 1);
	        }
	    });
		
		//Check the drugcodes selected when merge is clicked
		$(".merge_drug").live('click',function(){
		  var test = confirm("Are You Sure?");
		   if(test) {
		   	var counter=0;
			var primary_drug_merge_id = $(this).attr("id");
			var base_url='<?php echo base_url();?>';
			counter=arr.length;
            if(counter>0){
				$.ajax({
	                url: base_url+'regimen_management/merge/'+primary_drug_merge_id,
	                type: 'POST', 
	                data: { 'drug_codes': arr },      
	                success: function(data) {	           
	                     //Refresh Page
	                     location.reload(); 
	                },
	                error: function(){
	                	toastr.error('failed merged!', 'Merging', opts);
	                }
	           });
            }else{
		      	toastr.error('no regimen selected!', 'Merging', opts);
		    }
		    return true;
           } else {
			  return false;
			}
		});
		
		
		//Disable multiple drugs
		$("#disable_mutliple_regimens").live("click",function(event){
			event.preventDefault();
			var count_checked = $("input:checkbox[name='drugcodes']:checked").size();
			var drug_selected = $("input:checkbox[name='drugcodes']:checked")
			if(count_checked>0){
				var test = confirm("Are You Sure?");
				if(test) {//User clicks yes
					var drug_codes=new Array();
					var base_url='<?php echo base_url();?>';
					$("input:checkbox[name='drugcodes']:checked").each(function(){
					    drug_codes.push($(this).val());
		            });
		            $.ajax({
			                url: base_url+'regimen_management/disable',
			                type: 'POST', 
			                data: { 'drug_codes': drug_codes,'multiple':'1' },      
			                success: function(data) {
			                	//Refresh Page
			                    location.reload(); 
			                },
			                error: function(){
			                	toastr.error('Failed to disable!', 'Disabling', opts);
			                }
			           });
		            
		            
				}else {
					return false;
				}
			}else{
				bootbox.alert("<h4>Selection Alert</h4>\n\<hr/><center>You have not selected any drugs to disable</center>");
			}
			
			
		});
		
		//Enable multiple drugs
		$("#enable_mutliple_regimens").live("click",function(event){
			event.preventDefault();
			var count_checked = $("input:checkbox[name='drugcodes']:checked").size();
			var drug_selected = $("input:checkbox[name='drugcodes']:checked")
			if(count_checked>0){
				var test = confirm("Are You Sure?");
				if(test) {//User clicks yes
					var drug_codes=new Array();
					var base_url='<?php echo base_url();?>';
					$("input:checkbox[name='drugcodes']:checked").each(function(){
					    drug_codes.push($(this).val());
		            });
		            $.ajax({
			                url: base_url+'regimen_management/enable',
			                type: 'POST', 
			                data: { 'drug_codes': drug_codes,'multiple':'1' },      
			                success: function(data) {
			                	//Refresh Page
			                    location.reload(); 
			                },
			                error: function(){
			                	toastr.error('failed to enable!', 'Enabling', opts);
			                }
			           });
				}else {
					return false;
				}
			}else{
				bootbox.alert("<h4>Selection Alert</h4>\n\<hr/><center>You have not selected any drugs to enable</center>");
			}
			
			
		});
		
		
		//This loop goes through each table row in the page and applies the necessary modifications
		$.each($(".table_row"), function(i, v) {
			//First get the row id which will be used later
			var row_id = $(this).attr("row_id");
			//This gets the first td element of that row which will be used to add the action links
			var first_td = $(this).find("td:first");
			//Get the width of this td element in integer form (i.e. remove the .px part)
			var width = first_td.css("width").replace("px", "");
			//If the width is less than 200px, extend it to 200px so as to have a more uniform look
			if(width < 200) {
				first_td.css("width", "200px");
			}
			/*
			 //Append the contents of the 'action_panel_parent' to this first td element
			 $($("#action_panel_parent").html()).appendTo(first_td);
			 //Loop through all the links included in the action panel for this td and append the row_id to the end of it
			 $.each($(this).find(".link"), function(i,v){
			 var current_link = $(this).attr("link");
			 var new_link = $(this).attr("link")+row_id;
			 $(this).attr("href",new_link);
			 });
			 */
		});
		//Add a hover listener to all rows
		$(".table_row").hover(function() {
			//When hovered on, make the background color of the row darker and show the action links
			$(this).addClass("hovered");
			$(this).find(".actions_panel").css("visibility", "visible");
		}, function() {
			//When hovered off, reset the background color and hide the action links
			$(this).removeClass("hovered");
			$(this).find(".actions_panel").css("visibility", "hidden");
		});
		
		//count to check which message to display
        var count='<?php echo @$this -> session -> userdata['message_counter']?>';
        var message='<?php echo @$this -> session -> userdata['message']?>';	
	
	if(count == 1) {
	$(".passmessage").slideDown('slow', function() {

	});
	$(".passmessage").append(message);

	var fade_out = function() {
	$(".passmessage").fadeOut().empty();
	}
	setTimeout(fade_out, 5000);
     <?php      
     	//$this -> session -> set_userdata('message_counter', "0");
     	$this -> session -> set_userdata('message', " ");
     ?>

	}
	if(count == 2) {
	$(".errormessage").slideDown('slow', function() {

	});
	$(".errormessage").append(message);

	var fade_out = function() {
	$(".errormessage").fadeOut().empty();
	}
	setTimeout(fade_out, 5000);
     <?php 
     //$this -> session -> set_userdata('message_counter', "0");
     //$this -> session -> set_userdata('message', " ");
     ?>

	}
	});

</script>
<style type="text/css">
	.actions_panel {
		width: 200px;
		margin-top: 5px;
	}
	.hovered td {
		background-color: #E5E5E5 !important;
	}
	a {
		text-decoration: none;
	}
	.enable_user {
		color: green;
		font-weight: bold;
	}
	.disable_user {
		color: red;
		font-weight: bold;
	}
	.edit_user {
		color: blue;
		font-weight: bold;
	}
	.merge_drug{
	    color:green;
		font-weight:bold;	
	}
	.unmerge_drug{
	    color:red;
		font-weight:bold;	
	}
	
	#entry_form,#edit_form{
		background-color:#CCFFFF;
	}
	

</style>



<div id="view_content">
	
	<div class="container-fluid">
	  <div class="row-fluid">	
	    <!-- Side bar menus -->
	    <?php echo $this->load->view('settings_side_bar_menus_v.php'); ?>
	    <!-- SIde bar menus end -->

	    <div class="span12 span-fixed-sidebar">
	      <div class="hero-unit">
	        <a href="#entry_form" role="button" id="new_regimen" class="btn" data-toggle="modal"><i class="icon-plus icon-black"></i>New Regimen</a>
	        <button id="disable_mutliple_regimens" class="btn btn-danger">Disable selected regimens</button>
	      	<button id="enable_mutliple_regimens" class="btn btn-info">Enable selected regimens</button>
	      	<?php echo $regimens;?>
	      </div>
	    </div><!--/span-->
	  </div><!--/row-->


	</div><!--/.fluid-container-->
	
	<div id="entry_form" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="NewRegimen" aria-hidden="true">
		<?php
			$attributes = array('class' => 'input_form');
			echo form_open('regimen_management/save', $attributes);
		?>
		<div class="modal-header">
		    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		    <h3 id="NewRegimen">Regimen details</h3>
		</div>
		<div class="modal-body">
			<?php
			echo validation_errors('<p class="error">', '</p>');
			?>
			<table>
				<tr><td><strong class="label">Regimen Code</strong></td>
					<td><input type="hidden" name="regimen_id" id="regimen_id" class="input" >
						<input type="text" name="regimen_code" id="regimen_code" class="input-xlarge"></td>
					
				</tr>
				<tr><td><strong class="label">Description</strong></td>
					<td>
						<input type="text" name="regimen_desc" id="regimen_desc" class="input-xlarge"></td>
					
				</tr>
				<tr><td><strong class="label">Category</strong></td>
					<td>
						<select class="input-xlarge" id="category" name="category">
							<?php
			foreach($regimen_categories as $regimen_category){
							?>
							<option value="<?php echo $regimen_category -> id;?>"><?php echo $regimen_category -> Name;?></option>
							<?php }?>
						</select>
					</td>
					
				</tr>
				<tr>
					<td><strong class="label">Line</strong></td>
					<td><input type="text" name="line" id="line" class="input-xlarge"></td>
				</tr>
				<tr>
					<td><strong class="label">Type of Service</strong></td>
					<td>
						<select class="input-xlarge" id="type_of_service" name="type_of_service">
							<?php 
								foreach($regimen_service_types as $regimen_service_type){
								if($access_level!="facility_administrator"){
									if($regimen_service_type -> Name!="ART"){
									?>
									<option value="<?php echo $regimen_service_type -> id;?>"><?php echo $regimen_service_type -> Name;?></option>
									<?php  
									}
								}
								elseif($access_level=="facility_administrator") {
									?>
									<option value="<?php echo $regimen_service_type -> id;?>"><?php echo $regimen_service_type -> Name;?></option>
									<?php
								}
							}?>
						</select>
					</td>
					
				</tr>
				<tr><td><strong class="label">Remarks</strong></td>
					<td>
						<textarea name="remarks" id="remarks" class="input-xlarge" rows="4"></textarea>
					</td>			
				</tr>
				  <tr><td><strong class="label">Mapping</strong></td>
					<td>
						<select class="input" id="add_regimen_mapping" name="regimen_mapping">
							<option value="0">--Select One--</option>
							<?php
								$x = 0;
								$prev_cat = '';
								$act_cat = '';
							      foreach ($edit_mappings as $map) {
							      	$category_name = $map['category_name'];
									  if(trim($category_name)==''){
									  	$category_name = 'Others';
									  }
							      	$act_cat = $map['category_id'];
							      	if($x==0){
							      		$prev_cat = $act_cat;
										 echo "<optgroup label='".$category_name."'>";
										 $x++;
							      	}else{
							      		if($prev_cat!=$act_cat){
							      			echo "</optgroup>";
							      			echo "<optgroup label='".$category_name."'>";
											$prev_cat=$act_cat;
							      		}
							      	}
								     echo "<option value='".$map['id']."'>".$map['code']." | ".$map['name']."</option>";
							      }
							?>
						</select>
					</td>	
				</tr>
			</table>
			
		</div>
		
		<div class="modal-footer">
		   <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
		   <input type="submit" value="Save" class="btn btn-primary " />
		</div>
		<?php echo form_close(); ?>
	</div>
	
	<div id="edit_form" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="EditRegimen" aria-hidden="true">
		<?php
		$attributes = array('class' => 'input_form');
		echo form_open('regimen_management/update', $attributes);
		echo validation_errors('<p class="error">', '</p>');
		?>
		<div class="modal-header">
		    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		    <h3 id="EditRegimen">Regimen details</h3>
		</div>
		<div class="modal-body">
		
		<table id="regimen_edit_tbl">
			<tr><td><strong class="label">Regimen Code</strong></td>
				<td><input type="hidden" name="regimen_id" id="edit_regimen_id" class="input" >
					<input type="text" name="regimen_code" id="edit_regimen_code" class="input-xlarge"></td>
				
			</tr>
			<tr><td><strong class="label">Description</strong></td>
				<td>
					<input type="text" name="regimen_desc" id="edit_regimen_desc" class="input-xlarge"></td>
				
			</tr>
			<tr><td><strong class="label">Category</strong></td>
				<td>
					<select class="input-xlarge" id="edit_category" name="category">
						<?php
		foreach($regimen_categories as $regimen_category){
						?>
						<option value="<?php echo $regimen_category -> id;?>"><?php echo $regimen_category -> Name;?></option>
						<?php }?>
					</select>
				</td>
				
			</tr>
			<tr>
				<td><strong class="label">Line</strong></td>
				<td><input type="text" name="line" id="edit_line" class="input-xlarge"></td>
			</tr>
			<tr>
				<td><strong class="label">Type of Service</strong></td>
				<td>
					<select class="input-xlarge" id="edit_type_of_service" name="type_of_service">
						<?php
		foreach($regimen_service_types as $regimen_service_type){
						?>
						<option value="<?php echo $regimen_service_type -> id;?>"><?php echo $regimen_service_type -> Name;?></option>
						<?php }?>
					</select>
				</td>
				
			</tr>
			<tr><td><strong class="label">Remarks</strong></td>
				<td>
					<textarea name="remarks" id="edit_remarks" class="input-xlarge" rows="4"></textarea>
				</td>	
			</tr>
			<tr><td><strong class="label">Mapping</strong></td>
				<td>
				 <select class="input all_mappings" id="edit_regimen_mapping" name="regimen_mapping">
					<option value='0'>-Select One--</option>
					<?php
						$x = 0;
						$prev_cat = '';
						$act_cat = '';
					      foreach ($edit_mappings as $map) {
					      	$act_cat = $map['category_id'];
							$category_name = $map['category_name'];
							if(trim($category_name)==''){
							 $category_name = 'Others';
							}
					      	if($x==0){
					      		$prev_cat = $act_cat;
								 echo "<optgroup label='".$category_name."'>";
								 $x++;
					      	}else{
					      		if($prev_cat!=$act_cat){
					      			echo "</optgroup>";
					      			echo "<optgroup label='".$category_name."'>";
									$prev_cat=$act_cat;
					      		}
					      	}
						     echo "<option value='".$map['id']."'>".$map['code']." | ".$map['name']."</option>";
					      }
					?>
				</select>
				<select class="input semi_mappings" id="edit_regimen_mapping" name="regimen_mapping">
					<option value='0'>-Select One--</option>
					<?php
						$x = 0;
						$prev_cat = '';
						$act_cat = '';
					      foreach ($mappings as $map) {
					      	$act_cat = $map['category_id'];
							$category_name = $map['category_name'];
							if(trim($category_name)==''){
							 $category_name = 'Others';
							}
					      	if($x==0){
					      		$prev_cat = $act_cat;
								 echo "<optgroup label='".$category_name."'>";
								 $x++;
					      	}else{
					      		if($prev_cat!=$act_cat){
					      			echo "</optgroup>";
					      			echo "<optgroup label='".$category_name."'>";
									$prev_cat=$act_cat;
					      		}
					      	}
						     echo "<option value='".$map['id']."'>".$map['code']." | ".$map['name']."</option>";
					      }
					?>
				</select>
				</td>	
			</tr>
		</table>
		
		
		</div>
		<div class="modal-footer">
		   <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
		   <input type="submit" value="Save" id="btn_update_regimen" class="btn btn-primary " />
		</div>
		<?php echo form_close() ; ?>
		
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		var $td = $("#regimen_setting tr td");
		$td.eq(2).css('width','80px');
		console.log($td.eq(2).val())
	})
</script>