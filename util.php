<?php
/**
 * Import the game image to the media library.
 *
 *
 * @return void
 */
function image_quality_chooser_game_generate_images() {
	$game_data = array();
	$folder = plugin_dir_path( dirname( __FILE__ ) . '/source_images' ) . 'source_images';
	$quality = array( '70','82','84','86','90' );
	$formats = array( 'jpeg','webp','avif' );
	$engines = array( 'GD','Imagick' );
	$images = glob( $folder . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE );
	add_filter('https_ssl_verify', '__return_false');
	foreach ( $images as $image ) {
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
				error_log( "Generating $format image: $plugin_image_url engine: $engine" );
				$attach_id = media_sideload_image( $plugin_image_url, 0, basename( $image ) . '-' . $format . '-' . $engine, 'id' );
				$attach_data = wp_get_attachment_metadata( $attach_id );
				// Log id and data.
				error_log( "Attachment ID: $attach_id" );
				error_log( print_r( $attach_data, true ) );

				$game_data[] = array(
					'attachment_id' => $attach_id,
					'upload_file'   => $attach_data['file'],
					'filename'      => $image,
					'source_mime'   => 'image/jpeg', // Originals are all jpeg.
					'output_mime'   => $mime_type,
					'engine'        => $engine,
					'filesize'      => $attach_data['filesize'],
					'width'         => $attach_data['width'],
					'height'        => $attach_data['height'],
					'sizes'         => array(
						'thumbnail' => $attach_data['sizes']['thumbnail'],
						'medium'    => $attach_data['sizes']['medium'],
						'large'     => $attach_data['sizes']['large'],
					),
				);

				remove_all_filters( 'image_editor_output_format' );
			}
			remove_all_filters( 'wp_image_editors' );
		}
	}
	error_log( json_encode( $game_data, JSON_PRETTY_PRINT ) );

	return $game_data;
}

/**
 * Set the game data.
 *
 * @param array $game_data The game data.
 * @return void
 */
function image_quality_chooser_set_game_data( $game_data ) {
	update_option( 'image_quality_chooser_game_settings', $game_data );
}

/**
 * Get the game data.
 *
 * @return array $game_data The game data.
 */
function image_quality_chooser_get_game_data() {
	return get_option( 'image_quality_chooser_game_settings' );
}

/**
 * Reset the game data.
 *
 * @return void
 */
function image_quality_chooser_reset_game_data() {
	delete_option( 'image_quality_chooser_game_settings' );
}




