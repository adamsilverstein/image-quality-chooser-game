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
	 * ## EXAMPLES
	 *
	 *     wp image-quality-chooser-game create-test-images
	 *
	 * @when before_wp_load
	 */
	public function __invoke( $args, $assoc_args ) {

		// Update the user with progress: starting processing folder.
		WP_CLI::line( 'Importing images' );

		// Import the folder of images.
		$game_date = image_quality_chooser_game_generate_images( );
		if ( ! empty( $game_data ) ) {
			// Update the user with progress: finished processing folder.
			WP_CLI::success( 'Imported images' );
			image_quality_chooser_set_game_data( $game_data );
		} else {
			// Update the user with progress: finished processing folder.
			WP_CLI::error( 'No images imported' );
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
			$data = get_option( 'image-quality-chooser-game-choices', array() );
			$fp = fopen( $args[0], 'w' );
			foreach ( $data as $fields ) {
				fputcsv( $fp, $fields );
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
		public function __invoke( $args, $assoc_args ) {
			image_quality_chooser_reset_game_data();
		}
	}

WP_CLI::add_command( 'image-quality-chooser-game create-test-images', __NAMESPACE__ . '\Create_Test_Images_Command' );
WP_CLI::add_command( 'image-quality-chooser-game export-results', __NAMESPACE__ . '\Export_Results_Command' );
WP_CLI::add_command( 'image-quality-chooser-game reset', __NAMESPACE__ . '\Reset_Command' );
