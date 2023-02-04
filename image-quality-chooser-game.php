<?php
/**
 * Plugin Name: Image Quality Chooser Game
 * Plugin URI:  https://github.com/adamsilverstein/image-quality-chooser-game
 * Requires PHP: 7.4
 * Description: A game to help users understand the tradeoffs between image quality and file size.
 * Version:     1.0.1
 * Author:      Adam Silverstein
 * Author URI:  https://make.wordpress.org/profile/adamsilverstein
 * License:     Apache-2.0
 * License URI: https://www.apache.org/licenses/LICENSE-2.0
 * Text Domain: image-quality-chooser-game
 */

/**
 * This plugin is intended to take over a site and display a game at the home page URL.
 * All functionality is directly included in this plugin.
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
 * Interrupt all front end requests to serve the game instead.
 */
add_action( 'template_redirect', function() {
	// only redirect the home (root) route.
	if ( '/' !== $_SERVER['REQUEST_URI'] ) {
		return;
	}
	require_once __DIR__ . '/game.php';
	image_quality_chooser_game_display();
	exit;
} );

// Require the REST API file.
require_once __DIR__ . '/rest.php';

/**
 * Register the WP-CLI commands.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/cli.php';
}

// Require the util file.
require_once __DIR__ . '/util.php';

// Require the settings screen file.
require_once __DIR__ . '/settings.php';
