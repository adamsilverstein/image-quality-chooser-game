<?php
/*
 * Several WP-CLI commands are also provided as part of the plugin:
 * -The `create-test-images` command will be provided to create all of the test images for the game.
 *   It will take an input folder of source images and generate a set of output images using WordPress directly.
 *   The following default WordPress image sizes will be used: Thumbnail, Medium and Large.
 *   The following quality settings will be used: 70, 82, 84, 86 and 90.
 *   The following image formats will be used: JPEG, WebP and AVIF.
 *   Both LibGD and Imagick will be used to generate the images (when the format is supported).
 * - The `export-results` command will be provided to export the results of the game to a CSV file.
 * - The `reset` command will be provided to reset the game to its initial state, clearing existing results.
*/

/**
 * The `create-test-image` command.
 *
 * @package ImageQualityChooserGame
 * @subpackage CLI
 * @since 1.0.0
 *
 */
class Create_Test_Images_Command extends WP_CLI_Command {
	/**
	 * Create all of the test images for the game.
	 *
	 * ## OPTIONS
	 *
	 * [<input>]
	 * : The input folder of source images. Default is the `source_images` folder in the plugin directory.
	 *
	 *
	 * [--quality=<quality>]
	 * : The quality settings to use. Default: 70,82,84,86,90
	 *
	 * [--format=<format>]
	 * : The image formats to use. Default: jpeg,webp,avif
	 *
	 * [--size=<size>]
	 * : The image sizes to use. Default: thumbnail,medium,large
	 *
	 * [--engine=<engine>]
	 * : The image engines to use. Default: gd,imagick
	 *
	 * ## EXAMPLES
	 *
	 *     wp image-quality-chooser-game create-test-images /path/to/input
	 *
	 * @when before_wp_load
	 */
	public function __invoke( $args, $assoc_args ) {
		// Set the default.
		if ( isset( $args[0] ) ) {
			$input = $args[0];
		} else {
			// Default input is the plugin's source_images folder.




		// Update the user with progress: starting processing folder.
		WP_CLI::line( 'Processing folder: ' . $input );

		// Import the folder of images.
		image_quality_chooser_game_generate_images( $input );

	}
}


/**
 * Sideload the images from a folder and add the meta data.
 *
 * @sice 1.0.0
 *
 * @param string $input The input folder of source images.
 * @param array $quality The quality settings to use.
 * @param array $format The image formats to use.
 * @param array $engine The image engines to use.
 *
 */
function iqcg_sideload_images( $input, $quality, $format, $engine ) {

		// Include the core WP_Image_Editor_GD and WP_Image_Editor_Imagick classes.
		require_once ABSPATH . 'wp-includes/class-wp-image-editor.php';
		require_once ABSPATH . 'wp-includes/class-wp-image-editor-imagick.php';
		require_once ABSPATH . 'wp-includes/class-wp-image-editor-gd.php';
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');


		// Iterate over the files in the input folder.
		$files = glob( $input . '/*' );
		foreach ( $files as $file ) {
			$filename = basename( $file );
			WP_CLI::line( 'Saving: ' . $filename );

			foreach ( $quality as $quality_value ) {
				foreach ( $format as $format_value ) {
					$mime_type = 'image/' . $format_value;
					foreach ( $engine as $engine_value ) {
						$engine_class = 'WP_Image_Editor_' . $engine_value;

						// Import the file into the media library.
						$attachment_id = media_sideload_image( $file, 0, null, 'id' );

						// Check for errors.
						if ( is_wp_error( $attachment_id ) ) {
							// Log the error.
							error_log( $attachment_id->get_error_message() );
							continue;
						}

						// Add the meta data to the image.
						$meta = array(
							'engine' => $engine_value,
							'quality' => $quality_value,
							'format' => $format_value,
						);
						wp_update_attachment_metadata( $attachment_id, $meta );

					}
				}
			}
		}
	}


/**
 * The `export-results` command.
 *
 * @package Image_Quality_Chooser_Game
 * @subpackage CLI
 * @since 1.0.0
 */
class Export_Results_Command extends WP_CLI_Command {
	/**
	 * Export the results of the game to a CSV file.
	 *
	 * ## OPTIONS
	 *
	 * <output>
	 * : The output CSV file.
	 *
	 * ## EXAMPLES
	 *
	 *     wp image-quality-chooser-game export-results /path/to/output.csv
	 *
	 * @when before_wp_load
	 */
	public function __invoke( $args, $assoc_args ) {
		$output = $args[0];
		$fp = fopen( $output, 'w' );
		$images = get_posts( array(
			'post_type' => 'image',
			'posts_per_page' => -1,
		) );
		foreach ( $images as $image ) {
			$choices = get_post_meta( $image->ID, 'choices', true );
			$choices = array_map( function( $choice ) {
				return $choice['quality'];
			}, $choices );
			fputcsv( $fp, array_merge( array( $image->post_title ), $choices ) );
		}
		fclose( $fp );
	}
}

/**
 * The `reset` command.
 *
 * @package Image_Quality_Chooser_Game
 * @subpackage CLI
 * @since 1.0.0
 */
class Reset_Command extends WP_CLI_Command {
	/**
	 * Reset the game to its initial state, clearing existing results.
	 *
	 * ## EXAMPLES
	 *
	 *     wp image-quality-chooser-game reset
	 *
	 * @when before_wp_load
	 */

}

WP_CLI::add_command( 'image-quality-chooser-game create-test-images', __NAMESPACE__ . '\Create_Test_Images_Command' );
WP_CLI::add_command( 'image-quality-chooser-game export-results', __NAMESPACE__ . '\Export_Results_Command' );
WP_CLI::add_command( 'image-quality-chooser-game reset', __NAMESPACE__ . '\Reset_Command' );
