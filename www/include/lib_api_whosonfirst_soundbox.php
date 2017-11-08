<?php

	loadlib("soundbox");

	##############################################################################

	function api_whosonfirst_soundbox_isCurrent(){

		api_utils_features_ensure_enabled("soundbox");
	}

	##############################################################################

	# the end