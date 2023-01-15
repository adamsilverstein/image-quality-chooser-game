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
	$quality = array( '70,82,84,86,90' );
	$format = array( 'jpeg,webp,avif' );
	$engine = array( 'GD,Imagick' );
	$images = glob( $folder . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE );
	foreach ( $images as $image ) {
		$filename = basename( $image );
		$filetype = wp_check_filetype( $filename, null );
		$wp_upload_dir = wp_upload_dir();
		$attachment = array(
			'guid'           => $wp_upload_dir['url'] . '/' . $filename,
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $image );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $image );
		$game_data[] = array(
			'attachment_id' => $attach_id,
			'filename' => $filename,
			'filetype' => $filetype,
			'width' => $attach_data['width'],
			'height' => $attach_data['height'],
			'sizes' => array(
				'thumbnail' => $attach_data['sizes']['thumbnail'],
				'medium' => $attach_data['sizes']['medium'],
				'large' => $attach_data['sizes']['large'],
			),
			'quality' => $quality,
			'format' => $format,
			'engine' => $engine,
		);
	}
	return $game_data;
}

/**
 * Set the game data.
 *
 * @param array $game_data The game data.
 * @return void
 */
function image_quality_chooser_set_game_data( $game_data ) {
	update_option( 'image_quality_chooser_game_data', $game_data );
}

/**
 * Get the game data.
 *
 * @return array $game_data The game data.
 */
function image_quality_chooser_get_game_data() {
	return get_option( 'image_quality_chooser_game_data' );
}



