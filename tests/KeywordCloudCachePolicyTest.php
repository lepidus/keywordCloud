<?php

require_once dirname(__DIR__) . '/classes/KeywordCloudCachePolicy.inc.php';

use PHPUnit\Framework\TestCase;

class KeywordCloudCachePolicyTest extends TestCase
{
    private const LIFETIME = 172800;

    public function testEmptyKeywordListIsUsableWhileFresh(): void
    {
        $this->assertTrue(KeywordCloudCachePolicy::isUsable('[]', 60, self::LIFETIME));
    }

    public function testKeywordListIsUsableWhileFresh(): void
    {
        $this->assertTrue(KeywordCloudCachePolicy::isUsable('[{"text":"ojs","size":1}]', 60, self::LIFETIME));
    }

    public function testMissingOrUnreadableCacheIsRecalculated(): void
    {
        $this->assertFalse(KeywordCloudCachePolicy::isUsable(null, 60, self::LIFETIME));
    }

    public function testExpiredCacheIsRecalculatedEvenWhenEmpty(): void
    {
        $this->assertFalse(KeywordCloudCachePolicy::isUsable('[]', self::LIFETIME, self::LIFETIME));
        $this->assertFalse(KeywordCloudCachePolicy::isUsable('[{"text":"ojs","size":1}]', self::LIFETIME + 1, self::LIFETIME));
    }
}
