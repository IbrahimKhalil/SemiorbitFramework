<?php
header("Content-type: text/javascript");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("expires: 0");
header("expires: Tue, 01 Jan 1980 1:00:00 GMT");

$honeypots = isset($_SESSION[ '_honeypot_id' ]) ? $_SESSION[ '_honeypot_id' ] : false;

if ( is_array( $honeypots ) && count( $honeypots ) > 0 ) :

    $honeypots = json_encode( $honeypots );

?>
var form_inputs_json_arr = <?= $honeypots ?>;
for (var key in form_inputs_json_arr) {if (form_inputs_json_arr.hasOwnProperty(key)){document.getElementById(form_inputs_json_arr[key]).style.display = 'none';}}
<?php
unset( $_SESSION[ '_honeypot_id' ] );

endif;
