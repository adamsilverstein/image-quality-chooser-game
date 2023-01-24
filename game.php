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

	// Time the function load.
	$start_time = microtime( true );

	$game_data = image_quality_chooser_game_generate_images();

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

	$sizes = image_quality_chooser_get_sizes();

	// Log time so far.
	$so_far_time = microtime( true );

	// Pick a random size for the game. Both images use the same size.
	$experiment_size = $sizes[ array_rand( $sizes ) ];

	// Try several times to pick random files for the game.
	$tries = 5;
	// Pick a random file for the game.
	$filenames = array_keys( $game_image_data );
	while ( $tries-- > 0 ) {
		$left_image = '';
		$right_image = '';

		$experiment_filename = $filenames[ array_rand( $filenames ) ];

		// Pick the left and right images mime types..
		$left_mime  = $mimes[ array_rand( $mimes ) ];
		$right_mime = $mimes[ array_rand( $mimes ) ];

		// Pick the left and right quality.
		$left_quality  = $qualities[ array_rand( $qualities ) ];
		$right_quality = $qualities[ array_rand( $qualities ) ];

		// Avoid too similar images.
		if ( $left_mime === $right_mime && abs( $left_quality - $right_quality ) < 5 ) {
			continue;
		}

		// Pick the left and right images engines.
		$left_engine  = $engines[ array_rand( $engines ) ];
		$right_engine = $engines[ array_rand( $engines ) ];

		// Calculate the filenames for the left and right images.
		$left_name  = image_quality_chooser_make_name( $experiment_filename, $experiment_size, $left_engine, $left_mime, $left_quality );
		$right_name = image_quality_chooser_make_name( $experiment_filename, $experiment_size, $right_engine, $right_mime, $right_quality );

		if ( empty ( $game_image_data[ $experiment_filename ][ $experiment_size ][ $left_engine ][ $left_mime ][ $left_quality ] ) ) {
			continue;
		} else {
			$left_image = $game_image_data[ $experiment_filename ][ $experiment_size ][ $left_engine ][ $left_mime ][ $left_quality ];
		}

		if ( empty ( $game_image_data[ $experiment_filename ][ $experiment_size ][ $right_engine ][ $right_mime ][ $right_quality ] ) ) {
			continue;
		} else {
			$right_image = $game_image_data[ $experiment_filename ][ $experiment_size ][ $right_engine ][ $right_mime ][ $right_quality ];
		}

		// Continue of we have distinct left and right images.
		if ( ! empty( $left_image ) && ! empty( $right_image ) && $left_image !== $right_image ) {
			break;
		}
	}

	// 2nd log time so far.
	$so_far_time = microtime( true );


	$left_image_url = wp_get_attachment_image_url( $left_image, $experiment_size );
	$right_image_url = wp_get_attachment_image_url( $right_image, $experiment_size );

	wp_enqueue_script( 'image-quality-chooser', plugins_url( '/js/image-quality-chooser-game.js', __FILE__ ), [], '1.0.0', true );
	wp_enqueue_style( 'image-quality-chooser', plugins_url( '/css/image-quality-chooser-game.css', __FILE__ ), [], '1.0.0' );


	$game_comparison_data = array(
		'left_image'          => $left_image,
		'right_image'         => $right_image,
		'left_quality'        => $left_quality,
		'right_quality'       => $right_quality,
		'left_mime'           => str_replace( 'image/', '', $left_mime ),
		'right_mime'          => str_replace( 'image/', '', $right_mime ),
		'left_engine'         => $left_engine,
		'right_engine'        => $right_engine,
		'experiment_size'     => $experiment_size,
		'experiment_filename' => $experiment_filename,
	);

	// Add a none for the submission.
	$submission_nonce = wp_create_nonce( image_quality_chooser_get_nonce_key() );
	$wp_nonce = wp_create_nonce( 'wp_rest' );

	// After the submission, reveal the image meta data.
	?><html lang="en-US">
	<head>
		<?php 	wp_head(); ?>
	</head>
	<body>
	<div class="image-quality-chooser-game__overlay"></div>
	<div class="image-quality-chooser-game">
		<div class="image-quality-chooser-game__meta_header image-quality-chooser-game__results">
			Image details
		</div>
		<div class="image-quality-chooser-game__experiment-data" data-wp-nonce="<?php echo $wp_nonce ?>" data-nonce="<?php echo $submission_nonce; ?>" data-game-comparison="<?php echo htmlspecialchars( json_encode( $game_comparison_data ), ENT_QUOTES, 'UTF-8'); ?>"></div>
		<div class="image-quality-chooser-game__instructions">
			Which image do you prefer?
		</div>
		<div class="image-quality-chooser-game__images">
			<div class="image-quality-chooser-game__image">
				<div class="image-quality-chooser-game__results">
					<?php echo sprintf( 'Image Type: %s, Quality: %s, Engine: %s', ucfirst( str_replace( 'image/', '', $left_mime ) ), $left_quality, $left_engine ); ?>
				</div>
				<img src="<?php echo $left_image_url; ?>" data-image="<?php echo $left_image ?>" class="image-quality-chooser-game__image_tag">
			</div>
			<div class="image-quality-chooser-game__image">
				<div class="image-quality-chooser-game__results">
					<?php echo sprintf( 'Image Type: %s, Quality: %s, Engine: %s', ucfirst( str_replace( 'image/', '', $right_mime ) ), $right_quality, $right_engine ); ?>
				</div>
				<img src="<?php echo $right_image_url; ?>" data-image="<?php echo $right_image ?>" class="image-quality-chooser-game__image_tag">
			</div>
		</div>
		<div class="image-quality-chooser-game__results">
			Download the Performance Lab Plugin to test!<br />
			<img src="<?php echo plugins_url( '/images/download-the-performance-lab-plugin-small.png', __FILE__ ) ?>" width=150 height=150>
		</div>
		<div class="image-quality-chooser-game__controls">
			<button class="image-quality-chooser-game__button" data-image="no-preference">No Preference</button>
		</div>
	</div>
	<?php wp_footer(); ?>
	</body>
</html>
<?php
}
