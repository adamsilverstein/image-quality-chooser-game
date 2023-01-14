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
	 * <input>
	 * : The input folder of source images.
	 *
	 * <output>
	 * : The output folder of test images.
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
	 * [--overwrite]
	 * : Overwrite existing images.
	 *
	 * ## EXAMPLES
	 *
	 *     wp image-quality-chooser-game create-test-images /path/to/input /path/to/output
	 *
	 * @when before_wp_load
	 */
	public function __invoke( $args, $assoc_args ) {
		$input = $args[0];
		$output = $args[1];
		$quality = explode( ',', $assoc_args['quality'] ?? '70,82,84,86,90' );
		$format = explode( ',', $assoc_args['format'] ?? 'jpeg,webp,avif' );
		$size = explode( ',', $assoc_args['size'] ?? 'thumbnail,medium,large' );
		$engine = explode( ',', $assoc_args['engine'] ?? 'gd,imagick' );
		$overwrite = $assoc_args['overwrite'] ?? false;

		$files = glob( $input . '/*' );
		foreach ( $files as $file ) {
			$filename = basename( $file );
			$metadata = wp_get_image_metadata( $file );
			foreach ( $size as $size_name ) {
				$size_data = image_get_intermediate_size( $metadata, $size_name );
				if ( ! $size_data ) {
					continue;
				}
				$size_file = $output . '/' . $size_name . '-' . $filename;
				foreach ( $quality as $quality_value ) {
					foreach ( $format as $format_value ) {
						foreach ( $engine as $engine_value ) {
							$engine_class = 'WP_Image_Editor_' . ucfirst( $engine_value );
							if ( ! class_exists( $engine_class ) ) {
								continue;
							}
							$engine = new $engine_class( $file );
							if ( ! $engine->supports_mime_type( $format_value ) ) {
								continue;
							}
							$engine->load();
							$engine->set_quality( $quality_value );
							$engine->save( $size_file . '-' . $quality_value . '-' . $format_value . '.' . $format_value );
						}
					}
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