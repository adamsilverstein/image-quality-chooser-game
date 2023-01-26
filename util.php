<?php
/**
 * Import the game image to the media library.
 *
 *
 * @return void
 */
function image_quality_chooser_game_generate_images() {
	$game_data = image_quality_chooser_get_game_data();
	$completed_images = ( empty( $ci = json_decode( get_option( 'image_quality_chooser_completed_images' ) ) ) ) ? array() : $ci;

	$folder = plugin_dir_path( dirname( __FILE__ ) . '/source_images' ) . 'source_images';
	$images = glob( $folder . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE );
	if ( $game_data && sizeof( $completed_images ) === sizeof( $images ) ) {
		return $game_data;
	}
	$qualities = array(
		70,
		75,
		82,
		84,
		86,
		90,
	);
	$formats = array(
		'jpeg',
		'webp',
		// 'avif',
	);
	$engines = array(
		'GD',
		//'Imagick',
	);

	// Ignore certificate issues for SSL.
	add_filter( 'https_ssl_verify', '__return_false' );

	// Restrict core sub size generation to the sizes we need for the game.
	$game_sizes = image_quality_chooser_get_sizes();

	add_filter( 'intermediate_image_sizes_advanced', function( $sizes ) use ( $game_sizes ) {
		return array_intersect_key( $sizes, array_flip( $game_sizes ) );
	} );

	$multiplier      = count( $qualities ) * count( $engines ) * count( $formats );
	$total           = $multiplier * count( $images );
	$total_completed = $multiplier * count( $completed_images );
	$remaining       = $total- $total_completed;
	$count           = 1;

	foreach ( $images as $image ) {

		// Skip already completed images.
		if ( in_array( $image, $completed_images ) ) {
			error_log( "skipping $image" );
			continue;
		}
		// Set the quality for each iteration.
		foreach ( $qualities as $quality ) {
			add_filter( 'wp_editor_set_quality', function() use ( $quality ) {
				return $quality;
			} );

			// Sideload the images into the media library using their URLs.
			$plugin_image_url = plugins_url( 'source_images/' . basename( $image ), plugin_dir_path( __FILE__ ) . 'source_images' );
			require_once(ABSPATH . 'wp-admin/includes/media.php');
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/image.php');

			// Generate with each supported engine.
			foreach ( $engines as $engine ) {
				// Set the editor engine accordingly.
				add_filter( 'wp_image_editors', function( $editors ) use ( $engine ) {
					$editors = array( 'WP_Image_Editor_' .$engine );
					return $editors;
				} );

				// Generate each supported test mime type.
				foreach ( $formats as $format ) {
					// Set the mime type accordingly.
					$mime_type = 'image/' . $format;

					// Skip if the current editor doesn't support the format.
					if ( ! wp_image_editor_supports( array( 'mime_type' => $mime_type ) ) ) {
						continue;
					}

					add_filter( 'image_editor_output_format', function( $format_mapping ) use ( $mime_type ) {
						$format_mapping['image/jpeg'] = $mime_type;
						return $format_mapping;
					} );

					$image_variation = image_quality_chooser_make_name( basename( $image ), 'orig', $format,  $engine,  $quality );

					// Log the image variation.
					image_quality_chooser_log_message( sprintf( 'Processed variation %s of %s (%s)', $count, $remaining, $image_variation ) );
					$count++;

					$attach_id = media_sideload_image( $plugin_image_url, 0, $image_variation, 'id' );
					$attach_data = wp_get_attachment_metadata( $attach_id );

					$sizes = array();
					foreach( image_quality_chooser_get_sizes() as $size ) {
						$sizes[ $size ] = $attach_data['sizes'][ $size ];
					}
					$game_data[] = array(
						'attachment_id' => $attach_id,
						'upload_file'   => $attach_data['file'],
						'filename'      => $image,
						'source_mime'   => 'image/jpeg', // Originals are all jpeg.
						'output_mime'   => $mime_type,
						'engine'        => $engine,
						'quality'       => $quality,
						'filesize'      => $attach_data['filesize'],
						'width'         => $attach_data['width'],
						'height'        => $attach_data['height'],
						'sizes'         => $sizes,
					);
					remove_all_filters( 'image_editor_output_format' );
				}
				remove_all_filters( 'wp_image_editors' );
			}
			remove_all_filters( 'wp_editor_set_quality' );


			if ( ! in_array( $image, $completed_images ) ) {

				// After each image is processed, record the progress in a transient. If the process
				// is interrupted, the transient will be used to resume the process.
				$completed_images[] = $image;

				// Log storage.
				image_quality_chooser_log_message( 'Save completed images: ' . count( $completed_images ) );
				update_option( 'image_quality_chooser_completed_images', json_encode( $completed_images ) );

				image_quality_chooser_set_game_data( $game_data );
			}
		}
	}

	return $game_data;
}

/**
 * Make a filename from a base name, format, engine and quality.
 *
 * @param string $base_name The base file name.
 * @param string $size The image size.
 * @param string $format The image format.
 * @param string $engine The image editor engine.
 * @param string $quality The image quality.
 */
function image_quality_chooser_make_name( $base_name, $size, $format, $engine, $quality ) {
	return basename( $base_name, '.jpg' ) . '-' . $size . '-' . str_replace( 'image/', '', $format ) . '-' . strtolower( $engine ) . '-' . $quality;
}


/**
 * Log helper uses error log or WP_CLI logging depending on context.
 */
function image_quality_chooser_log_message( $message ) {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::log( $message );
	} else {
		error_log( $message );
	}
}

/**
 * Set the game data.
 *
 * @param array $game_data The game data.
 * @return bool True if the value was updated, false otherwise.
 */
function image_quality_chooser_set_game_data( $game_data ) {
	return update_option( 'image_quality_chooser_game_data', $game_data );
}

/**
 * Get the game data.
 *
 * @return array $game_data The game data.
 */
function image_quality_chooser_get_game_data() {
	return get_option( 'image_quality_chooser_game_data' );
}

/**
 * Reset the game data.
 *
 * @return void
 */
function image_quality_chooser_reset_game_data() {
	return delete_option( 'image_quality_chooser_game_data' );
}

/**
 * Get the sizes to use for the game.
 */
function image_quality_chooser_get_sizes() {
	return array(
		// 'thumbnail',
		 'medium',
		//'large',
	);
}

/**
 * Set the game choices.
 *
 * @param array $choices The choices.
 * @return bool True if the value was updated, false otherwise.
 */
function image_quality_chooser_set_game_choices( $choices ) {
	return update_option( 'image-quality-chooser-game-choices', $choices );
}

/**
 * Get the game choices.
 */
function image_quality_chooser_get_game_choices() {
	return get_option( 'image-quality-chooser-game-choices' );
}

/**
 * Reset the game choices.
 *
 * @return void
 */
function image_quality_chooser_reset_game_choices() {
	return delete_option( 'image-quality-chooser-game-choices' ) && delete_option( 'image_quality_chooser_completed_images' );
}


