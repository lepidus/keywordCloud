 
<?php

/**
 * @file plugins/blocks/keywordCloud/KeywordCloudBlockPlugin.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class KeywordCloudBlockPlugin
 * @ingroup plugins_blocks_keywordcloud
 *
 * @brief Class for KeywordCloud block plugin
 */

define('KEYWORD_BLOCK_MAX_ITEMS', 50);
define('KEYWORD_BLOCK_CACHE_DAYS', 2);
define('ONE_DAY_SECONDS', 60 * 60 * 24);
define('TWO_DAYS_SECONDS', ONE_DAY_SECONDS * KEYWORD_BLOCK_CACHE_DAYS);

use PKP\plugins\BlockPlugin;

class KeywordCloudBlockPlugin extends BlockPlugin
{
    
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

        $locale = AppLocale::getLocale();
        $cacheManager = CacheManager::getManager();
        $cache = $cacheManager->getFileCache(
            $context->getId(),
            'keywords_'. $locale,
            [$this, 'cacheDismiss']
        );

        $keywords =& $cache->getContents();
        $currentCacheTime = time() - $cache->getCacheTime();

        if ($currentCacheTime > TWO_DAYS_SECONDS) {
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

    public function getKeywordsJournal($journalId)
    {
        $submissionKeywordDao = DAORegistry::getDAO('SubmissionKeywordDAO');

        //Get all IDs of the published Articles
        $submissionsIterator = Services::get('submission')->getMany([
            'contextId' => $journalId,
            'status' => STATUS_PUBLISHED,
        ]);

        //Get all Keywords from all published articles of this journal
        $all_keywords = array();
        $currentLocale = AppLocale::getLocale();
        foreach ($submissionsIterator as $submission) {
            $publications = $submission->getPublishedPublications();

            foreach ($publications as $publication) {
                $publi_keywords = $submissionKeywordDao->getKeywords($publication->getId(), array($currentLocale));

                if(count($publi_keywords) > 0) {
                    $all_keywords = array_merge($all_keywords, $publi_keywords[$currentLocale]);
                }
            }
        }
        //Count the keywords and sort them in a frequency basis
        $count_keywords = array_count_values($all_keywords);
        arsort($count_keywords, SORT_NUMERIC);

        // Put only the most often used keywords in an array
        // maximum of KEYWORD_BLOCK_MAX_ITEMS
        $top_keywords = array_slice($count_keywords, 0, KEYWORD_BLOCK_MAX_ITEMS);
        $keywords = array();

        foreach ($top_keywords as $key => $countKey) {
            $keyWords = new stdClass();
            $keyWords->text = $key;
            $keyWords->size = $countKey;
            $keywords[] = $keyWords;
        }

        return json_encode($keywords);
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\blocks\keywordCloud\KeywordCloudBlockPlugin', '\KeywordCloudBlockPlugin');
}
