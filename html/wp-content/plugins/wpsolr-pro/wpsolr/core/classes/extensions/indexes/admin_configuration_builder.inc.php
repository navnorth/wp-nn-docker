<?php


use wpsolr\core\classes\engines\configuration\builder\WPSOLR_Configuration_Builder_Factory;

try {

	$test = WPSOLR_Configuration_Builder_Factory::build_form( $option_name, $option_data, $index_indice, '', '' );

	echo $test;

} catch ( Exception $e ) {

	echo $e->getMessage();
}

