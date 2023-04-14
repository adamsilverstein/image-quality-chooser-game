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

	if ( ! $game_data ) {
		return;
	}

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
				'filesize' => $image_data['filesize'],
			);
		}
		$game_image_data[ $filename ]['attachment_id'] = $image_data['attachment_id'];
		$game_image_data[ $filename ]['filesize'] = $image_data['filesize'];

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


			$game_image_data[ $filename ][ $size_name ][ $engine ][ $mime ][ $quality ]['attachment_id'] = $image_data['attachment_id'];
			$game_image_data[ $filename ][ $size_name ][ $engine ][ $mime ][ $quality ]['filesize'] = $image_data['filesize'];

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
	$tries = 10;
	// Pick a random file for the game.
	$filenames = array_keys( $game_image_data );

	$image_1_image_url = "";
	$image_2_image_url = "";

	while ( $tries-- > 0 ) {
		$image_1_image = '';
		$image_2_image = '';

		$experiment_filename = $filenames[ array_rand( $filenames ) ];

		// Use WebP for the comparison images.
		$image_1_mime = 'image/webp';
		$image_2_mime = 'image/webp';

		// Pick two different qualities.
		$image_1_quality  = $qualities[ array_rand( $qualities ) ];
		$image_2_quality = $qualities[ array_rand( $qualities ) ];

		// Avoid too similar images.
		if ( abs( $image_1_quality - $image_2_quality ) < 10 ) {
			continue;
		}

		// Pick the left and right images engines.
		$image_1_engine  = $engines[ array_rand( $engines ) ];
		$image_2_engine = $engines[ array_rand( $engines ) ];

		// Reset one of the images randomly to the default jpeg 82 image.
		if ( rand( 0, 1 ) ) {
			$image_1_engine = 'GD';
			$image_1_mime = 'image/jpeg';
			$image_1_quality = 82;
		} else {
			$image_2_engine = 'GD';
			$image_2_mime = 'image/jpeg';
			$image_2_quality = 82;
		}

		// Calculate the filenames for the left and right images.
		$image_1_name  = image_quality_chooser_make_name( $experiment_filename, $experiment_size, $image_1_engine, $image_1_mime, $image_1_quality );
		$image_2_name = image_quality_chooser_make_name( $experiment_filename, $experiment_size, $image_2_engine, $image_2_mime, $image_2_quality );

		if ( empty ( $game_image_data[ $experiment_filename ][ $experiment_size ][ $image_1_engine ][ $image_1_mime ][ $image_1_quality ] ) ) {
			continue;
		} else {
			$image_1_image = $game_image_data[ $experiment_filename ][ $experiment_size ][ $image_1_engine ][ $image_1_mime ][ $image_1_quality ]['attachment_id'];
			$image_1_size  = $game_image_data[ $experiment_filename ][ $experiment_size ][ $image_1_engine ][ $image_1_mime ][ $image_1_quality ]['filesize'];
		}

		if ( empty ( $game_image_data[ $experiment_filename ][ $experiment_size ][ $image_2_engine ][ $image_2_mime ][ $image_2_quality ] ) ) {
			continue;
		} else {
			$image_2_image = $game_image_data[ $experiment_filename ][ $experiment_size ][ $image_2_engine ][ $image_2_mime ][ $image_2_quality ]['attachment_id'];
			$image_2_size = $game_image_data[ $experiment_filename ][ $experiment_size ][ $image_2_engine ][ $image_2_mime ][ $image_2_quality ]['filesize'];
		}

		$image_1_image_url = wp_get_attachment_image_url( $image_1_image, $experiment_size );
		$image_2_image_url = wp_get_attachment_image_url( $image_2_image, $experiment_size );

		// Find the original image url from the filename.
		$original_image_url = wp_get_attachment_image_src( $game_image_data[ $experiment_filename ]['attachment_id'] );
		$original_image_filesize = $game_image_data[ $experiment_filename ]['filesize'];


		// Try loading both images to make sure they are available.
		/*
		$image_1 = wp_remote_get( $image_1_image_url );
		$image_2 = wp_remote_get( $image_2_image_url );

		if ( is_wp_error( $image_1 ) || is_wp_error( $image_2 ) ) {
			continue;
		}
		*/

		// Continue if we have distinct left and right images.
		if ( ! empty( $image_1_image_url ) && ! empty( $image_2_image_url ) && $image_1_image !== $image_2_image ) {
			break;
		}
	}

	// Log time so far.
	$so_far_time = microtime( true );

	wp_enqueue_script( 'image-quality-chooser', plugins_url( '/js/image-quality-chooser-game.js', __FILE__ ), [], '1.0.0', true );
	wp_enqueue_style( 'image-quality-chooser', plugins_url( '/css/image-quality-chooser-game.css', __FILE__ ), [], '1.0.0' );

	$image_format_names = array(
		'image/jpeg' => 'JPEG',
		'image/webp' => 'WebP',
	);

	// Add a none for the submission.
	$wp_nonce = wp_create_nonce( 'wp_rest' );

	// After the submission, reveal the image meta data.
	?><html lang="en-US">
	<head>
		<?php wp_head(); ?>
	</head>
	<body>
	<div id="image-quality-chooser-overlay" class="image-quality-chooser-game__overlay"></div>
	<div id="image-quality-chooser-game-data" data-wp-nonce="<?php echo $wp_nonce ?>" data-game-size="<?php echo $experiment_size; ?>" data-game-filename="<?php echo $experiment_filename; ?>" data-original-filesize="<?php echo $original_image_filesize ?>"></div>
	<div class="image-quality-chooser-game__instructions">
		<h1>Which image is more similar to the original?</h1>
		<p>Press the <em>1</em> and <em>2</em> keys to swap the left image. Press <em>z</em> to toggle zoom</p>
		<p>
			Your choice:
			<button id="image-quality-chooser-image-1-button" class="image-quality-chooser-game__button" data-selection="1">Image 1</button>
			<button id="image-quality-chooser-image-2-button" class="image-quality-chooser-game__button" data-selection="2">Image 2</button>
			<button id="image-quality-chooser-image-neither-button" class="image-quality-chooser-game__button" data-selection="Neither">Neither</button>
		</p>
	</div>
		<div id="image-quality-chooser-game" class="image-quality-chooser-game">
			<div class="image-quality-chooser-left-image image-quality-chooser-game__image" id="image-quality-chooser-image-1">
				<div class="image-quality-chooser-game__image_header" >
					Image 1
				</div>
				<div class="image-quality-chooser-game__results">
					<?php echo sprintf( 'Image 1: %s, Quality: %s - Size: %s', $image_format_names[ $image_1_mime ], $image_1_quality, size_format( $image_1_size, 2 ) ); ?>
				</div>
				<div class="image-quality-chooser-game__image_wrapper">
					<img src="<?php echo $image_1_image_url; ?>" data-image="<?php echo $image_1_image ?>" data-mime="<?php echo $image_1_mime; ?>" data-quality="<?php echo $image_1_quality ?>" data-engine="<?php echo $image_1_engine ?>" data-size="<?php echo $image_1_size ?>" class="image-quality-chooser-game__image_tag">
				</div>
			</div>
			<div class="image-quality-chooser-left-image image-quality-chooser-game__image" id="image-quality-chooser-image-2">
				<div class="image-quality-chooser-game__image_header">
					Image 2
				</div>
				<div class="image-quality-chooser-game__results">
					<?php echo sprintf( 'Image 2: %s, Quality: %s - Size: %s', $image_format_names[ $image_2_mime ], $image_2_quality, size_format( $image_2_size, 2 ) ); ?>
				</div>
				<div class="image-quality-chooser-game__image_wrapper">
					<img src="<?php echo $image_2_image_url; ?>" data-image="<?php echo $image_2_image ?>" data-mime="<?php echo $image_2_mime; ?>" data-quality="<?php echo $image_2_quality ?>" data-engine="<?php echo $image_2_engine ?>" data-size="<?php echo $image_2_size ?>" class="image-quality-chooser-game__image_tag">
				</div>
			</div>
			<div class="image-quality-chooser-game__image image-quality-chooser-right-image image-quality-chooser-game__image_original ">
				<div class="image-quality-chooser-image-original">
					<div class="image-quality-chooser-game__image_header">
						Original Image
					</div>
					<div class="image-quality-chooser-game__results ">
						<?php echo sprintf( 'Original Image - Size: %s', size_format( $original_image_filesize, 2 ) ); ?>
					</div>
					<div class="image-quality-chooser-game__image_wrapper">
						<img src="<?php echo $original_image_url[0]; ?>" data-image="<?php echo $experiment_filename ?>" class="image-quality-chooser-game__image_tag ">
					</div>
				</div>
			</div>

		</div>

		<div class="image-quality-chooser-game__results image-quality-chooser-game__CTA">
			Download the Performance Lab Plugin to test!<br />
			<img src="<?php echo plugins_url( '/images/download-the-performance-lab-plugin-small.png', __FILE__ ) ?>" width=150 height=150>
		</div>
		<div id="image-quality-chooser-reload-timer"></div>

	</div>
	<?php wp_footer(); ?>
	</body>
</html>
<?php
}
