/**
 * Onload, set up dismiss events. Vanilla JS.
 */
document.addEventListener('readystatechange', function() {
	if (document.readyState !== "complete") {
		return;
	}
	// Set up function to make ajax call.
	function dismissNotice( noticeElement ) {
		// do fetch request to ajax endpoint.
		fetch( ajaxurl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8',
			},
			body: 'action=ptam_dismiss_notice&_ajax_nonce=' + ptam_app_dismiss.nonce,
		} )
		.then( function( response ) {
			return response.json();
		} )
		.then( function( json ) {
			if ( json.success ) {
				// Hide the notice.
				noticeElement.style.display = 'none';
			}
		} );
	}

	// Set up button events.
	var ratingNotice = document.querySelector( '.is-dismissible' );
	if ( null !== ratingNotice ) {
		// Get the dismiss button.
		var dismissButton = ratingNotice.querySelector( '.notice-dismiss' );
		// Set up event.
		dismissButton.addEventListener( 'click', function( event ) {
			event.preventDefault();
			// Hide the notice.
			dismissNotice( ratingNotice );
		} );
	}	
} );