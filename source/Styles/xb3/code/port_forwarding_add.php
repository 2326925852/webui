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
<?php include('includes/utility.php'); ?>
<!-- $Id: port_forwarding_add.php 3158 2010-01-08 23:32:05Z slemoine $ -->
<div id="sub-header">
	<?php include('includes/userbar.php'); ?>
</div><!-- end #sub-header -->
<?php include('includes/nav.php'); ?>
<?php 
//add by yaosheng
$devices_param = array(
    "LanGwIP"   	=> "Device.X_CISCO_COM_DeviceControl.LanManagementEntry.1.LanIPAddress",
	"LanSubnetMask"	=> "Device.X_CISCO_COM_DeviceControl.LanManagementEntry.1.LanSubnetMask",
	"DeviceMode"	=> "Device.X_CISCO_COM_DeviceControl.DeviceMode",
	);
    $devices_value = KeyExtGet("Device.X_CISCO_COM_DeviceControl.", $devices_param);
$LanGwIP 	= $devices_value["LanGwIP"];
$LanSubnetMask 	= $devices_value["LanSubnetMask"];
$beginAddr 	= getStr("Device.DHCPv4.Server.Pool.1.MinAddress");
$endAddr 	= getStr("Device.DHCPv4.Server.Pool.1.MaxAddress");
$DeviceMode = $devices_value["DeviceMode"];
//$DeviceMode = "IPv6";
//2040::/64, 2040:1::/64, 2040:1:2::/64 and 2040:1:2:3::/64
$prefix_arr = explode('::/', getStr("Device.IP.Interface.1.IPv6Prefix.1.Prefix"));
//$prefix_arr = explode('::/', "2040:1:2:3::/64");
$v6_begin_addr = "0000:0000:0000:0001";
$v6_end_addr = "ffff:ffff:ffff:fffe";
$productLink = getStr("Device.DeviceInfo.X_RDKCENTRAL-COM_Syndication.RDKB_UIBranding.CloudUI.link");
$partnersId = getStr("Device.DeviceInfo.X_RDKCENTRAL-COM_Syndication.PartnerId");
?>
<style type="text/css">
label{
	margin-right: 10px !important;
}
.form-row input.ipv6-input {
	width: 35px;
}
</style>
<script type="text/javascript">
$(document).ready(function() {
    gateway.page.init("Advanced > Port Forwarding > Add Service", "nav-port-forwarding");
    $('#service_name').focus();
    var jsNetMask	= "<?php echo $LanSubnetMask; ?>";
    var beginAddr	= "<?php echo $beginAddr; ?>";
    var endAddr		= "<?php echo $endAddr; ?>";
    var beginArr	= beginAddr.split(".");
    var endArr		= endAddr.split(".");
    var jsGwIP = "<?php echo $LanGwIP; ?>".split(".");
    var jsGatewayIP = "<?php echo $LanGwIP; ?>";
    var DeviceMode = "<?php echo $DeviceMode; ?>";
function populateIPv6Addr(v6addr){
    var v6_arr = new Array();
	var arr = v6addr.split("::");
	if (arr[1] != undefined) { //:: exist
		var arr_first = arr[0].split(':');
		var arr_second = arr[1].split(':');
		var arr1_num = arr_first.length;
		var arr2_num = arr_second.length;
		var zero_num = 8 - arr1_num - arr2_num;
		if (arr1_num == 0) v6_arr[0] = 0;
	    for (var i = 0; i < arr1_num ; i++) {
	    	v6_arr[i] = arr_first[i];
	    }
	    for (var i = arr1_num, j = 0; j<zero_num; i++, j++) {
	    	v6_arr[i] = 0;
	    }
	    for (var i = arr1_num + zero_num, j = 0; j < arr2_num; i++, j++) {
	    	v6_arr[i] = arr_second[j];
	    }
	} //end of if undefined
	else{
	    v6_arr = v6addr.split(':');
	}
    return v6_arr;
}
function IsBlank(id_prefix){
	//Don't check for - ip6_address_r[1-4]
	var ret = true;
	$('[id^="'+id_prefix+'"]').each(function(){
		if($(this).attr('id').search(/^ip6_address_r[1-4]$/) == "-1"){
			if ($(this).val().replace(/\s/g, '') != ""){
				ret = false;
				return false;
			}
		}
	});
	return ret;
}
function GetAddress(separator, id_prefix){
	var ret = "";
	$('[id^="'+id_prefix+'"]').each(function(){
		ret = ret + $(this).val() + separator;
	});
	return ret.replace(eval('/'+separator+'$/'), '');
}
function isIp6AddrRequired()
{
	return !IsBlank('ip6_address_r');
}
function ipv6_in_int(ipv6_address){
	//Residential – /64, Only "interface id" is converted to int for ipv6
	ipv6_int = new Array();
	ipv6_int[0] = parseInt(ipv6_address[0].toUpperCase(), 16);
	ipv6_int[1] = parseInt(ipv6_address[1].toUpperCase(), 16);
	ipv6_int[2] = parseInt(ipv6_address[2].toUpperCase(), 16);
	ipv6_int[3] = parseInt(ipv6_address[3].toUpperCase(), 16);
	return ipv6_int;
}
function isHex(hexa_number){
	var int_number = parseInt(hexa_number,16);
	return (int_number.toString(16) === hexa_number);
}
/* This function checks ending address should be larger than begin address */
function validate_v6addr_pool (DBArr, DEArr) {
	var flag = true;
	if (DEArr[0] < DBArr[0]) {
		flag = false;
	}
	else if (DEArr[0] == DBArr[0]) {
		if (DEArr[1] < DBArr[1]) {
			flag = false;
		}
		else if (DEArr[1] == DBArr[1]) {
			if (DEArr[2] < DBArr[2]) {
				flag = false;
			}
			else if (DEArr[2] == DBArr[2]) {
				if (DEArr[3] < DBArr[3]) {
					flag = false;
				}
			}
		}
	}	
	return flag;
}
function isIp4AddrRequired()
{
	return !IsBlank('server_ip_address_');
}
	jQuery.validator.addMethod("blank",function(value,element){
		return this.optional(element) || (value!='<?php echo _("Choose or input a service name")?>');
	}, "<?php echo _('Please enter a service name.')?>");
	jQuery.validator.addMethod("ip",function(value,element){
		return this.optional(element) || (value.match(/^\d+$/g) && value >= 0 && value <= 255);
	}, "<?php echo _('Please enter a valid IP address.')?>");
	jQuery.validator.addMethod("ip4",function(value,element){
		return this.optional(element) || (value.match(/^\d+$/g) && value >= 1 && value <= 254);
	}, "<?php echo _('Please enter a valid IP address.')?>");
	jQuery.validator.addMethod("ip4_end",function(value,element){
		return this.optional(element) || (value.match(/^\d+$/g) && value >= 1 && value <= 253);
	}, "<?php echo _('Please enter a valid IP address.')?>");
	jQuery.validator.addMethod("port",function(value,element){
		return this.optional(element) || (value.match(/^\d+$/g) && value >= 0 && value <= 65535);
	}, "<?php echo _('Please enter a port number less than 65536.')?>");
	jQuery.validator.addMethod("ltstart",function(value,element){
		return this.optional(element) || value>=parseInt($("#start_port").val());
	}, "<?php echo _('Please enter a value more than or equal to Start Port.')?>");
	jQuery.validator.addMethod("serviceNameRequired",function(value,element){
		var options = ['ssh', 'ftp','aim','http','pptp','https','telnet'];
		if(options.indexOf(value.toLowerCase()) !== -1) {
		 	return false;
		}else{
			return true;
		}
	}, "<?php echo _('Please provide a service name other than the ones mentioned in Common Service.')?>");
	var validator = $("#pageForm").validate({
    	groups:{
			server_ipv4: "server_ip_address_1 server_ip_address_2 server_ip_address_3 server_ip_address_4",
			server_ipv6: "ip6_address_r1 ip6_address_r2 ip6_address_r3 ip6_address_r4 ip6_address_r5 ip6_address_r6 ip6_address_r7 ip6_address_r8"
		},
        rules: {     
        	service_name: {
        		serviceNameRequired: true
        	},    	
        	start_port: {
                required: true,
				port: true,
				digits: true,
				min: 1
            }
            ,end_port: {
                required: true,
				port: true,
				digits: true,
				min: 1,
				ltstart: true
            }
            ,server_ip_address_1: {
                required: isIp4AddrRequired,
				ip4: true
            }
			,server_ip_address_2: {
                required: isIp4AddrRequired,
				ip: true
            }
			,server_ip_address_3: {
                required: isIp4AddrRequired,
				ip: true
            }
			,server_ip_address_4: {
                required: isIp4AddrRequired,
				ip4_end: true
            }
            ,ip6_address_r1:{
            	required: isIp6AddrRequired,
            	hexadecimal: true            	
            } 
            ,ip6_address_r2:{
            	required: isIp6AddrRequired,
            	hexadecimal: true            	
            }  
            ,ip6_address_r3:{
            	required: isIp6AddrRequired,
            	hexadecimal: true            	
            }  
            ,ip6_address_r4:{
            	required: isIp6AddrRequired,
            	hexadecimal: true            	
            }  
            ,ip6_address_r5:{
            	required: isIp6AddrRequired,
            	hexadecimal: true            	
            }  
            ,ip6_address_r6:{
            	required: isIp6AddrRequired,
            	hexadecimal: true            	
            }   
            ,ip6_address_r7:{
            	required: isIp6AddrRequired,
            	hexadecimal: true            	
            }  
            ,ip6_address_r8:{
            	required: isIp6AddrRequired,
            	hexadecimal: true            	
            }            
        }
        ,highlight: function( element, errorClass, validClass ) {
			$(element).closest(".form-row").find("input").addClass(errorClass).removeClass(validClass);
		}
		,unhighlight: function( element, errorClass, validClass ) {
			$(element).closest(".form-row").find("input").removeClass(errorClass).addClass(validClass);
		}		
    });
    $("#btn-cancel").click(function() {
    	window.location = "port_forwarding.php";
    });
	$("#btn-save").click(function(){
		$("p.error").remove();
		var isValid = true;
        if($("#common_services").find("option:selected").val() == "other") {
        	var name = $('#service_name').val().replace(/^\s+|\s+$/g, '');
        	if (name.length == 0){
        		jAlert("<?php echo _('Please input a service name !')?>");
        		return;
        	}
        	else if(name.match(/[<>&"'|]/)!=null){
        		jAlert('<?php echo _("Please input valid Service Name ! \\n Less than (<), Greater than (>), Ampersand (&), Double quote (\"), \\n Single quote (\') and Pipe (|) characters are not allowed.")?>');
				return;
        	}
        }
        else {
        	var name = $("#common_services").find("option:selected").text();
        }
		var type=$('#service_type').find("option:selected").text();
		var ip=$('#server_ip_address_1').val()+'.'+$('#server_ip_address_2').val()+'.'+$('#server_ip_address_3').val()+'.'+$('#server_ip_address_4').val();
		var startport=$('#start_port').val();
		var endport=$('#end_port').val();
		var ipv6addr = GetAddress(":", "ip6_address_r");
		var host0 = parseInt($("#server_ip_address_1").val());
		var host1 = parseInt($("#server_ip_address_2").val());
		var host2 = parseInt($("#server_ip_address_3").val());
		var host3 = parseInt($("#server_ip_address_4").val());
	    if (IsBlank("server_ip_address_") && IsBlank("ip6_address_r")) {
	   	  	jAlert("<?php echo _('Please input valid server address !')?>");
	   	  	return;
		}
		if (!IsBlank("server_ip_address_")) {
			//to check if "Server IPv4 Address" is in "DHCP Mask range"
			var IPv4_valid = ValidIp4Addr(ip, jsGatewayIP, jsNetMask);
			
			//to check if "Server IPv4 Address" is in "DHCP Pool range"
			IPv4_valid = ValidIp4AddrInDhcpPool(ip, beginAddr, endAddr);
			
			//IPv4 validation
			if (ip == jsGatewayIP){
				jAlert("<?php echo _('Server IP can\'t be equal to the Gateway IP address !')?>");
				return;
			} else if(!IPv4_valid){
				jAlert("<?php echo _('Server IP addr is not in valid range !')?>");
				return;
			}		}
		if (!IsBlank("ip6_address_r")) {
			//IPv6 validation
			//if Stateful(Use Dhcp Server) then accept inrange values
			var start	= "<?php echo $v6_begin_addr; ?>";
			var start1	= start.split(":");
			var end		= "<?php echo $v6_end_addr; ?>";
			var end1	= end.split(":");
			var ipv6res	= ipv6addr.split(":");
			var ipv6res1	= ipv6res.splice(4, 4);
			ipv6res_int = ipv6_in_int(ipv6res1);
			start_int 	= ipv6_in_int(start1);
			end_int 	= ipv6_in_int(end1);
			//is valid "interface id" is converted to int for ipv6
			if(!(isHex(ipv6res1[0]) && isHex(ipv6res1[1]) && isHex(ipv6res1[2]) && isHex(ipv6res1[3]))){
				jAlert("<?php echo _('Server IPv6 addr is not valid!')?>");
				return;
			}
			if(!validate_v6addr_pool(start_int, ipv6res_int) || !validate_v6addr_pool(ipv6res_int, end_int)){
				jAlert("<?php echo _('Server IPv6 addr is not in valid range:')?> \n <?php echo $prefix_arr[0].':'.$v6_begin_addr.' ~ '.$prefix_arr[0].':'.$v6_end_addr; ?>");
				return;
			}
		}
		$('.port').each(function(){
			if (!validator.element($(this))){
				isValid = false;	//any invalid will make this false
				return;
			}
		});
		if (IsBlank("server_ip_address_")) {
	   	    	ip = "255.255.255.255";
		}
		if (IsBlank("ip6_address_r")) {
		    	ipv6addr = "x"; 
		}
		if($("#pageForm").valid()) {
			jProgress('<?php echo _("This may take several seconds.")?>',60);
			$.ajax({
				type:"POST",
				url:"actionHandler/ajax_port_forwarding.php",
				data:{add:"true",name:name,type:type,ip:ip,ipv6addr:ipv6addr,startport:startport,endport:endport},
				dataType: "json",
				success:function(results){
					jHide();
					if (results=="<?php echo _('Success!')?>") { 
						window.location.href="port_forwarding.php";
					}
					else if (results=="") {jAlert('<?php echo _("Failure! Please check your inputs.")?>');}
					else jAlert(results);
				},
				error:function(){
					jHide();
					jAlert("<?php echo _('Something went wrong, please try later!')?>");
				}
			});
		} //end of pageform valid
	}); //end of save btn click
//=================================================
 // Monitor Common Services because it informs value and visibility of other field
    $("#common_services").change(function() {
        var $common_select = $(this);
        var $other = $("#service_name");
        if($common_select.find("option:selected").val() == "other") {
            $other.prop("disabled", false).removeClass("disabled").closest(".form-row").show();
            $("#start_port, #end_port").val("").prop("disabled", false); // Reset ports for user entered numbers
        } else {
            $other.prop("disabled", true).removeClass("disabled").val("").closest(".form-row").hide();
			// value in select must be start port + | + end port
			var ports = $common_select.find("option:selected").val();
			var start_port = ports.split("|")[0];
			var end_port = ports.split("|")[1];
			$("#start_port").val(start_port).prop("disabled", true);
			$("#end_port").val(end_port).prop("disabled", true);
        }
    }).trigger("change");
$('#device').click(function(){
	$.virtualDialog({
		title: "<?php echo _('Select from below Connected Devices:')?>",
		content: $("#device_list"),
		footer: '<div id="pop-btn-group">' +
					'<div style="float:left; position:relative; left:140px">' +
					'<input id="add_btn" type="button" value="<?php echo _("Add")?>"/>' +
					'</div>' +
					'<div style="position:relative; left:200px">' +
					'<input id="close_btn" type="button" value="<?php echo _("Close")?>" />' +
					'</div>' +
				'</div>',
		width: "550px",
	});
	$('#add_btn').click(function() {
		var partner_Id = '<?php echo $partnersId ?>';
		var ipv4_addr = $('input[type="radio"]:checked').parent().parent().find("td:eq(1)").text().replace(/^\s+|\s+$/g, '');
		var ipv6_addr = $('input[type="radio"]:checked').parent().parent().find("td:eq(2)").text().replace(/^\s+|\s+$/g, '');
		var ipv4_arr = ipv4_addr.split(".");
		var ipv6_arr = populateIPv6Addr(ipv6_addr);
		$("#server_ip_address_1").val(ipv4_arr[0]);
		$("#server_ip_address_2").val(ipv4_arr[1]);
		$("#server_ip_address_3").val(ipv4_arr[2]);
		$("#server_ip_address_4").val(ipv4_arr[3]);
		if(partner_Id.indexOf('sky-')===0){
		$("#ip6_address_r1").val(ipv6_arr[0]);
		$("#ip6_address_r2").val(ipv6_arr[1]);
		$("#ip6_address_r3").val(ipv6_arr[2]);
		$("#ip6_address_r4").val(ipv6_arr[3]);
		}
		$("#ip6_address_r5").val(ipv6_arr[4]);
		$("#ip6_address_r6").val(ipv6_arr[5]);
		$("#ip6_address_r7").val(ipv6_arr[6]);
		$("#ip6_address_r8").val(ipv6_arr[7]);
		$.virtualDialog("hide");
	});
	$("#close_btn").click(function(){
		$.virtualDialog("hide");
	});
	$('#add-0').focus();
});
	if(DeviceMode == "Ipv4"){
		$("#ip6_address_r5, #ip6_address_r6, #ip6_address_r7, #ip6_address_r8").prop("disabled", true);
    	} else {
		$("#ip6_address_r5, #ip6_address_r6, #ip6_address_r7, #ip6_address_r8").prop("disabled", false);
	}
});//end of document ready
</script>
<?php if($CloudUIEnable == "true"){ ?>
<div id="content">
	<h1><?php echo _("Advanced > Port Forwarding > Add Service")?></h1>
	<div class="module forms">
		<div id="content" style="text-align: center;">
			<br>
			<h3><?php echo sprintf(_("Managing your home network settings is now easier than ever.<br>Visit <a href='http://%s'>%s</a> to set up port forwards, among many other features and settings."),$productLink, $productLink)?></h3>
			<br>
		</div>
	</div> <!-- end .module -->
</div><!-- end #content -->
<?php } else { ?>
<div id="content">
	<h1><?php echo _("Advanced > Port Forwarding > Add Service")?></h1>
    <div id="educational-tip">
        <p class="tip"> <?php echo _("Add a rule for port forwarding services by user.")?></p>
        <p class="hidden"><?php echo _("Port forwarding permits communications from external hosts by forwarding them to a particular port.")?></p>
		<p class="hidden"><?php echo _("Port forwarding settings can affect the Gateway's performance.")?></p>
    </div>
	<form method="post" id="pageForm" action="">
	<div class="module forms">
		<h2><?php echo _("Add Port Forward")?></h2>
		<div  class="form-row odd">
					<label for="common_services"><?php echo _("Common Service:")?></label>
					<select  id="common_services" name="common_services">
					<option  value="21|21" >FTP</option>
					<option  value="5190|5190">AIM</option>
					<option  value="80|80" >HTTP</option>
					<option  value="1723|1723">PPTP</option>
					<option  value="443|443">HTTPs</option>
					<option  value="23|23">Telnet</option>
					<option  value="22|22">SSH</option>
					<option  value="other" class="other" selected="selected"><?php echo _("Other")?></option>
					</select>
				</div>
				<div class="form-row ">
			<label  for="service_name"><?php echo _("Service Name:")?></label> 
			<input type="text"  class="text" value="" id="service_name" name="service_name" />
		</div>
		<div  class="form-row odd">
			<label for="service_type"><?php echo _("Service Type:")?></label>
			<select id="service_type">
				<option value="tcp_udp" selected="selected">TCP/UDP</option>
				<option value="tcp">TCP</option>
				<option value="udp">UDP</option>
			</select>
		</div>
		<div class="form-row ">
			<label for="server_ip_address_1"><?php echo _("Server IPv4 Address:")?></label>
	        <input type="text" size="2"  maxlength="3"  id="server_ip_address_1" name="server_ip_address_1" class="ipv4-addr smallInput" />
	        <label for="server_ip_address_2" class="acs-hide"></label>
			.<input type="text" size="2" maxlength="3"  id="server_ip_address_2" name="server_ip_address_2" class="ipv4-addr smallInput" />
	        <label for="server_ip_address_3" class="acs-hide"></label>
			.<input type="text" size="2" maxlength="3"  id="server_ip_address_3" name="server_ip_address_3" class="ipv4-addr smallInput" />
	        <label for="server_ip_address_4" class="acs-hide"></label>
			.<input type="text" size="2" maxlength="3"  id="server_ip_address_4" name="server_ip_address_4" class="ipv4-addr smallInput" />
		</div>
		<?php
			$ipv6_prefix_arr = explode(':', $prefix_arr[0]);
			$ipa_size = count($ipv6_prefix_arr);
		?>
		<div class="form-row odd">		
			<label for="ip6_address_r1"><?php echo _("Server IPv6 Address:")?></label>
			<input type="text" size="1" maxlength="4" id="ip6_address_r1" name="ip_address_1" disabled="disabled" class="ipv6-addr ipv6-input" value="<?php if($DeviceMode!='Ipv4') {echo $ipv6_prefix_arr[0];} ?>"/>:
	        <label for="ip6_address_r2" class="acs-hide"></label>
			<input type="text" size="1" maxlength="4" id="ip6_address_r2" name="ip_address_2" disabled="disabled" class="ipv6-addr ipv6-input" value="<?php if($DeviceMode!='Ipv4') {if($ipa_size > 1) echo $ipv6_prefix_arr[1]; else echo '0';} ?>"/>:
	        <label for="ip6_address_r3" class="acs-hide"></label>
			<input type="text" size="1" maxlength="4" id="ip6_address_r3" name="ip_address_3" disabled="disabled" class="ipv6-addr ipv6-input" value="<?php if($DeviceMode!='Ipv4') {if($ipa_size > 2) echo $ipv6_prefix_arr[2]; else echo '0';} ?>"/>:
	        <label for="ip6_address_r4" class="acs-hide"></label>
			<input type="text" size="1" maxlength="4" id="ip6_address_r4" name="ip_address_4" disabled="disabled" class="ipv6-addr ipv6-input" value="<?php if($DeviceMode!='Ipv4') {if($ipa_size > 3) echo $ipv6_prefix_arr[3]; else echo '0';} ?>"/>:
	        <label for="ip6_address_r5" class="acs-hide"></label>
			<input type="text" size="1" maxlength="4" id="ip6_address_r5" name="ip_address_5" class="ipv6-addr ipv6-input"/>:
	        <label for="ip6_address_r6" class="acs-hide"></label>
			<input type="text" size="1" maxlength="4" id="ip6_address_r6" name="ip_address_6" class="ipv6-addr ipv6-input"/>:
	        <label for="ip6_address_r7" class="acs-hide"></label>
			<input type="text" size="1" maxlength="4" id="ip6_address_r7" name="ip_address_7" class="ipv6-addr ipv6-input"/>:
	        <label for="ip6_address_r8" class="acs-hide"></label>
			<input type="text" size="1" maxlength="4" id="ip6_address_r8" name="ip_address_8" class="ipv6-addr ipv6-input"/>
    	</div>
		<div class="form-row ">
			<label for="start_port"><?php echo _("Start Port:")?></label>  <input type="text" class="port" value="" id="start_port" name="start_port" />
		</div>
		<div class="form-row odd">
			<label for="end_port"><?php echo _("End Port:")?></label>  <input type="text" class="port" value="" id="end_port" name="end_port" />
		</div>
		<div class="form-row">
			<strong><p><?php echo _("Select a device to add IPv4 and IPv6 address")?></p></strong>
			<input  id="device" type="button" value="<?php echo _('Connected Device')?>" class="btn"  style="position:relative;top:0px;right: 0px;"/>
		</div>
		<div class="form-btn">
			<input type="button" id="btn-save" value="<?php echo _('save')?>" class="btn submit"/>
			<input type="button" id="btn-cancel" value="<?php echo _('Cancel')?>" class="btn alt reset"/>
		</div>
	</div> <!-- end .module -->
	</form>
</div><!-- end #content -->
<?php } ?>
<div id="device_list" style="display: none;">
	<table summary="<?php echo _("This table lists connected devices")?>">
	<tr>
		<th id="device-nmae"><?php echo _("Device Name")?></th>
		<th id="ipv4-addr"><?php echo _("IPv4 Address")?></th>
		<th id="ipv6-addr"><?php echo _("IPv6 Address")?></th>
		<th id="add-radio"><?php echo _("Add")?></th>					
		<th colspan="2">&nbsp;</th>
	</tr>
<?php
		$hostsInstanceArr = DmExtGetInstanceIds("Device.Hosts.Host.");	
		$hostNums = getStr("Device.Hosts.HostNumberOfEntries");
		/*if ($_DEBUG) {
			$hostNums = "2";
		}*/
		for ($i=0; $i < $hostNums; $i++) { 
			$HostName  = getStr("Device.Hosts.Host.$hostsInstanceArr[$i].HostName");
			$IPAddress = getStr("Device.Hosts.Host.$hostsInstanceArr[$i].IPAddress");
			$Active    = getStr("Device.Hosts.Host.$hostsInstanceArr[$i].Active");
			$IPv6Addr = "EMPTY";
			$IPv6Num = getStr("Device.Hosts.Host.$hostsInstanceArr[$i].IPv6AddressNumberOfEntries");
			if (((int)$IPv6Num) > 0) {
				$ids = explode(",", getInstanceIds("Device.Hosts.Host.$hostsInstanceArr[$i].IPv6Address."));
				foreach ($ids as $j) {
					if ($j === '') break;
					$val = getStr("Device.Hosts.Host.$hostsInstanceArr[$i].IPv6Address.$j.IPAddress");
					if (stripos($val, "fe80:") === 0) continue;
					if(strpos($partnersId, "sky-") !== false){
                                            $val_arr = explode(':', $val);
                                            if(is_null($ipv6_prefix_arr[3]) && $val_arr[3]==0){$ipv6_prefix_arr[3]=0;}
                                            if ($val_arr[0]==$ipv6_prefix_arr[0] && $val_arr[1]==$ipv6_prefix_arr[1] && $val_arr[2]==$ipv6_prefix_arr[2] && $val_arr[3]==$ipv6_prefix_arr[3]) {
                                              $IPv6Addr = $val;
                                              break;
                                            }
					}
					else{
					  if (trim($val)!="") {
	 					$IPv6Addr = $val;
	 					break;
					  }  
					}
				}
			}
			/*if ($_DEBUG) {
				$HostName = "Host ".($i+1);
				$IPAddress = "1.1.1.".($i+1);
				$IPv6Addr = "2001::89$i";
				$Active = "true";
			}*/
			if($Active == 'true'){
				echo '<tr>';
				echo '<td headers="devcie-name">'. $HostName . '</td>';
				echo '<td headers="ipv4-addr">'. $IPAddress . '</td>';
				echo '<td headers="ipv6-addr">'. _($IPv6Addr). '</td>';
				echo '<td headers="add-radio"><input type="radio" value="add" id="add-' .$i. '" name="select-device"><label for="add-' .$i. '" class="acs-hide"></label></td>';
				echo '</tr>';
			} 
		}
	?>
	<tfoot>
		<tr class="acs-hide">
			<td headers="device-name">null</td>
			<td headers="ipv4-addr">null</td>
			<td headers="ipv6-addr">null</td>
			<td headers="add-radio">null</td>
		</tr>
	</tfoot>
   </table>
</div>
<?php include('includes/footer.php'); ?>
