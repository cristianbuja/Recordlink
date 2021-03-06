<?php
declare(strict_types=1);
namespace Intera\Recordlink\Hooks;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Typolink\DatabaseRecordLinkBuilder;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;


/**
 * Builds a TypoLink to a database record
 */
class RecordLinkBuilder extends DatabaseRecordLinkBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array &$linkDetails, string $linkText, string $target, array $conf): array
    {

        $tsfe = $this->getTypoScriptFrontendController();
        $configurationKey = $linkDetails['identifier'] . '.';
        $configuration = $tsfe->tmpl->setup['plugin.']['tx_recordlink.'];
        $linkHandlerConfiguration = $tsfe->pagesTSconfig['TCEMAIN.']['linkHandler.']['recordlink.']['configuration.'];


        if (!isset($configuration[$configurationKey]) || !isset($linkHandlerConfiguration[$configurationKey])) {
            throw new UnableToLinkException(
                'Configuration how to link "' . $linkDetails['typoLinkParameter'] . '" was not found, so "' . $linkText . '" was not linked.',
                1490989149,
                null,
                $linkText
            );
        }
        $typoScriptConfiguration = $configuration[$configurationKey]['typolink.'];

        if ($configuration[$configurationKey]['forceLink']) {
            $record = $tsfe->sys_page->getRawRecord($linkHandlerConfiguration[$configurationKey]['table'], $linkDetails['uid']);
        } else {
            $record = $tsfe->sys_page->checkRecord($linkHandlerConfiguration[$configurationKey]['table'], $linkDetails['uid']);
        }

        if ($record === 0) {
            throw new UnableToLinkException(
                'Record not found for "' . $linkDetails['typoLinkParameter'] . '" was not found, so "' . $linkText . '" was not linked.',
                1490989659,
                null,
                $linkText
            );
        }

        // Build the full link to the record
        $localContentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $localContentObjectRenderer->start($record, $linkHandlerConfiguration['table']);
        $localContentObjectRenderer->parameters = $this->contentObjectRenderer->parameters;
        $link = $localContentObjectRenderer->typoLink($linkText, $typoScriptConfiguration);

        $this->contentObjectRenderer->lastTypoLinkLD = $localContentObjectRenderer->lastTypoLinkLD;
        $this->contentObjectRenderer->lastTypoLinkUrl = $localContentObjectRenderer->lastTypoLinkUrl;
        $this->contentObjectRenderer->lastTypoLinkTarget = $localContentObjectRenderer->lastTypoLinkTarget;

        // nasty workaround so typolink stops putting a link together, there is a link already built
        throw new UnableToLinkException(
            '', 1491130170, null, $link
        );
    }
}
