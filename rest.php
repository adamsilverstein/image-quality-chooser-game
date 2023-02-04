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
			$data      = $request->get_param( 'data' );
			$selection = $data[ 'selection' ];
			$comparison_data = $data[ 'comparison-data' ];

			// Record the comparison data.
			$choices = get_option( 'image-quality-chooser-game-choices', array() );

			$current_choice = array(
				'selection'       => $selection,
				'timestamp'       => $timestamp,
				'image_1'         => $comparison_data['image-1'],
				'image_2'         => $comparison_data['image-2'],
				'iamge_1_quality' => $comparison_data['image-1-quality'],
				'image_2_quality' => $comparison_data['image-2-quality'],
				'image_1_mime'    => $comparison_data['image-1-mime'],
				'image_2_mime'    => $comparison_data['image-2-mime'],
				'image_1_engine'  => $comparison_data['image-1-engine'],
				'image_2_engine'  => $comparison_data['image-2-engine'],
				'image_1_size'    => $comparison_data['image-1-size'],
				'image_2_size'    => $comparison_data['image-2-size'],
				'size'            => $comparison_data['game-size'],
				'filename'        => $comparison_data['original-filename'],
				'filesize'        => $comparison_data['original-filesize'],
			);

			$choices[] = $current_choice;

			update_option( 'image-quality-chooser-game-choices', $choices );
			return rest_ensure_response( $choices );
		},
	] );
} );
