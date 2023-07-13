<?php
/**
 * @defgroup plugins_blocks_Keywordcloud KeywordCloud Block Plugin
 */

/**
 * @file plugins/blocks/KeywordCloud/index.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_blocks_Keywordcloud
 * @brief Wrapper for KeywordCloud block plugin.
 *
 */

require_once('KeywordCloudBlockPlugin.inc.php');

return new KeywordCloudBlockPlugin();
