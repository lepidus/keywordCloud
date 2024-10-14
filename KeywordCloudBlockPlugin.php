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

use APP\facades\Repo;
use APP\submission\Submission;
use PKP\context\Context;
use PKP\cache\CacheManager;
use PKP\facades\Locale;
use PKP\plugins\BlockPlugin;

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
        $primaryLocale = Locale::getPrimaryLocale();

        $keywords = $this->getCachedKeywords($context, $locale);
        if ($keywords == '[]') {
            $keywords = $this->getCachedKeywords($context, $primaryLocale);
        }

        $templateMgr->addJavaScript('d3', 'https://d3js.org/d3.v4.js');
        $templateMgr->addJavaScript(
            'd3-cloud',
            'https://cdn.jsdelivr.net/gh/holtzy/D3-graph-gallery@master/LIB/d3.layout.cloud.js'
        );

        $templateMgr->assign('keywords', $keywords);
        return parent::getContents($templateMgr, $request);
    }

    private function getCachedKeywords(Context $context, string $locale): ?string
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
            && $currentCacheTime < self::TWO_DAYS_SECONDS
        ) {
            return $keywords;
        }

        if ($currentCacheTime > self::TWO_DAYS_SECONDS) {
            $cache->flush();
        }

        $cache->setEntireCache($this->getJournalKeywords($context->getId(), $locale));
        $keywords = & $cache->getContents();

        return $keywords;
    }

    private function getJournalKeywords(int $journalId, string $locale): string
    {
        $submissions = Repo::submission()
            ->getCollector()
            ->filterByContextIds([$journalId])
            ->filterByStatus([Submission::STATUS_PUBLISHED])
            ->getMany();

        $keywords = [];
        foreach ($submissions as $submission) {
            $publications = $submission->getPublishedPublications();

            foreach ($publications as $publication) {
                $publicationKeywords = $publication->getData('keywords', $locale);

                if (!is_null($publicationKeywords) and count($publicationKeywords) > 0) {
                    $keywords = array_merge($keywords, $publicationKeywords);
                }
            }
        }

        $uniqueKeywords = array_unique(array_map('strtolower', $keywords));
        $countKeywords = array_count_values($uniqueKeywords);
        arsort($countKeywords, SORT_NUMERIC);

        $topKeywords = array_slice($countKeywords, 0, self::KEYWORD_BLOCK_MAX_ITEMS);
        $keywords = [];

        foreach ($topKeywords as $key => $countKey) {
            $keywords[] = (object) ['text' => $key, 'size' => $countKey];
        }

        return json_encode($keywords);
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\blocks\keywordCloud\KeywordCloudBlockPlugin', '\KeywordCloudBlockPlugin');
}
