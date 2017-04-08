/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Module: TYPO3/CMS/Recordlink/RecordLinkHandler
 * Record link interaction
 */
define(['jquery', 'TYPO3/CMS/Recordlist/LinkBrowser'], function($, LinkBrowser) {
	'use strict';

	/**
	 *
	 * @type {{currentLink: string}}
	 * @exports Intera/Recordlink/RecordLinkHandler
	 */
	var RecordLinkHandler = {
		currentLink: ''
	};

	/**
	 *
	 * @param {Event} event
	 */
	RecordLinkHandler.linkRecord = function(event) {
		event.preventDefault();

		var config = $(this).data('config');
		var uid = $(this).data('uid');

		LinkBrowser.finalizeFunction('recordlink:' + config + ':' + uid);
	};

	/**
	 *
	 * @param {Event} event
	 */
	RecordLinkHandler.linkCurrent = function(event) {
		event.preventDefault();

		LinkBrowser.finalizeFunction(RecordLinkHandler.currentLink);
	};

	$(function() {
		RecordLinkHandler.currentLink = $('body').data('currentLink');

		$('a.t3js-recordLink').on('click', RecordLinkHandler.linkRecord);
		$('input.t3js-linkCurrent').on('click', RecordLinkHandler.linkCurrent);
	});

	return RecordLinkHandler;
});
