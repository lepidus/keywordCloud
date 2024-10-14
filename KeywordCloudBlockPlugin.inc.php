<?php

/**
 * @file plugins/blocks/KeywordCloud/KeywordCloudBlockPlugin.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class KeywordCloudBlockPlugin
 * @ingroup plugins_blocks_Keywordcloud
 *
 * @brief Class for KeywordCloud block plugin
 */

define('KEYWORD_BLOCK_MAX_ITEMS', 50);
define('KEYWORD_BLOCK_CACHE_DAYS', 2);
define('ONE_DAY_SECONDS', 60 * 60 * 24);
define('TWO_DAYS_SECONDS', ONE_DAY_SECONDS * KEYWORD_BLOCK_CACHE_DAYS);

import('lib.pkp.classes.plugins.BlockPlugin');
import('classes.submission.SubmissionDAO');

class KeywordCloudBlockPlugin extends BlockPlugin
{
    public function getContextSpecificPluginSettingsFile()
    {
        return $this->getPluginPath() . '/settings.xml';
    }

    public function getDisplayName()
    {
        return __('plugins.block.keywordCloud.displayName');
    }

    public function cacheDismiss()
    {
        return null;
    }

    public function getDescription()
    {
        return __('plugins.block.keywordCloud.description');
    }

    public function getContents($templateMgr, $request = null)
    {
        $context = $request->getContext();
        if (!$context) {
            return '';
        }

        $locale = AppLocale::getLocale();
        $primaryLocale = AppLocale::getPrimaryLocale();

        $keywords = $this->getCachedKeywords($context, $locale);
        if ($keywords == '[]') {
            $keywords = $this->getCachedKeywords($context, $primaryLocale);
        }

        $templateMgr->addJavaScript('d3', 'https://d3js.org/d3.v4.js');
        $templateMgr->addJavaScript('d3-cloud', 'https://cdn.jsdelivr.net/gh/holtzy/D3-graph-gallery@master/LIB/d3.layout.cloud.js');

        $templateMgr->assign('keywords', $keywords);
        return parent::getContents($templateMgr, $request);
    }

    private function getCachedKeywords($context, $locale)
    {
        $cacheManager = CacheManager::getManager();
        $cache = $cacheManager->getFileCache(
            $context->getId(),
            'keywords_' . $locale,
            [$this, 'cacheDismiss']
        );

        $keywords = & $cache->getContents();
        $currentCacheTime = time() - $cache->getCacheTime();

        if (
            ($keywords && $keywords != '[]')
            && $currentCacheTime < TWO_DAYS_SECONDS
        ) {
            return $keywords;
        }

        if ($currentCacheTime > TWO_DAYS_SECONDS) {
            $cache->flush();
        }

        $cache->setEntireCache($this->getContextKeywords($context->getId(), $locale));
        $keywords = & $cache->getContents();

        return $keywords;
    }

    private function getContextKeywords($contextId, $locale)
    {
        $submissionKeywordDao = DAORegistry::getDAO('SubmissionKeywordDAO');

        $submissionsIterator = Services::get('submission')->getMany([
            'contextId' => $contextId,
            'status' => STATUS_PUBLISHED,
        ]);

        $allKeywords = array();
        foreach ($submissionsIterator as $submission) {
            $publications = $submission->getPublishedPublications();

            foreach ($publications as $publication) {
                $publicationKeywords = $submissionKeywordDao->getKeywords($publication->getId(), array($locale));

                if (count($publicationKeywords) > 0) {
                    $allKeywords = array_merge($allKeywords, $publicationKeywords[$locale]);
                }
            }
        }

        $uniqueKeywords = array_unique(array_map('strtolower', $allKeywords));
        $countKeywords = array_count_values($uniqueKeywords);
        arsort($countKeywords, SORT_NUMERIC);

        $topKeywords = array_slice($countKeywords, 0, KEYWORD_BLOCK_MAX_ITEMS);
        $keywords = array();

        foreach ($topKeywords as $key => $countKey) {
            $keyword = new stdClass();
            $keyword->text = $key;
            $keyword->size = $countKey;
            $keywords[] = $keyword;
        }

        return json_encode($keywords);
    }
}
