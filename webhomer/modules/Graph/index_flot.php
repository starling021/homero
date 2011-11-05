<?php
/*
 * HOMER Web Interface
 * App: Homer's Stats generator (Alternative Version)
 *
 * Copyright (C) 2011-2012 Alexandr Dubovikov <alexandr.dubovikov@gmail.com>
 * Copyright (C) 2011-2012 Lorenzo Mangani <lorenzo.mangani@gmail.com>
 *
 * The Initial Developers of the Original Code are
 *
 * Alexandr Dubovikov <alexandr.dubovikov@gmail.com>
 * Lorenzo Mangani <lorenzo.mangani@gmail.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
*/

$API = APILOC;
if ($API == 'APILOC') {
$included = 1;
include('../../configuration.php');
//echo '<script type="text/javascript" src="js/jquery.flot.js"></script>';
} else { $included = 0; }


date_default_timezone_set(CFLOW_TIMEZONE);
$offset = STAT_OFFSET;
$xhours = STAT_RANGE;

?>


		<div id="chart1" style="min-width: 380px; width: 99%; margin-left: 1px; float: center; height: 220px"></div>



<script type="text/javascript">



<?php

$uri = "http://".$_SERVER['SERVER_NAME'].APILOC;

// INVITES
$request = $uri."api.php?task=statscount&method=INVITE&hours=".$xhours;
$jsondata = file_get_contents($request);
$response = json_decode($jsondata, true);
//print_r( $response);
foreach($response as $entry){
        foreach($entry as $data){
	$newtime = (strtotime($data['from_date']." ".$offset));
        $secondes[] = '['.$newtime.'000, '.$data['total'].']';
        $asr[] = '['.$newtime.'000, '.$data['asr'].']';
        }
}

// REGISTRATIONS
$request =  $uri."api.php?task=statscount&method=REGISTER&hours=".$xhours;
$jsondata = file_get_contents($request);
$response = json_decode($jsondata, true);
//print_r( $response);
foreach($response as $entry){
        foreach($entry as $data){
	$newtime = (strtotime($data['from_date']." ".$offset));
	$auth = $data['auth']; $pass = $data['completed']; $failed = ($auth - $pass);
		if ($failed >= 0) {
                	$sip401[] = '['.$newtime.'000, '.$failed.']';
                } else { 
			$sip401[] = '['.$newtime.'000, '.($failed + 3).']';
		}
        }
}

// GENERAL FLOW
$request =  $uri."api.php?task=statscount&method=CURRENT&hours=".$xhours;
$jsondata = file_get_contents($request);
$response = json_decode($jsondata, true);
//print_r( $response);
foreach($response as $entry){
        foreach($entry as $data){
        $newtime = (strtotime($data['from_date']." ".$offset));
                $sip100[] = '['.$newtime.'000, '.$data['total'].']';
        }
}

// GRANMASTERTOTAL
$request =  $uri."api.php?task=statscount&method=ALL";
$jsondata = file_get_contents($request);
$response = json_decode($jsondata, true);
//print_r( $response);
foreach($response as $entry){
        foreach($entry as $data){
                $sipTOT = $data['total'];
        }
}


?>

$ = jQuery;

jQuery(document).ready(function() {

 var d1 = [<?php echo join($secondes, ', ');?>];
 var d2 = [<?php echo join($sip100, ', ');?>];
 var d3 = [<?php echo join($sip401, ', ');?>];
 var allp = "<?php echo $sipTOT ?>";
 var asr = [<?php echo join($asr, ', ');?>];


	$.plot($("#chart1"), 
		[ 
		{ data: d2, label: "Packets", lines: { show: true, fill: true } },
             	{ data: d1, label: "Calls", yaxis: 2 }, 
             	{ data: d3, label: "AuthFail", yaxis: 2,  bars: { show: true } },
             	{ data: asr, label: "ASR", yaxis: 1,  lines: { show: true, steps: true }, 
		  color: "rgb(30, 180, 20)", threshold: { below: 60, color: "rgb(200, 20, 30)" } },
		],
           { 
               xaxes: [ { mode: 'time' } ],
               yaxes: [  { position: 'left' },
                         { position: 'right' }
                      ],

		legend: {
                position: "nw",
		margin: 10,
                backgroundOpacity: 1
                },

		 grid: {
                borderWidth: 0
                }

           });

     $('#chart1').append('<div style="position:absolute;left:40%;top:10px;color:#666;font-size:small">Captured Frames: '+allp+'</div>');

});



function pad(number) {
     return (number < 10 ? '0' : '') + number;
}  


</script>		
