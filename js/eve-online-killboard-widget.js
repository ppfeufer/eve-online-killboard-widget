/* global killboardWidgetL10n, killboardOptions */

jQuery(document).ready(function($) {
	/**
	 * Killboard Data Ajax Update
	 */
	if($('.eve-online-killboard-widget .loaderImage').length) {
		/**
		 * Ajax Call EVE Killboard Data
		 */
		var getKillboardWidgetDataData = {
			ajaxCall: function() {
				$.ajax({
					type: 'post',
					url: killboardWidgetL10n.ajax.url,
					data: 'action=get-eve-killboard-widget-data&nonce=' + killboardWidgetL10n.ajax.eveKillboardWidget.nonce + '&type=' + killboardOptions.entityType + '&name=' + killboardOptions.entityName + '&count=' + killboardOptions.killCount + '&showLosses=' + killboardOptions.showLosses,
					dataType: 'json',
					success: function(result) {
						if(result !== null) {
							$('.killboard-widget-kill-list').html(result.html);
						} // END if(result !== null)
					},
					error: function(jqXHR, textStatus, errorThrow) {
						console.log('Ajax request - ' + textStatus + ': ' + errorThrow);
					}
				});
			} // END ajaxCall()
		};

		var cSpeed = 5;
		var cWidth = 127;
		var cHeight = 19;
		var cTotalFrames = 20;
		var cFrameWidth = 127;
		var cImageSrc = killboardWidgetL10n.ajax.loaderImage;

		var cImageTimeout = false;
		var cIndex = 0;
		var cXpos = 0;
		var cPreloaderTimeout = false;
		var SECONDS_BETWEEN_FRAMES = 0;

		/**
		 * Continue animation
		 *
		 * @returns {undefined}
		 */
		var continueAnimation = function() {
			cXpos += cFrameWidth;

			/**
			 * increase the index so we know which frame
			 * of our animation we are currently on
			 */
			cIndex += 1;

			/**
			 * if our cIndex is higher than our total number of frames,
			 * we're at the end and should restart
			 */
			if(cIndex >= cTotalFrames) {
				cXpos = 0;
				cIndex = 0;
			} // END if(cIndex >= cTotalFrames)

			if($('.eve-online-killboard-widget .loaderImage')) {
				$('.eve-online-killboard-widget .loaderImage').css('backgroundPosition', (-cXpos) + 'px 0');
			} // END if($('.eve-online-killboard-widget .loaderImage'))

			cPreloaderTimeout = setTimeout(continueAnimation, SECONDS_BETWEEN_FRAMES * 1000);
		};

		/**
		 * Start animation
		 *
		 * @returns {undefined}
		 */
		var startAnimation = function() {
			$('.eve-online-killboard-widget .loaderImage').css('display', 'block');
			$('.eve-online-killboard-widget .loaderImage').css('backgroundImage', 'url(' + cImageSrc + ')');
			$('.eve-online-killboard-widget .loaderImage').css('width', cWidth + 'px');
			$('.eve-online-killboard-widget .loaderImage').css('height', cHeight + 'px');
			$('.eve-online-killboard-widget .loaderImage').css('margin', '0 auto');

			var FPS = Math.round(100 / cSpeed);

			SECONDS_BETWEEN_FRAMES = 1 / FPS;
			cPreloaderTimeout = setTimeout(continueAnimation, SECONDS_BETWEEN_FRAMES / 1000);
		};

		/**
		 * stops animation
		 *
		 * @returns {undefined}
		 */
		var stopAnimation = function() {
			clearTimeout(cPreloaderTimeout);
			cPreloaderTimeout = false;
		};

		/**
		 * Imageloader
		 *
		 * @param {type} s
		 * @param {type} fun
		 * @returns {undefined}
		 */
		var imageLoader = function(s, fun) {
			clearTimeout(cImageTimeout);
			cImageTimeout = 0;

			var genImage = new Image();
			genImage.onload = function() {
				cImageTimeout = setTimeout(fun, 0);
			};
			genImage.onerror = new Function('console.log(\'Could not load the image\')');
			genImage.src = s;
		};

		/**
		 * Start the animation
		 */
		imageLoader(cImageSrc, startAnimation);

		/**
		 * Call the ajax to get the market data
		 */
		getKillboardWidgetDataData.ajaxCall();
	} // END if($('.eve-online-killboard-widget .loaderImage').length)
});
