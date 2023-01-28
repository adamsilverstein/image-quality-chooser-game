<?php

/**
 * Register the REST endpoint to collect choices.
 */
add_action( 'rest_api_init', function() {
	register_rest_route( 'image-quality-chooser-game/v1', '/settings', [
		'methods'  => [ 'POST' ],
		'callback' => function( WP_REST_Request $request ) {
			$params = $request->get_json_params();
			$action = $params['action'];

			// Log the action.
			error_log( 'Action: ' . $action );

			// Handle the action: one of setup, export or reset.
			switch ( $action ) {
				case 'setup':
					// Setup the game images.
					image_quality_chooser_game_generate_images();
					break;
				case 'export':
					// Export the game data.
					image_quality_chooser_export_game_data();
					break;
				case 'reset':
					// Reset all game data.
					image_quality_chooser_reset_game_data();
					image_quality_chooser_reset_game_choices();
					image_quality_chooser_reset_completed_images();
					break;
				case 'reset-results':
					// Reset the game (choices) data.
					image_quality_chooser_reset_game_choices();
					break;
			}
		},
		'permission_callback' => '__return_true',
	] );
	/**
	 * Register the endpoint to collect choices.
	 */
	register_rest_route( 'image-quality-chooser-game/v1', '/choose', [
		'methods'  => 'POST',
		'callback' => function( WP_REST_Request $request ) {
			$comparison_data = json_decode( $request->get_param( 'comparison-data' ), true );
			$selection       = json_decode( $request->get_param( 'selection' ) );
			$timestamp       = json_decode( $request->get_param( 'timestamp' ) );

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
