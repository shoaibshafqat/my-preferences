<?php

return array(

	'success' => array(
		//Preference has been updated.
		'prefUpdated'		=> 'Item preference updated.',

		//Preferences have been reset.
		'prefsReset'		=> 'Your preferences have been reset.',
		
		),

	'general' => array(	
		//When trying to update preference without any change.
		'prefUpToDate'		=> 'Already up to date.',

		//Prompt when reset button is clicked.
		'resetPrefsPrompt'	=> 'Are you sure you want to reset your preferences? This will DELETE all your preference data.',

		//In category preference. When category already overrides it's items
		'catItemsPrefsOverridden' => 'To modify preferences for items under {$cat_name}, change this to "No Preference".',
		
		//In category preference. When category preference is not set.
		'canOverrideCatItems' => 'This will override preferences for all items under {$cat_name}.',

		//Popup When user clicks on locked items.
		'lockedItems'		=> 'A category\'s preference overrides it\'s items\'. <br/>
                    			To change this item\'s preference,
                    			you have to set it\'s category\'s to "No Preference".'
		),	
	'error' => array(
		//Error while loading items to accordion. Mostly when there is a server error.
		'itemsNotLoaded'	=> 'Error while loading items. Try reloading the page.',

		//When updating a missing item
		'itemNotFound'		=> 'Error!. Item not found.',
		
		//Error when updating preference. Mostly when there is a server error.
		'prefNotUpdated'	=> 'Error updating item preference. Try again.',

		//Error when reseting preferences. Mostly when there is a server error.
		'prefsNotReset'		=> 'Error reseting your preferences. Try again.'

		
		),


	);