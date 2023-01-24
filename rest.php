<?php
/**
 * Register the REST endpoint to collect choices.
 */
add_action( 'rest_api_init', function() {
	register_rest_route( 'image-quality-chooser-game/v1', '/choose', [
		'methods'  => 'POST',
		'callback' => function( WP_REST_Request $request ) {
			$comparison_data = json_decode( $request->get_param( 'comparison-data' ), true );
			$selection       = json_decode( $request->get_param( 'selection' ) );
			$timestamp       = json_decode( $request->get_param( 'timestamp' ) );
			$nonce           = $request->get_param( 'nonce' );
			error_log( "is_user_logged_in:" . json_encode( is_user_logged_in(), JSON_PRETTY_PRINT ) );

			// Validate the nonce.
			if ( ! is_user_logged_in() && ! wp_verify_nonce( $nonce, image_quality_chooser_get_nonce_key() ) ) {
				return new WP_Error( 'invalid-nonce', __( 'Invalid nonce.', 'image-quality-chooser' ), array( 'status' => 403 ) );
			}

			// Record the comparison data.
			$choices = get_option( 'image-quality-chooser-game-choices', array() );

			$current_choice = array(
				'selection'     => $selection,
				'timestamp'     => $timestamp,
				'left_image'    => $comparison_data['left_image'],
				'right_image'   => $comparison_data['right_image'],
				'left_quality'  => $comparison_data['left_quality'],
				'right_quality' => $comparison_data['right_quality'],
				'left_mime'     => $comparison_data['left_mime'],
				'right_mime'    => $comparison_data['right_mime'],
				'left_engine'   => $comparison_data['left_engine'],
				'right_engine'  => $comparison_data['right_engine'],
				'size'          => $comparison_data['experiment_size'],
				'filename'      => $comparison_data['experiment_filename'],
			);

			// Log the choices.
			//error_log( print_r( $current_choice, true ) );

			$choices[] = $current_choice;

			update_option( 'image-quality-chooser-game-choices', $choices );
			return rest_ensure_response( $choices );
		},
	] );
} );