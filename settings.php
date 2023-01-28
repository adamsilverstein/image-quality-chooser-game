<?php
// Add a simple settings screen for the plugin.
//
// The settings screen will offer three buttons:
//  - Setup - generates image files (1 try) and sets up the game data.
//  - Export Results - exports the game play results to a CSV file.
//  - Reset Results - resets the game play results.
//
function image_quality_chooser_game_settings_page() {
	// Check if the user have submitted the settings.
	// WordPress will add the "settings-updated" $_GET parameter to the url.
	if ( isset( $_GET['settings-updated'] ) ) {
		// Add settings saved message with the class of "updated".
		add_settings_error( 'image_quality_chooser_messages', 'image_quality_chooser_message', __( 'Settings Saved', 'image-quality-chooser-game' ), 'updated' );
	}
	$wp_nonce = wp_create_nonce( 'wp_rest' );

	?>
	<div class="wrap image_quality_chooser_game_settings" data-wp-nonce="<?php echo $wp_nonce ?>">
		<h1>Image Quality Chooser Game</h1>
		<p>Choose an action:</p>
		<button data-action="export" class="button">Export</button>
		<button data-action="setup" class="button">Setup</button>
		<button data-action="reset-results" class="button">Reset Results</button>
		<button data-action="reset" class="button">Reset All</button>
	</div>
	<?php
}
function  image_quality_chooser_game_settings_page_setup() {
	// Register a new setting for "image_quality_chooser" page.
	add_options_page(
		'Image Quality Game',
		'Image Quality Game',
		'manage_options',
		'image-quality-game',
		'image_quality_chooser_game_settings_page'
	);

	// Enqueue the script to handle interactions.
	wp_enqueue_script( 'image-quality-chooser-game-settings', plugins_url( 'js/image-quality-chooser-game-settings.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
}
add_action( 'admin_menu', 'image_quality_chooser_game_settings_page_setup' );

