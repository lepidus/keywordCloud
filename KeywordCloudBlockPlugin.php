<?php

/**
 * @file plugins/blocks/keywordCloud/KeywordCloudBlockPlugin.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class KeywordCloudBlockPlugin
 *
 * @brief Class for KeywordCloud block plugin
 */

namespace APP\plugins\blocks\keywordCloud;

use PKP\plugins\BlockPlugin;
use PKP\cache\CacheManager;
use APP\facades\Repo;
use PKP\facades\Locale;
use APP\submission\Submission;

class KeywordCloudBlockPlugin extends BlockPlugin
{
    private const KEYWORD_BLOCK_MAX_ITEMS = 50;
    private const KEYWORD_BLOCK_CACHE_DAYS = 2;
    private const ONE_DAY_SECONDS = 60 * 60 * 24;
    private const TWO_DAYS_SECONDS = self::ONE_DAY_SECONDS * self::KEYWORD_BLOCK_CACHE_DAYS;

    public function getDisplayName()
    {
        return __('plugins.block.keywordCloud.displayName');
    }

    public function getDescription()
    {
        return __('plugins.block.keywordCloud.description');
    }

    public function getContextSpecificPluginSettingsFile()
    {
        return $this->getPluginPath() . '/settings.xml';
    }

    public function cacheDismiss()
    {
        return null;
    }

    public function getContents($templateMgr, $request = null)
    {
        $context = $request->getContext();
        if (!$context) {
            return '';
        }

        $locale = Locale::getLocale();
        $cacheManager = CacheManager::getManager();
        $cache = $cacheManager->getFileCache(
            $context->getId(),
            'keywords_'. $locale,
            [$this, 'cacheDismiss']
        );

        $keywords =& $cache->getContents();
        $currentCacheTime = time() - $cache->getCacheTime();

        if ($currentCacheTime > self::TWO_DAYS_SECONDS) {
            $cache->flush();
            $cache->setEntireCache($this->getKeywordsJournal($context->getId()));
        } elseif ($keywords == "[]") {
            $cache->setEntireCache($this->getKeywordsJournal($context->getId()));
        }

        $templateMgr->addJavaScript('d3', 'https://d3js.org/d3.v4.js');
        $templateMgr->addJavaScript('d3-cloud', 'https://cdn.jsdelivr.net/gh/holtzy/D3-graph-gallery@master/LIB/d3.layout.cloud.js');

        $templateMgr->assign('keywords', $keywords);
        return parent::getContents($templateMgr, $request);
    }

    public function getKeywordsJournal(int $journalId): string
    {
        $submissions = Repo::submission()
            ->getCollector()
            ->filterByContextIds([$journalId])
            ->filterByStatus([Submission::STATUS_PUBLISHED])
            ->getMany();

        $keywords = array();
        $locale = Locale::getLocale();
        foreach ($submissions as $submission) {
            $publications = $submission->getPublishedPublications();

            foreach ($publications as $publication) {
                $publicationKeywords = $publication->getData('keywords', $locale);

                if(!is_null($publicationKeywords) and count($publicationKeywords) > 0) {
                    $keywords = array_merge($keywords, $publicationKeywords);
                }
            }
        }

        $countKeywords = array_count_values($keywords);
        arsort($countKeywords, SORT_NUMERIC);

        $topKeywords = array_slice($countKeywords, 0, self::KEYWORD_BLOCK_MAX_ITEMS);
        $keywords = array();

        foreach ($topKeywords as $key => $countKey) {
            $keywords[] = (object) ['text' => $key, 'size' => $countKey];
        }

        return json_encode($keywords);
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\blocks\keywordCloud\KeywordCloudBlockPlugin', '\KeywordCloudBlockPlugin');
}
