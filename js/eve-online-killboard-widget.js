/* global fittingManagerL10n, Clipboard, eftData */

jQuery(document).ready(function($) {
	/**
	 * Market Data Ajax Update
	 */
	if($('.fitting-market-price').length) {
		/**
		 * Ajax Call EVE Market Data
		 */
		var getEveFittingMarketData = {
			ajaxCall: function() {
				$.ajax({
					type: 'post',
					url: fittingManagerL10n.ajax.url,
					data: 'action=get-eve-fitting-market-data&nonce=' + fittingManagerL10n.ajax.eveFittingMarketData.nonce + '&eftData=' + eftData,
					dataType: 'json',
					success: function(result) {
						if(result !== null) {
							$('.table-fitting-marketdata .eve-market-ship-buy').html(result.ship.jitaBuyPrice);
							$('.table-fitting-marketdata .eve-market-fitting-buy').html(result.fitting.jitaBuyPrice);
							$('.table-fitting-marketdata .eve-market-total-buy').html(result.total.jitaBuyPrice);

							$('.table-fitting-marketdata .eve-market-ship-sell').html(result.ship.jitaSellPrice);
							$('.table-fitting-marketdata .eve-market-fitting-sell').html(result.fitting.jitaSellPrice);
							$('.table-fitting-marketdata .eve-market-total-sell').html(result.total.jitaSellPrice);
						}
					},
					error: function(jqXHR, textStatus, errorThrow) {
						console.log('Ajax request - ' + textStatus + ': ' + errorThrow);
					}
				});
			}
		};

		var cSpeed = 5;
		var cWidth = 127;
		var cHeight = 19;
		var cTotalFrames = 20;
		var cFrameWidth = 127;
		var cImageSrc = fittingManagerL10n.ajax.loaderImage;

		var cImageTimeout = false;
		var cIndex = 0;
		var cXpos = 0;
		var cPreloaderTimeout = false;
		var SECONDS_BETWEEN_FRAMES = 0;

		/**
		 * Start animation
		 *
		 * @returns {undefined}
		 */
		var startAnimation = function() {
			$('.table-fitting-marketdata .loaderImage').css('display', 'block');
			$('.table-fitting-marketdata .loaderImage').css('backgroundImage', 'url(' + cImageSrc + ')');
			$('.table-fitting-marketdata .loaderImage').css('width', cWidth + 'px');
			$('.table-fitting-marketdata .loaderImage').css('height', cHeight + 'px');

			var FPS = Math.round(100 / cSpeed);
			SECONDS_BETWEEN_FRAMES = 1 / FPS;

			cPreloaderTimeout = setTimeout(continueAnimation, SECONDS_BETWEEN_FRAMES / 1000);
		};

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
			}

			if($('.table-fitting-marketdata .loaderImage')) {
				$('.table-fitting-marketdata .loaderImage').css('backgroundPosition', (-cXpos) + 'px 0');
			}

			cPreloaderTimeout = setTimeout(continueAnimation, SECONDS_BETWEEN_FRAMES * 1000);
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
			genImage.onerror = new Function('alert(\'Could not load the image\')');
			genImage.src = s;
		};

		/**
		 * Start the animation
		 */
		imageLoader(cImageSrc, startAnimation);

		/**
		 * Call the ajax to get the market data
		 */
		getEveFittingMarketData.ajaxCall();
	}
});
