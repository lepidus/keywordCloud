 
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

import('lib.pkp.classes.plugins.BlockPlugin');
import('classes.article.ArticleDAO');

class KeywordCloudBlockPlugin extends BlockPlugin {
	/**
	 * Install default settings on journal creation.
	 * @return string
	 */
	function getContextSpecificPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return __('plugins.block.keywordCloud.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.block.keywordCloud.description');
	}

	/**
	 * @see BlockPlugin::getContents
	 */
	function getContents($templateMgr, $request = null) {
		$journal = $request->getJournal();
		if (!$journal) return '';
		
		$cacheManager = CacheManager::getManager();
		$cache = $cacheManager->getFileCache(
			$journal->getId(),
			'keywords_'. $locale,
			null
		);

		$keywords =& $cache->getContents();
		if (time() - $cache->getCacheTime() > 60 * 60 * 24 * KEYWORD_BLOCK_CACHE_DAYS){
			$cache->flush();
			$cache->setEntireCache($this->getKeywordsJournal($journal->getId()));
		}
		else if ($keywords == "[]"){
			$cache->setEntireCache($this->getKeywordsJournal($journal->getId()));
		}
		
		$templateMgr->addJavaScript('d3','https://d3js.org/d3.v4.js');
		$templateMgr->addJavaScript('d3-cloud','https://cdn.jsdelivr.net/gh/holtzy/D3-graph-gallery@master/LIB/d3.layout.cloud.js');

		$templateMgr->assign('keywords', $keywords);
		return parent::getContents($templateMgr, $request);
	}
	
	function getKeywordsJournal($journalId){
		$publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
		$publishedArticles =& $publishedArticleDao->getPublishedArticlesByJournalId($journalId, $rangeInfo = null, $reverse = true);
		
		$submissionKeywordDao = DAORegistry::getDAO('SubmissionKeywordDAO');
		
		//Get all Keywords from all published articles of this journal
		$all_keywords = array();
		$currentLocale = AppLocale::getLocale();
		while ($article = $publishedArticles->next()) {
			$articleId = $article->getId();
			$article_keywords = $submissionKeywordDao->getKeywords($articleId, array($currentLocale));

			if(count($article_keywords) > 0) {
				$all_keywords = array_merge($all_keywords, $article_keywords[$currentLocale]);
			}	
		}
		
		//Count the keywords and sort them in a frequency basis
		$count_keywords = array_count_values($all_keywords);
		arsort($count_keywords, SORT_NUMERIC);

		// Put only the most often used keywords in an array
		// maximum of KEYWORD_BLOCK_MAX_ITEMS
		$top_keywords = array_slice($count_keywords, 0, KEYWORD_BLOCK_MAX_ITEMS);
		$keywords = array();

		foreach ($top_keywords as $k => $c) {
			$kw = new stdClass();
			$kw->text = $k;
			$kw->size = $c;
			$keywords[] = $kw;
		}
		
		return json_encode($keywords);
	}
	
	function getJavaScriptURL($request) {
		return $request->getBaseUrl() . '/' . $this->getPluginPath() . '/js/';
	}
}
