<?php

/**
 * @file plugins/blocks/keywordCloud/classes/KeywordCloudCachePolicy.inc.php
 *
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class KeywordCloudCachePolicy
 *
 * @brief Decides whether a cached keyword list can be served without recalculating it.
 */

class KeywordCloudCachePolicy
{
    /**
     * An empty list ('[]') is a valid result for a context without keywords in a locale,
     * so it is kept until the cache expires instead of being recalculated on every page.
     *
     * @param string|null $keywords Cache contents; null when the cache file is missing or unreadable
     * @param int $cacheAge Seconds since the cache was written
     * @param int $lifetime Cache lifetime in seconds
     */
    public static function isUsable($keywords, int $cacheAge, int $lifetime): bool
    {
        return $keywords !== null && $cacheAge < $lifetime;
    }
}
