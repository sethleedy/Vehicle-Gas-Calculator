<?php
/* Gas calculator was created by combining several scripts into a smoother interface.
	Feel free to use and modify.
	Creator @ Http://SethLeedy.Name
*/

error_reporting(E_ALL&(~E_NOTICE));
session_start();

// Set some defaults
if ($_SESSION['calc_gas_mileageValue']=="") {
	$_SESSION['calc_gas_mileageValue']="4.3";
}
if ($_SESSION['calc_gas_mileSurChargeValue']=="") {
	$_SESSION['calc_gas_mileSurChargeValue']=".20";
}


if(!empty($_POST['calculator_ok'])) {
	// session storage
	foreach($_POST as $key=>$var) $_SESSION["calc_gas_".$key]=$var;

	if($_POST['type']=='cost')
	{
		$debug=$_POST['distanceValue']. " - " .$_POST['mileageValue']. " - " .$_POST['priceValue'];
		$MileCharge=round( ($_POST['distanceValue'] / 2) * $_POST['mileSurChargeValue'], 2);
		settype($_POST['distanceValue'], "integer");
		$FuelSurCharge=round((intval($_POST['mileageValue']) / 100) * $_POST['distanceValue'] * $_POST['priceValue'], 2);
		$price=$FuelSurCharge + $MileCharge;
	}
	else
	{
		$mileage=number_format(round($_POST['money'] / ($_POST['priceValue'] * $_POST['mileageValue']) * 100));
	}

	// session storage
	switch($_POST['distance'])
	{
		case 'm':
			{
			$mainUnit="miles";
			$mainMileage="Gallons per 100 miles";
			$mainPrice="Per gallon";
			$subMileage="Liters per 100 kilometers";
			$subPrice="Per liter";
			$equivDistance=round($_POST['distanceValue'] * 1.609344);
			$perunit=round($price / $_POST['distanceValue'],2);
			$volume=round(($_POST['mileageValue'] / 100) * $_POST['distanceValue'], 2);
			$volumeUnit="gallons";
			break;
			}
		case 'km':
		{
			$subMileage="Gallons per 100 miles";
			$subPrice="Per gallon";
			$mainUnit="km";
			$mainMileage="liters per 100 km";
			$mainPrice="Per liter";
			$equivDistance=round($_POST['distanceValue'] * 0.621371192);
			$perunit=round($price/$_POST['distanceValue'],2);
			$volume=round(($_POST['mileageValue'] / 100) * $_POST['distanceValue'],2);
			$volumeUnit="liters";
			break;
		}
	}

	if($_POST['type']=='cost')
	{
		$message="Cost for client is: <b>" . $price*2 . " " . $_POST['currency'] . "</b><br><br>My ";
		$message=$message . $_POST['distanceValue'] . " " . $mainUnit . " road trip will cost the client ";
		$message=$message . $price . " " . $_POST['currency'] . " one way, and round trip " . $price*2 . ".";
		$message=$message . $_POST['currency'] . "<br>Based on an efficiency of " . $_POST['mileageValue'] . " " . $mainMileage;
		$message=$message . " and a total fuel volume of " . $volume . " " . $volumeUnit;
		$message=$message . "<br>Total Mileage SurCharge: " . $MileCharge;
		$message=$message . "<br>Per mile surcharge: " . $_POST['mileSurChargeValue'] . $_POST['currency'];
		$message=$message . "<br>Your fuel cost per " . $mainUnit . " will be about " . $_POST['currency'] . $perunit;
		$message=$message . "<br>";
		$message=$message . "<table>";
		$message=$message . "<tr><td>Miles to go(One Way):</td><td></td><td>" . ($_POST['distanceValue'] / 2) . "</td></tr>";
		$message=$message . "<tr><td>Miles to go(Round Trip):</td><td></td><td>" . $_POST['distanceValue'] . "</td></tr>";
		$message=$message . "<tr><td>Charge per Mile:</td><td>*</td><td>" . $_POST['mileSurChargeValue'] . "</td></tr>";
		$message=$message . "<tr><td>Mile SurCharge:</td><td>=</td><td>" . $MileCharge."</td></tr>";
		$message=$message . "<tr><td colspan='4'><hr></td></tr>";
		
		$message=$message . "<tr><td>Gallons per mile:</td><td></td><td>" . intval($_POST['mileageValue']) / 100 . "</td></tr>";
		$message=$message . "<tr><td>Price of fuel per gallon:</td><td>*</td><td>" . $_POST['priceValue'] . "</td></tr>";
		$message=$message . "<tr><td>Distance in miles:</td><td>*</td><td>" . $_POST['distanceValue'] . "</td></tr>";
		$message=$message . "<tr><td>Price of fuel, rounded:</td><td>=</td><td>" . $FuelSurCharge . "</td></tr>";
		$message=$message . "<tr><td colspan='4'><hr></td></tr>";
		
		$message=$message . "<tr><td>Mile SurCharge: " . $MileCharge . "</td><td>+</td><td>Price of fuel: " . $FuelSurCharge . "</td><td>=</td><td>" . $price . " and round trip, " . $price*2 . "</td></tr>";
		$message=$message . "</table>";
		//$message="Cost for client is: <b>$_POST[currency]$price*2</b><br><br>My $_POST['distanceValue']$mainUnit road trip will cost the client $_POST[currency]$price in fuel one way and round trip $_POST[currency]$price*2, based on an efficiency of $_POST['mileageValue']$mainMileage and a total fuel volume of $volume$volumeUnit<br> The per mile surcharge is: $_POST[currency]$_POST[mileSurChargeValue]<br>Your fuel cost per $mainUnit will be about $_POST[currency]$perunit";
	}
	else
	{
		$message="For the given amount of $_POST[currency] $_POST[money] your car can travel $mileage $mainUnit. (Based on efficiency of $_POST[mileageValue] $mainMileage).";
	}
	
} else {

    // We did not calculate yet.
    $message = "Nothing calculated yet.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Gas Calculator</title>
	<meta charset="utf-8">
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false" type="text/javascript"></script>
	<script type="text/javascript" src="gps.jquery.js"></script>

	<script type="text/javascript" language="javascript" src="jquery.ba-hashchange.js"></script>
	<script type="text/javascript" language="javascript" src="jquery.jtabs.0.1.min.js"></script>

	<script type="text/javascript">

		var directionsDisplay;
		var directionsService = new google.maps.DirectionsService();
		// Starting address position of map.
		var start_gps_cord_lat = "40.784936"
		var start_gps_cord_long = "-81.931109"

		function initialize() {
			directionsDisplay = new google.maps.DirectionsRenderer();
			var mapOptions = {
				zoom: 11,
				center: new google.maps.LatLng(start_gps_cord_lat, start_gps_cord_long)
			};
			var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
			directionsDisplay.setMap(map);
			directionsDisplay.setPanel(document.getElementById('directions-panel'));

			var control = document.getElementById('control');
			control.style.display = 'block';
			map.controls[google.maps.ControlPosition.TOP_CENTER].push(control);
		}

		function calcRoute() {
			var start = document.getElementById('start').value;
			var end = document.getElementById('end').value;
			var request = {
				origin: start,
				destination: end,
				travelMode: google.maps.TravelMode.DRIVING
			};
			directionsService.route(request, function(response, status) {
				if (status == google.maps.DirectionsStatus.OK) {
				  directionsDisplay.setDirections(response);
				}
			});
		}
		
		// This will copy the values from steps 1 and 2 into step 3.
		function check_boxes() {

			// Copy Mileage from Google Maps output to Calc input box.
			
			// Can't get the if statement to fire, only when it has something in it. ???
			var exists=$("span[jstcache='24']");
			if (exists.length > 0 && exists.text() != ""); {
				// Remove the " mi" from the string
				org_miles=exists.text()
				miles=org_miles.substring(0, org_miles.length - 3)
				// Remove commas from values
				miles=parseFloat(miles.replace(/,/g, ''));
				$('#distanceValue').val(miles);
				//alert("Distance: -"+$('#distanceValue').val()+"-");
				//clearInterval(calc_update);
			}

			// Copy Gas Buddy fuel price to Calc input
			
			var g1_exists=$(".sim-gas-price1").text();
			//alert(g1_exists.text());
			var g2_exists=$(".sim-gas-price2").text();
			var g3_exists=$(".sim-gas-price3").text();
			//Combine to get correct price.
			fuel_price=g1_exists.concat(".", g2_exists, g3_exists);
			$('#priceValue').val(fuel_price);

			// Default the fuel surcharge to our choice.
			$('#mileSurChargeValue').val(<?php echo $_SESSION['calc_gas_mileSurChargeValue']; ?>);
		}

		function validateForm(frm) {
		   distance=frm.distanceValue.value;
		   mileage=frm.mileageValue.value;
		   price=frm.priceValue.value;

		   if (distance=="" ) {
			  alert('Error: all fields are required!');
			  frm.distanceValue.focus();
			  return false;
		   }
		   if (mileage=="" ) {
			  alert('Error: all fields are required!');
			  frm.mileageValue.focus();
			  return false;
		   }
		   if (price=="" || price==0) {
			  alert('Error: all fields are required and price cannot be 0');
			  frm.priceValue.focus();
			  return false;
		   }
		   return;
		}

		function SetAllIndex(indexNum,formNme,fldA,fldB,fldC) {
		   document.getElementById(fldA).selectedIndex=indexNum;
		   document.getElementById(fldB).selectedIndex=indexNum;
		   document.getElementById(fldC).selectedIndex=indexNum;

		   return;
		}

		function IsNumber(fldId) {
		  var fld=document.getElementById(fldId).value;

		  if (isNaN(fld)) {
			  document.getElementById(fldId).value=fld.substring(0, fld.length-1);
			  var newvalue=document.getElementById(fldId).value;
			  IsNumber(fldId);
		  }

		  return;
		}

		function changeCurrency(item) {
			document.getElementById('currencyShow').innerHTML=item.value;
		}

////End Functions


		// Start Map
		google.maps.event.addDomListener(window, 'load', initialize);

		// Start when ready.
		$(document).ready(function() {

			// Start Tabs Interface.
			$("#tabsdiv").jTabs({
				nav: "ul#tabs-nav",
				tab: ".content .data",
				//"fade", "fadeIn", "slide", "slide_down" or ""
				effect: "slide",
				hashchange: true
			});

			// Start checking text boxes to update the Gas Calc fields from the Maps and Gas.
			//calc_update=setInterval(check_boxes, 3000);
			// Turn off the auto update when on tab3. This allows us to change what was automaticaly put in there
			$(".check_box_off").on("click", function() {
			  clearInterval(calc_update);
			  log("Clearing Int");
			});
			// For the other tabs, turn it back on
			$(".check_box_on").on("click", function() {
			  calc_update=setInterval(check_boxes, 1000);
			  log("Setting Int");
			});

		});

		function log(message){
			if(typeof console == "object"){
				console.log(message);
			}
		}
	</script>

	<link rel="stylesheet" type="text/css" href="gas-calc.css" />
</head>

<body>
	<!-- <?php echo $debug; ?> -->
	<div id="header">
		<a href="http://www.sethleedy.name/gas-calc.php">Gas Calculator</a>
		<div id="subheader">
			Version .8
		</div>
		<hr>
	</div>
	<div id="tabsdiv">

		<ul id="tabs-nav" class="jtab-nav">
            <li><a href="#tab1" class="check_box_on" title="">Step 1 - Plot Route</a></li>
            <li><a href="#tab2" class="check_box_on" title="">Step 2 - Check Fuel</a></li>
            <li><a href="#tab3" class="check_box_off" title="">Step 3 - Input Calculations</a></li>
			<li><a href="#tab4" class="check_box_on" title="">Step 4 - See Results</a></li>
		</ul>

		<div class="content">

			<!-- tab 1 -->
			<div class="tab1 data" style="height: 100%; width: 100%;">
                <p>
				<div id="map_input">

					<!-- STARTING address -->
					From: <input type="text" id="start" value="Wooster, Ohio 44691" onchange="calcRoute();" />
					
					To: <input autofocus type="text" id="end" onchange="calcRoute();" />
					<br>
					<!-- <input type="submit" id="getdirections" value="Get Directions!" /> -->
					<h6><font color="red">*</font> Use this to get the driving distance. It should automatically enter it into the calculator on step 3.</h6>
				</div>
				</p>
				<div id="control"></div>
				<div id="directions-panel"></div>
				<div id="map-canvas" ></div>

			</div>

			<!-- tab 2 -->
			<div class="tab2 data">

				<h3>This price should be automatically entered already on step 3. If not, type it in.</h3>
				<div id="Gas-div">
					<link rel="stylesheet" href="http://affddl.automotive.com/widgets/gas/2/gas.css"/>
					<!-- Automotive Gas Widget -->
					<div class="sim-gas-widget" data-datasetid="44691">
					<span class="sim-gas-head"></span>
					<span class="sim-gas-price"><span class="sim-gas-city">Wooster, OH Cheapest Price</span></span>
					<span class="sim-gas-link">Powered by <a  href="http://www.automotive.com" title="Automotive.com">Automotive.com</a></span>
					</div>
					<script src="http://affddl.automotive.com/widgets/gas/2/gas.js"></script>
					<!-- End of Automotive Gas Widget -->

				</div>

			</div>

			<!-- tab 3 -->
			<div class="tab3 data">

				<div class="calculator_div">
					
					<form action="gas-calc.php#tab4" method="post" name="form1" onsubmit="return validateForm(this);">

					<div style="text-align:center;clear:both;"><input type="submit" value="Calculate!"></div>
					<input type="hidden" name="calculator_ok" value="1">
					<h6><font color="red">*</font> Input should be automatic. If not, enter the mileage from step 1 & the price of fuel from step 2.</h6>
					<hr>

						<p style="clear:both;"><label>Currency:</label> <select name="currency" onchange="changeCurrency(this);">
						 <option value="USD" <?php if($_SESSION['calc_gas_currency']=='USD') echo "selected='true'";?>>USD</option>
						 <option value="EUR" <?php if($_SESSION['calc_gas_currency']=='EUR') echo "selected='true'";?>>EUR</option>
						 <option value="GBP" <?php if($_SESSION['calc_gas_currency']=='GBP') echo "selected='true'";?>>GBP</option>
						 <option value="JPY" <?php if($_SESSION['calc_gas_currency']=='JPY') echo "selected='true'";?>>JPY</option>
						 </select> </p>

						 <fieldset>
							<legend>Calculate trip cost:</legend>
							<p><input type="radio" name="type" value="cost" <?php if(empty($_SESSION['calc_gas_type']) or $_SESSION['calc_gas_type']=='cost') echo "checked='true';"?>> Given the following distance, calculate how much I will pay</p>
							<p style="clear:both;"> <label>Distance:</label>
							<select name=distance id=select onchange="SetAllIndex(this.selectedIndex,'form1','select','select2','select3');" >
							<option value="m" <?php if($_SESSION["calc_gas_distance"]=="m") echo "selected"; ?> >Miles</option>
							<option value="km" <?php if($_SESSION["calc_gas_distance"]=="km") echo "selected"; ?> >Kilometers</option></select>
							<input type="text" name="distanceValue" id="distanceValue" size=7 onkeyup="IsNumber(this.id)" value="<?php echo $_SESSION['calc_gas_distanceValue'];?>" >
							</p>
					   </fieldset>

					   <p align="center"><b>OR</b></p>

					   <fieldset>
							 <legend>How much can I travel:</legend>
							 <p><input type="radio" name="type" value="mileage" <?php if(!empty($_SESSION['calc_gas_type']) and $_SESSION['calc_gas_type']=='mileage') echo "checked='true';"?>> How much can I travel for the given amount or money:</p>
							 <p style="clear:both;"> <label>Money:</label>
							 <span id="currencyShow"><?php echo $_SESSION['calc_gas_currency']?$_SESSION['calc_gas_currency']:'$';?></span> <input type="text" name="money" size="6" value="<?php echo $_SESSION['calc_gas_money']?>"></p>
					   </fieldset>


						<p style="clear:both;"><label>Mileage:</label>
										<select name="mileage" id="select2" onchange="SetAllIndex(this.selectedIndex,'form1','select','select2','select3');">
											<option value="gpk" <?php if($_SESSION["calc_gas_mileage"]=="mpg") echo "selected"; ?> >Gallons per 100 miles</option>
											<option value="lpk" <? if($_SESSION["calc_gas_mileage"]=="lpk") echo "selected"; ?> >liters per 100 km</option>
										</select>
											<input type="text" name="mileageValue" size=7 id="mileageValue" onkeyup="IsNumber(this.id)" value="<?php echo $_SESSION['calc_gas_mileageValue'];?>" ></p>
						<p style="clear:both;"><label>Price:</label>
										<select name=price id=select3 onchange="SetAllIndex(this.selectedIndex,'form1','select','select2','select3');">
											<option value="gallonPrice" <?php if($_SESSION["calc_gas_price"]=="gallonPrice") echo "selected"; ?> >Per gallon</option>
											<option value="literPrice" <?php if($_SESSION["calc_gas_price"]=="literPrice") echo "selected"; ?> >Per litre</option>
										</select>
										<input type="text" name="priceValue" size=7 id="priceValue" onkeyup="IsNumber(this.id)" value="<?php echo $_SESSION['calc_gas_priceValue'];?>" ></p>

						<p style="clear:both;"><label>Per mile surcharge:</label>
										<input type="text" name="mileSurChargeValue" size=7 id="mileSurChargeValue" value="<?php echo $MileCharge;?>" ></p>



						<hr>
						<div style="text-align:center;clear:both;"><input type="submit" value="Calculate!"></div>
						<input type="hidden" name="calculator_ok" value="1">
					</form>
				</div>

			</div>

			<!-- tab 4 -->
			<div class="tab4 data">

				<div id="table">
					<p style="clear:both;"> <?php echo $message; ?> </p>
				</div>

			</div>


		</div>
	</div>
</body>
</html>
