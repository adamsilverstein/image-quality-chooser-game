/**
 * Main Javascript to run the image quality chooser game
 */
( function () {

	// When the user clicks an img or button element, send the selection data
	// to the REST endpoint.
	var voted = false;

	// Listen for clicks on img or button elements.
	document.addEventListener( 'click', function (event) {
		var target = event.target;
		if ( ( 'IMG' === target.tagName || 'BUTTON' === target.tagName ) && ! voted ) {
			// Get the game data from the image-quality-chooser-game__experiment-data element.
			var dataTarget = document.querySelector( '.image-quality-chooser-game__experiment-data' );

			var data = {
				'selection'      : target.getAttribute('data-image'),
				'comparison-data': dataTarget.getAttribute('data-game-comparison'),
				'timestamp'      : new Date().getTime(),
				'nonce'          : dataTarget.getAttribute('data-nonce'),
			};

			showOverlay();

			// Send the data to the REST endpoint.
			var xhr = new XMLHttpRequest();
			// TODO: Use the REST API namespace and route.
			xhr.open( 'POST', 'wp-json/image-quality-chooser-game/v1/choose', true );
			xhr.setRequestHeader('Content-Type', 'application/json');
			xhr.send( JSON.stringify( data ) );

			// Handle success and failure.
			xhr.onload = function () {
				hideOverlay();
				if ( xhr.status >= 200 && xhr.status < 400 ) {
					// Success!
					var response = JSON.parse( xhr.responseText );
					console.log( "response:", response );

					showResults();

					// Mark this image as the winner by marking the click target's parent with the `image-quality-chooser-game__winner` class.
					target.parentNode.classList.add( 'image-quality-chooser-game__winner' );


					// After 1 minute, reload the display to restart the game
					setTimeout( function () {
						location.reload();
					}, 60000 );

				} else {
					// We reached our target server, but it returned an error
					console.log( "response:", xhr.responseText );
				}

				// Mark as having voted, preventing re-submission.
				voted = true;
			}
		}
	} );

	var overlay = document.querySelector('.image-quality-chooser-game__overlay');

	// Function to show the overlay.
	function showOverlay() {
		overlay.style.display = 'block';
	}

	// Function to hide the overlay.
	function hideOverlay() {
		overlay.style.display = 'none';
	}

	// Function to show all of the results.
	function showResults() {
		// Select all results from elements with the image-quality-chooser-game__results class.
		var results = document.querySelectorAll('.image-quality-chooser-game__results');
		for ( var i = 0; i < results.length; i++ ) {
			results[ i ].style.display = 'block';
		}

		// Also, hide the controls.
		var controls = document.querySelector('.image-quality-chooser-game__controls');
		controls.style.display = 'none';

		// Also hide the instructions
		var instructions = document.querySelector('.image-quality-chooser-game__instructions');
		instructions.style.display = 'none';

		// Mark the game (image-quality-chooser-game class) as complete.
		var game = document.querySelector('.image-quality-chooser-game' );
		game.classList.add( 'image-quality-chooser-game__complete' );


	}

} )()

