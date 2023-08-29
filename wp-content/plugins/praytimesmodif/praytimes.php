<?php

/**
 * You can use this plugin by [praytimes] shortcode
 *
 * You can change the default attributes (arguments) like this
 * [praytimes latitude=15.3 longitude=-61.4 method="Makkah" timezone=-4]
 * For more details about the attributes, see http://praytimes.org/manual
 *
 * @link              https://t.me/ManzoorWaniJK
 * @since             1.0.0
 * @package           PrayTimes
 *
 * @wordpress-plugin
 * Plugin Name:       PrayTimes
 * Plugin URI:        https://t.me/ManzoorWaniJK
 * Description:       Displays PrayTimes for a location. Use <code>[praytimes]</code> shortcode
 * Version:           1.1.3
 * Author:            Manzoor Wani
 * Author URI:        https://t.me/ManzoorWaniJK
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       praytimes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_shortcode( 'praytimes', 'PrayTimes' );

function PrayTimes( $atts ) {
	wp_enqueue_script( 'PrayTimes', untrailingslashit( plugins_url( '', __FILE__ ) ) . '/js/PrayTimes.js', array( 'jquery' ), '1.1.3' );
	wp_enqueue_script( 'praytimes-script', untrailingslashit( plugins_url( '', __FILE__ ) ) . '/js/script.js', array( 'jquery', 'PrayTimes' ), '1.1.3' );
	wp_enqueue_style( 'PrayTimes', untrailingslashit( plugins_url( '', __FILE__ ) ) . '/css/style.css', array(), time() );
	// Manchester
	$defaults = array(
		'latitude'  => '45.5016889',
		'longitude' => '-73.567256',
		'timezone'  => '-5',
		'dst'       => 0,
		'format'    => '24h',
		'method'    => 'ISNA',
	);

	$args = shortcode_atts( $defaults, $atts );

	ob_start();
	?>
	<div id="praytimes">
		<div class="praytimes-head">
			<ul>
				<li class="nav lnav"><a href="javascript:renderPrayTimes(-1)" class="arrow">PREV</a></li>
				<li class="month"><span id="table-title" class="caption"></span></li>
				<li class="nav rnav"><a href="javascript:renderPrayTimes(+1)" class="arrow">NEXT</a></li>
			</ul>
		</div>
		<div class="overflow-auto" >
			<table id="timetable" class="timetable">
				<tbody></tbody>
			</table>
		</div>

	</div>
	<script type="text/javascript">
		var PrayTimeArgs = '<?php echo json_encode( $args ); ?>';
		jQuery(document).ready(function() {
			renderPrayTimes();
		});
	</script>

	<?php
	return ob_get_clean();
}
