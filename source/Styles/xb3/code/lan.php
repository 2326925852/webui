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
<?php include('includes/header.php'); ?>
<!-- $Id: lan.php 3158 2010-01-08 23:32:05Z slemoine $ -->
<div id="sub-header">
	<?php include('includes/userbar.php'); ?>
</div><!-- end #sub-header -->
<?php include('includes/nav.php'); ?>
<?php include('includes/utility.php'); ?>
<?php
$PartnerId = getStr("Device.DeviceInfo.X_RDKCENTRAL-COM_Syndication.PartnerId");
$HomeNetworkControl = getStr("Device.DeviceInfo.X_RDKCENTRAL-COM_Syndication.RDKB_UIBranding.LocalUI.HomeNetworkControl");
function getPort4XHSEnabled() {
	$rootObjName = "Device.X_CISCO_COM_MultiLAN.";
	$paramNameArray = array("Device.X_CISCO_COM_MultiLAN.");
	$mapping_array  = array("PrimaryLANBridge", "PrimaryLANBridgeHSPorts", "HomeSecurityBridge", "HomeSecurityBridgePorts");
	$multiLan = getParaValues($rootObjName, $paramNameArray, $mapping_array);
	if (!empty($multiLan)) {
		$pLanBridgeHSPortEnable = getStr($multiLan[0]["PrimaryLANBridge"].".Port.".$multiLan[0]["PrimaryLANBridgeHSPorts"].".Enable");
		$HSBridgePortEnable = getStr($multiLan[0]["HomeSecurityBridge"].".Port.".$multiLan[0]["HomeSecurityBridgePorts"].".Enable");
		return ($pLanBridgeHSPortEnable === 'false' && $HSBridgePortEnable === 'true');
	}
	return false;
}
$isPort4XHSEnabled = getPort4XHSEnabled();
$rootObjName = "Device.Ethernet.Interface.";
$paramNameArray = array("Device.Ethernet.Interface.");
$mapping_array  = array("Upstream", "Status", "MACAddress", "CurrentBitRate");
$ethernetParam = getParaValues($rootObjName, $paramNameArray, $mapping_array, true);
if(strpos($PartnerId, "sky-") !== false){
	$port_entries = getStr('Device.X_RDK-Central_COM_WanAgent.InterfaceNumberOfEntries');
	for ($i=1;$i<=$port_entries; $i++){
        if("eth3" ==getStr('Device.X_RDK-Central_COM_WanAgent.Interface.'.$i.'.Name')){
            if('true'==getStr('Device.X_RDK-Central_COM_WanAgent.Interface.'.$i.'.Wan.Enable')){
               $reclaim = true;
            }
            else{
               $reclaim = false;
            }
        }
    }
}
?>
<script type="text/javascript">
var o_isPort4XHSEnabled = <?php echo $isPort4XHSEnabled ? 'true' : 'false'; ?>;
function onsavePort4() {
	var postData = {};
	postData.op = "savePort4XHS";
	postData.enable = $("#port4").prop("checked");
	jProgress('<?php echo _('This may take several seconds')?>', 60);
	$.ajax({
		type: 'POST',
		url: 'actionHandler/ajaxSet_hardware_lan.php',
		dataType: 'json',
		data: postData,
		success: function(data) {
			jHide();
			if (data.status != 'success') {
				var str = "<?php echo _('Failed, please try again later.')?>";
				if (data.msg) {
					str += '\n<?php echo _('Message:')?> ' + data.msg;
				}
				jAlert(str);
				return;
			}
			else {
				window.location.reload(true);
			}
		},
		error: function() {
			/* restore the previous state */
			jHide();
			jAlert("<?php echo _('Failure, please try again.')?>");
		}
	});
}
function initEvents() {
	$("#saveXHSBtn").off("click").click(onsavePort4);
}
$(document).ready(function() {
  gateway.page.init("Gateway > Hardware > LAN Ethernet", "nav-lan");
  $("#port4").prop("checked", o_isPort4XHSEnabled);
  initEvents();
  $('.btn_vdsl').click(function(){
    var click_val = $(this).attr('name');
    var set_val;
    if(click_val == 'reclaim'){set_val='{"value_set":"'+false+'"}';}
    else if(click_val == 'wan_OE'){set_val='{"value_set":"'+true+'"}';}
    if(set_val!== null){
      jProgress('<?php echo _('This may take several seconds')?>', 5*60);
      $.ajax({
        type: 'POST',
        url: 'actionHandler/ajaxSet_wanOE_lan.php',
        data:{configInfo: set_val},
        success: function(data){
          msg_parseJSON = $.parseJSON(data);
          if(msg_parseJSON.error_message == 'success'){
            reboot();
          }
        }
      });
    }
  });
});
function reboot(){
  var set_val='{"router_reboot":"Router,Wifi,VoIP,Dect,MoCA"}';
  $.ajax({
    type: 'POST',
    url: 'actionHandler/ajaxSet_wanOE_lan.php',
    data: { configInfo: set_val},
    success: function(data) {
      msg_parseJSON = $.parseJSON(data);
      if(msg_parseJSON.error_message == 'success'){
        setTimeout(function recon_check(){
          var url = 'http://'+location.host+'/index.php';
          var request = new XMLHttpRequest;
          request.open('GET', url, true);
          request.send();
          request.onreadystatechange = function(){
            if(request.status==200){
              location.href= url;
            }
            else{
              setTimeout(recon_check,30*1000);
            }
          }},3*60*1000);
      }
    }
  });
}
</script>
<div id="content">
	<h1><?php echo _('Gateway > Hardware > LAN Ethernet')?></h1>
	<div id="educational-tip">
		<p class="tip"><?php echo _('View information about the Gateway\'s Ethernet Ports.')?> </p>
		<p class="hidden"><?php echo _('The Gateway has 4 Gigabit (GbE) Ethernet Ports. When a device is connected to the Gateway with an Ethernet cable, you\'ll see an <i>Active</i> status for that port.')?></p>
	</div>
	<?php
	function NameMap($str,$i)
	{
		//true for Ethernet, False for Docsis
		$wan_enable = getStr("Device.Ethernet.X_RDKCENTRAL-COM_WAN.Enabled");
		$selectedOperationalMode = getStr("Device.X_RDKCENTRAL-COM_EthernetWAN.SelectedOperationalMode");
		$allowEthWan= getStr("Device.DeviceInfo.X_RDKCENTRAL-COM_Syndication.RDKB_UIBranding.AllowEthernetWAN");
  		$autoWanEnable= getStr("Device.DeviceInfo.X_RDKCENTRAL-COM_AutowanFeatureSupport");
		$wanPort= getStr("Device.Ethernet.X_RDKCENTRAL-COM_WAN.Port");
		switch ($str)
		{
			case "Up":
				if($allowEthWan=="true" && $autoWanEnable=="false"){
					if($wan_enable=="true")
						return _("Active Ethernet WAN");
					else
						return _("Active Docsis WAN");
				}else if(($autoWanEnable=="true") && ($i==($wanPort+1))){
					if(strtolower($selectedOperationalMode)=="ethernet"){
						return "Active Ethernet WAN";
					}else if(strtolower($selectedOperationalMode)=="docsis"){
						return "Active Docsis WAN";
					}else{
						return  "Active Auto WAN";
					} 
				}else {
					return _("Active");
				}
				break;
			case "Down":
				return _("Inactive");
				break;
			default:
				return ($str == "Up" ? _("Up") : _("Down"));
		}
	}
	$ids = array_filter(explode(",",getInstanceIds("Device.Ethernet.Interface.")));
	/*if ($_DEBUG) {
		$ids = array("1", "2", "3", "4");
	}*/
	foreach ($ethernetParam as $id => $value)
	{
		if ("true" == $ethernetParam[$id]["Upstream"]){
			continue;
		}
		//WAN port details are not showing in lan page
		if(strpos($PartnerId, "sky-") === false || (strpos($PartnerId, "sky-") !== false && $reclaim && $ids[$id]!='4')||(strpos($PartnerId, "sky-") !== false && $reclaim==false)){
		echo '<div class="module forms block">';
		echo '<h2>'.sprintf(_("LAN Ethernet Port %s"),$ids[$id]).'</h2>';
		$dm = array(
			array(_("LAN Ethernet link status:"), null, $ethernetParam[$id]["Status"]),
		);
		/* link speed */
		$lspeed = $ethernetParam[$id]["CurrentBitRate"];
		$lunit = " Mbps";
		if (empty($lspeed)) {
			$lspeed = _("Not Applicable");
			$lunit = "";
		}
		else if ((int)$lspeed < 0) {
			$lspeed = _("Disconnected");
			$lunit = "";
		}
		/* zqiu
		else if ((int)$lspeed >= 1000) {
			$lspeed = floor((int)$lspeed / 1000);
			$lunit = " Gbps";
		} 
		 */
		array_push($dm, array(_("MAC Address:"), $ethernetParam[$id]["MACAddress"]));
		array_push($dm, array(_("Connection Speed:"), $lspeed.$lunit));
		for ($m=0, $i=0; $i<count($dm); $i++)
		{
			echo '<div class="form-row '.(($m++ % 2)?'odd':'').'" >';
			echo '<span class="readonlyLabel">'.$dm[$i][0].'</span>';
			echo '<span class="value">'.($dm[$i][1] === null ? NameMap($dm[$i][2],$ids[$id]) : $dm[$i][1]).'</span>';
			echo '</div>';
		}
		if ($ids[$id] === "4") {
			/* port 4 as home security port */
			//if($HomeNetworkControl == 'true'){
			if(PREPAID == false){		//XHS port configuration not required for prepaid devices
				if($PartnerId == 'comcast') {
					echo '<div class="form-row odd ">'.
						'<label for="channel_selection">'._("Associate Ethernet Port 4 to XFINITY HOME Network:").'</label>'.
						'<span class="checkbox"><input type="checkbox" id="port4" name="port4" /></span></br></br></br></br>'.
						_('Note: Associating Ethernet Port 4 to XFINITY HOME network will remove the port from your home network.').'</br></br>'.
					'</div>'.
					'<div class="form-row odd" >'.
						'<div style="position:relative;right:-120px;"><input id="saveXHSBtn" type="button" value="'._("Save").'" class="btn submit" /></div>'.
					'</div>';
				}
				else if($PartnerId == 'cox') {
					echo '<div class="form-row odd ">'.
						'<label for="channel_selection">'._("Associate Ethernet Port 4 to HOME SECURITY Network:").'</label>'.
						'<span class="checkbox"><input type="checkbox" id="port4" name="port4" /></span></br></br></br></br>'.
						_('Note: Associating Ethernet Port 4 to HOME SECURITY network will remove the port from your home network.').'</br></br>'.
					'</div>'.
					'<div class="form-row odd" >'.
						'<div style="position:relative;right:-120px;"><input id="saveXHSBtn" type="button" value="'._("Save").'" class="btn submit" /></div>'.
					'</div>';
				}
			}
			//}
		}
		echo '</div>';
             }
	}
	?>
<?php
	if(strpos($PartnerId, "sky-") !== false){
	    echo '<div class="form-btn">';
	    //SWITCH to WANOE && RECLAIM ETH PORT
           if($reclaim == true){
               echo '<input id="reclaim" type="button" name="reclaim" value="'._('Reclaim Eth port').'" class="btn btn_vdsl">';
            }
            else{
               echo '<input type="button" id="wanOE" name="wan_OE" value="'._('Switch to WANOE').'" class="btn btn_vdsl">';
            }
	   echo '</div>';
      }
?>
</div><!-- end #content -->
<?php include('includes/footer.php'); ?>
