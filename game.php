<?php
/**
 * Display the game.
 *
 * The game displays two images of the same size, side by side.
 * The images might be a different quality, format or both and one image might be the original image.
 *
 * The user is asked to “Select the version you prefer based on quality.
 * If both are equally appealing, select “No Preference”
 *
 * The user can select one of the images, or a “No Preference” button at the bottom of the screen.
 * Any of these actions sends a request to a custom REST endpoint that collects
 * the result of any previous votes of this same comparison.
 *
 */
function image_quality_chooser_game_display() {
	$game_data = image_quality_chooser_get_game_data();

	// Generate images if missing.
	if ( empty( $game_data ) ) {
		$game_data = image_quality_chooser_game_generate_images();
		image_quality_chooser_set_game_data( $game_data );
	}
	if ( empty( $game_data ) ) {
		return;
	}

	// Enqueue the game JavaScript in the page footer.
	wp_enqueue_script( 'image-quality-chooser-game', plugins_url( 'image-quality-chooser-game.js', __FILE__ ), [], '1.0.0', true );


	// The top level image game data will be indexed by filename and size.
	// Each game round will use the same image file and size, with two different variations of quality, engine and format (mime type).
	$game_image_data = array(
		'files' => array(),
	);

	$sizes     = array();
	$engines   = array();
	$mimes     = array();
	$qualities = array();



	// Go thru the images in the game data and get the sizes, engines and formats.
	foreach ( $game_data as $image_data ) {
		$filename = basename( $image_data['filename'] );

		if ( ! isset( $game_image_data[ $filename ] ) ) {
			$game_image_data[ $filename ] = array(
				'sizes' => array(),
			);
		}

		foreach ( $image_data['sizes'] as $size_name => $size ) {

			if ( ! isset( $game_image_data[ $filename ][ $size_name ] ) ) {
				$game_image_data[ $filename ][ $size_name ] = array();
			}

			$engine = $image_data['engine'];
			if ( ! isset( $game_image_data[ $filename ][ $size_name ][ $engine ] ) ) {
				$game_image_data[ $filename ][ $size_name ][ $engine ] = array();
			}

			$mime = $image_data['output_mime'];
			if ( ! isset( $game_image_data[ $filename ][ $size_name ][ $engine ][ $mime ] ) ) {
				$game_image_data[ $filename ][ $size_name ][ $engine ][ $mime ] = array();
			}

			$quality = $image_data['quality'];
			if ( ! isset( $game_image_data[ $filename ][ $size_name ][ $engine ][ $mime ][ $quality ] ) ) {
				$game_image_data[ $filename ][ $size_name ][ $engine ][ $mime ][ $quality ] = array();
			}


			$game_image_data[ $filename ][ $size_name ][ $engine ][ $mime ][ $quality ] = $image_data['attachment_id'];

			// Add and set size, engine or format.
			if ( ! in_array( $size_name, $sizes, true ) ) {
				$sizes[] = $size_name;
			}

			if ( ! in_array( $image_data['engine'], $engines, true ) ) {
				$engines[] = $image_data['engine'];
			}

			if ( ! in_array( $image_data['output_mime'], $mimes, true ) ) {
				$mimes[] = $image_data['output_mime'];
			}

			if ( ! in_array( $image_data['quality'], $qualities, true ) ) {
				$qualities[] = $image_data['quality'];
			}


		}
	}


	// Pick a random size for the game. Both images use the same size.
	$experiment_size = $sizes[ array_rand( $sizes ) ];

	// Try several times to pick random files for the game.
	$tries = 5;
	while ( $tries-- > 0 ) {
		$left_image = '';
		$right_image = '';

		// Pick a random file for the game.
		$filenames = array_keys( $game_image_data );
		$experiment_filename = $filenames[ array_rand( $filenames ) ];

		// Pick the left and right images mime types..
		$left_mime  = $mimes[ array_rand( $mimes ) ];
		$right_mime = $mimes[ array_rand( $mimes ) ];

		// Pick the left and right images engines.
		$left_engine  = $engines[ array_rand( $engines ) ];
		$right_engine = $engines[ array_rand( $engines ) ];

		// Pick the left and right quality.
		$left_quality  = $qualities[ array_rand( $qualities ) ];
		$right_quality = $qualities[ array_rand( $qualities ) ];

		// Calculate the filenames for the left and right images.
		$left_name  = image_quality_chooser_make_name( $experiment_filename, $experiment_size, $left_engine, $left_mime, $left_quality );
		$right_name = image_quality_chooser_make_name( $experiment_filename, $experiment_size, $right_engine, $right_mime, $right_quality );

		if ( empty ( $game_image_data[ $experiment_filename ][ $experiment_size ][ $left_engine ][ $left_mime ][ $left_quality ] ) ) {
			image_quality_chooser_log_message( 'game_image_data for left is empty' );
			continue;
		} else {
			$left_image = $game_image_data[ $experiment_filename ][ $experiment_size ][ $left_engine ][ $left_mime ][ $left_quality ];
		}

		if ( empty ( $game_image_data[ $experiment_filename ][ $experiment_size ][ $right_engine ][ $right_mime ][ $right_quality ] ) ) {
			image_quality_chooser_log_message( 'game_image_data for right is empty' );
			continue;
		} else {
			$right_image = $game_image_data[ $experiment_filename ][ $experiment_size ][ $right_engine ][ $right_mime ][ $right_quality ];
		}

		// Continue of we have distinct left and right images.
		if ( ! empty( $left_image ) && ! empty( $right_image ) && $left_image !== $right_image ) {
			break;
		}

	}

	$left_image_url = wp_get_attachment_image_url( $left_image, $experiment_size );
	$right_image_url = wp_get_attachment_image_url( $right_image, $experiment_size );

	// Log the image urls.
	image_quality_chooser_log_message( 'left_image_url: ' . $left_image_url );
	image_quality_chooser_log_message( 'right_image_url: ' . $right_image_url );

	?>
	<div class="image-quality-chooser-game">
		<div class="image-quality-chooser-game__images">
			<div class="image-quality-chooser-game__image">
				<img src="<?php echo $left_image_url; ?>"  >
			</div>
			<div class="image-quality-chooser-game__image">
				<img src="<?php echo $right_image_url; ?>"  />
			</div>
		</div>
		<div class="image-quality-chooser-game__controls">
			<button class="image-quality-chooser-game__vote" data-vote="no-preference">No Preference</button>
		</div>
	</div>
	<?php
}
