<?php
/*
 If not stated otherwise in this file or this component's Licenses.txt file the
 following copyright and licenses apply:

 Copyright 2018 RDK Management

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
*/
?>
<?php
$CloudUIEnable = getStr("Device.DeviceInfo.X_RDKCENTRAL-COM_CloudUIEnable");
?>
<?php include('includes/header.php'); ?>
<!-- $Id: port_forwarding.php 3158 2010-01-08 23:32:05Z slemoine $ -->
<div  id="sub-header">
	<?php include('includes/userbar.php'); ?>
</div><!-- end #sub-header -->
<?php include('includes/nav.php'); ?>
<?php include('includes/utility.php'); ?>
<?php 
$PFEnable = getStr("Device.NAT.X_Comcast_com_EnablePortMapping");
$productLink = getStr("Device.DeviceInfo.X_RDKCENTRAL-COM_Syndication.RDKB_UIBranding.CloudUI.link");
?>
<script type="text/javascript">
$(document).ready(function() {
	gateway.page.init("Advanced > Port Forwarding", "nav-port-forwarding");
	$("#pf_switch").radioswitch({
		id: "forwarding-switch",
		radio_name: "forwarding",
		id_on: "forwarding_enabled",
		id_off: "forwarding_disabled",
		title_on: "<?php echo _("Enable port forwarding")?>",
		title_off: "<?php echo _("Disable port forwarding")?>",
		state: <?php echo ($PFEnable === "true" ? "true" : "false"); ?> ? "on" : "off"
	});
	$("a.confirm").off('click');
	function setupDeleteConfirmDialogs() {
        /*
         * Confirm dialog for delete action
         */             
        $("a.confirm").click(function(e) {
            e.preventDefault();            
            var href = $(this).attr("href");
            var message = ($(this).attr("title").length > 0) ? "<?php echo _("Are you sure you want to")?> " + $(this).attr("title") + "?" : "<?php echo _("Are you sure?")?>";
            jConfirm(
                message
                ,"<?php echo _("Are You Sure?")?>"
                ,function(ret) {
                    if(ret) {
						delVal = href.substring(href.indexOf("=")+1);
						jProgress('<?php echo _("This may take several seconds.")?>',60);
						$.ajax({
							type:"POST",
							url:"actionHandler/ajax_port_forwarding.php",
							data:{del:delVal},
							success:function(){
								jHide();
								window.location.reload();
							},
							error:function(){
								jHide();
								jAlert("<?php echo _("Error! Please try later!")?>");
							}
						});
                    }
                }
			);
        });
    }
	var isUFWDDisabled = $("#pf_switch").radioswitch("getState").on === false;
	if(isUFWDDisabled) { 
		$("a.confirm").off('click');
		$('.module *').not(".radioswitch_cont, .radioswitch_cont *").addClass("disabled");
		$("#forwarding-items").prop("disabled",true).addClass("disabled");
		$("a.btn").addClass("disabled").click(function(e){e.preventDefault();});
		$(':checkbox').addClass("disabled").prop("disabled", true);
	}
	else{
		setupDeleteConfirmDialogs();
	}
	$("#pf_switch").change(function() {
		var UFWDStatus = $("#pf_switch").radioswitch("getState").on ? "Enabled" : "Disabled";
		var isUFWDDisabled = $("#pf_switch").radioswitch("getState").on === false;
		if(isUFWDDisabled) { 
			$("a.confirm").off('click');
			$('.module *').not(".radioswitch_cont, .radioswitch_cont *").addClass("disabled");
			$("#forwarding-items").prop("disabled",true).addClass("disabled");
			$("a.btn").addClass("disabled").click(function(e){e.preventDefault();});
			$(':checkbox').addClass("disabled").prop("disabled", true);
		}
		else{
			$('.module *').not(".radioswitch_cont, .radioswitch_cont *").removeClass("disabled");
			$("#forwarding-items").prop("disabled",false).removeClass("disabled");
			$("a.btn").removeClass("disabled").click(function(e){e.preventDefault();});
			setupDeleteConfirmDialogs();
			$(':checkbox').removeClass("disabled").prop("disabled", false);
		}
		jProgress("<?php echo _("This may take several seconds.")?>",60);
		$.ajax({
			type:"POST",
			url:"actionHandler/ajax_port_forwarding.php",
			data:{set:"true",UFWDStatus:UFWDStatus},
			success:function(){
				jHide();
				/*results=eval("("+result+")");
				if (UFWDStatus!=results){ 
					jAlert("Backend Error!");
					$("input[name='forwarding']").each(function(){
						if($(this).val()==results){$(this).parent().addClass("selected");$(this).prop("checked",true);}
						else{$(this).parent().removeClass("selected");$(this).prop("checked",false);}
					});
				}*/
				var isUFWDDisabled = $("#pf_switch").radioswitch("getState").on === false;
				if(isUFWDDisabled){
					$("a.confirm").off('click');
					$('.module *').not(".radioswitch_cont, .radioswitch_cont *").addClass("disabled");
					$("#forwarding-items").prop("disabled",true).addClass("disabled");
					$("a.btn").addClass("disabled").click(function(e){e.preventDefault();});
					$(':checkbox').addClass("disabled").prop("disabled", true);
				}else{
					$('.module *').not(".radioswitch_cont, .radioswitch_cont *").removeClass("disabled");
					$("#forwarding-items").prop("disabled",false).removeClass("disabled");
					$("a.btn").removeClass("disabled").off('click');
					setupDeleteConfirmDialogs();
					$(':checkbox').removeClass("disabled").prop("disabled", false);
				}
				//window.location.reload();
			},
			error: function(){
				jHide();
				jAlert("<?php echo _("Error! Please try later!")?>");
			}
		}); //end of ajax
	});//end of change
	$("input[name='PortActive']").change(function(){
		var isChecked=$(this).is(":checked");
		var id=$(this).attr("id").split("_");
		id=id[1];
		jProgress('<?php echo _("This may take several seconds.")?>',60);
		$.ajax({
			type:"POST",
			url:"actionHandler/ajax_port_forwarding.php",
			data:{active:"true",isChecked:isChecked,id:id},
			success:function(){
				jHide();
			},
			error:function(){
				jHide();
				jAlert("<?php echo _("Error! Please try later!")?>");
			}
		});
	});
});
</script>
<?php if($CloudUIEnable == "true"){ ?>
<div  id="content">
	<h1><?php echo _("Advanced > Port Forwarding")?></h1>
	<div  id=forwarding-items>
		<div class="module data">
			<div id="content" style="text-align: center;width: auto;">
				<br>
				<h3><?php echo sprintf(_("Managing your home network settings is now easier than ever.<br>Visit <a href='http://%s'>%s</a> to set up port forwards, among many other features and settings."),$productLink, $productLink)?></h3>
				<br>
			</div>
		</div> <!-- end .module -->
	</div>
</div><!-- end #content -->
<?php } else { ?>
<div  id="content">
	<h1><?php echo _("Advanced > Port Forwarding")?></h1>
	<div  id="educational-tip">
		<p class="tip"><?php echo _("Manage external access to specific ports on your network.")?></p>
		<p class="hidden"><?php echo _("Port forwarding permits communications from external hosts by forwarding them to a particular port.")?></p>
		<p class="hidden"><?php echo _("Select <strong>Enable</strong> to manage external access to specific ports on your network.")?></p>
		<p class="hidden"><?php echo _("Click <strong>+ADD SERVICE</strong> to add new port forwarding rules.")?></p>
		<p class="hidden"><?php echo _("Port forwarding settings can affect the Gateway's performance.")?></p>
	</div>
	<div class="module">
		<div class="select-row">
    		<span class="readonlyLabel label"><?php echo _("Port Forwarding:")?></span>
			<span id="pf_switch"></span>
    	</div>
	</div>
	<div  id=forwarding-items>
		<div class="module data">
		<h2><?php echo _("Port Forwarding")?></h2>
			<p class="button"><a tabindex='0'  href="port_forwarding_add.php" class="btn"  id="add-service"><?php echo _("+ ADD SERVICE")?></a></p>
			<table class="data" summary="This table list available port forwarding entries">
				<tr>
					<th id="service-name"><?php echo _("Service Name")?></th>
					<th id="service-type"><?php echo _("Type")?></th>
					<th id="start-port"><?php echo _("Start Port")?></th>
					<th id="end-port"><?php echo _("End Port")?></th>
					<th id="server-ip"><?php echo _("Server IPv4")?></th>
					<th id="server-ipv6"><?php echo _("Server IPv6")?></th>
					<th id="active"><?php echo _("Active")?></th>
					<th id="edit-button">&nbsp;</th>
					<th id="delete-button">&nbsp;</th>
				</tr>
				<?php 				
				$rootObjName    = "Device.NAT.PortMapping.";
				$paramNameArray = array("Device.NAT.PortMapping.");
				$mapping_array  = array("LeaseDuration", "InternalPort", "Protocol", "Description",
					                    "ExternalPort", "ExternalPortEndRange", "InternalClient", "X_CISCO_COM_InternalClientV6", "Enable");
				//$EntryNums = getStr("Device.NAT.PortMappingNumberOfEntries");
				$IndexArr  = DmExtGetInstanceIds("Device.NAT.PortMapping.");
				if(0 == $IndexArr[0]){  
				    // status code 0 = success   
					$IndexNums = count($IndexArr) - 1;
				}
				if(!empty($IndexNums)){
					$resArray = getParaValues($rootObjName, $paramNameArray, $mapping_array);
                    if (!empty($resArray)){
						$iclass = "";
					    for ($i=0; $i < $IndexNums; $i++) {
							$resArray[$i]['Description'] = htmlspecialchars($resArray[$i]['Description'], ENT_NOQUOTES, 'UTF-8');
							//zqiu
					    	if (($resArray[$i]['InternalPort'] !== '0') || 
								($resArray[$i]['InternalClient'] === '0.0.0.0') ||
								(strpos($resArray[$i]['InternalClient'],'172.16.12.') !== false)) {
					    		//filter out hs port forwarding entry whose internal port !== 0
					    		continue;
					    	}
	                        $index = $IndexArr[$i+1];
					    	if ($iclass == "") {
					    		$iclass = "odd";
					    	} else {
					    		$iclass = "";
					    	}
					    	if ($resArray[$i]['Protocol'] == "BOTH") {
					    		$resArray[$i]['Protocol'] = "TCP/UDP";
					    	}	
					    	if ($resArray[$i]['Enable'] == "true") {
					    		$checked = "checked";
					    	}
					    	else{
					    		$checked = "";
					    	}
							if ($resArray[$i]['InternalClient'] === '255.255.255.255') {
								$resArray[$i]['InternalClient'] = '';
							}
					    	if ($resArray[$i]['X_CISCO_COM_InternalClientV6'] == 'x')
					    		$resArray[$i]['X_CISCO_COM_InternalClientV6'] = '';
					    	echo "
					    	    <tr class='" . $iclass. "'>
								<td headers='service-name'>" . $resArray[$i]['Description'] . "</td>
								<td headers='service-type'>" . $resArray[$i]['Protocol'] . "</td>
								<td headers='start-port'>" . $resArray[$i]['ExternalPort'] . "</td>
								<td headers='end-port'>" . $resArray[$i]['ExternalPortEndRange'] . "</td>
								<td headers='server-ip'>" . $resArray[$i]['InternalClient'] . "</td>
								<td headers='server-ipv6'>" . $resArray[$i]['X_CISCO_COM_InternalClientV6'] . "</td>
								<td headers='active'><input type=\"checkbox\" id=\"PortActive_$index\" name=\""._("PortActive")."\" $checked /></td>
								<td headers='edit-button'  class=\"edit\"><a tabindex='0' href=\"port_forwarding_edit.php?id=$index\" class=\"btn\"  id=\"edit_$index\">"._("Edit")."</a></td>
								<td headers='delete-button'  class=\"delete\"><a tabindex='0'  href=\"actionHandler/ajax_port_forwarding.php?del=$index\" class=\"btn confirm\" 
								    title=\"".sprintf(_("delete this Port Forwarding service for %s."),$resArray[$i]['Description'])." \" id=\"delete_$index\">x</a></td>
								</tr>
					    	    ";
					    }//end of for
					} //end if empty resArray
				}//end if empty entry nums
				?>
			<tfoot>
				<tr class="acs-hide">
					<td headers="service-name">null</td>
					<td headers="service-type">null</td>
					<td headers="start-port">null</td>
					<td headers="end-port">null</td>
					<td headers="server-ip">null</td>
					<td headers="edit-button">null</td>
					<td headers="delete-button">null</td>
				</tr>
			</tfoot>
			</table>
		</div> <!-- end .module -->
	</div>
</div><!-- end #content -->
<?php } ?>
<?php include('includes/footer.php'); ?>
