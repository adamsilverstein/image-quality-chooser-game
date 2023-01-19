/**
 * Main Javascript to run the image quality chooser game
 */
( function () {

	// When the user clicks an img or button element, send the selection data
	// to the REST endpoint.

	// Listen for clicks on img or button elements.
	document.addEventListener('click', function (event) {
		var target = event.target;
		if ( 'IMG' === target.tagName || 'BUTTON' === target.tagName ) {
			// Get the game data from the image-quality-chooser-game__experiment-data element.
			var dataTarget = document.querySelector('.image-quality-chooser-game__experiment-data');

			var data = {
				'selection'      : target.getAttribute('data-image'),
				'comparison-data': dataTarget.getAttribute('data-game-comparison'),
				'timestamp'      : new Date().getTime(),
				'nonce'          : dataTarget.getAttribute('data-nonce'),
			};

			console.log( "data:", data );

			// Show the overlay during the submission.
			var overlay = document.querySelector('.image-quality-chooser-game__overlay');
			overlay.style.display = 'block';

			// Send the data to the REST endpoint.
			var xhr = new XMLHttpRequest();
			// TODO: Use the REST API namespace and route.
			xhr.open( 'POST', 'wp-json/image-quality-chooser-game/v1/choose', true );
			xhr.setRequestHeader('Content-Type', 'application/json');
			xhr.send( JSON.stringify( data ) );

			// Handle success and failure.
			xhr.onload = function () {
				overlay.style.display = 'none';
				if ( xhr.status >= 200 && xhr.status < 400 ) {
					// Success!
					var response = JSON.parse( xhr.responseText );
					console.log( "response:", response );

					// Insert and display the results, which includes a button to restart the game.

					// After 1 minute, reset the display to restart the game
				} else {
					// We reached our target server, but it returned an error
					console.log( "response:", xhr.responseText );
				}
			}
		}
	} );

} )()
