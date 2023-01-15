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
	$game_date = image_quality_chooser_get_game_data();

	// Generate images if missing.
	if ( empty( $game_date ) ) {
		$game_date = image_quality_chooser_game_generate_images();
		image_quality_chooser_set_game_data( $game_data );
	}

	$original = $images['original'];
	$comparison = $images['comparison'];
	?>
	<div class="image-quality-chooser-game">
		<div class="image-quality-chooser-game__images">
			<div class="image-quality-chooser-game__image">
				<img src="<?php echo esc_url( $original['url'] ); ?>" alt="<?php echo esc_attr( $original['alt'] ); ?>" />
			</div>
			<div class="image-quality-chooser-game__image">
				<img src="<?php echo esc_url( $comparison['url'] ); ?>" alt="<?php echo esc_attr( $comparison['alt'] ); ?>" />
			</div>
		</div>
		<div class="image-quality-chooser-game__controls">
			<button class="image-quality-chooser-game__vote" data-vote="no-preference" data-original="<?php echo esc_attr( $original['url'] ); ?>" data-comparison="<?php echo esc_attr( $comparison['url'] ); ?>">No Preference</button>
		</div>
	</div>
	<?php
}