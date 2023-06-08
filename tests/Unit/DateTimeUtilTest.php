<?php

namespace Tests\Unit;

use App\Utils\DateTimeUtil;
use Carbon\Carbon;
use Tests\TestCase;

class DateTimeUtilTest extends TestCase
{
    public function testParseRepublicEra()
    {
        $this->assertEquals('2022-06-09', DateTimeUtil::parseRepublicEra('111-06-09')->format('Y-m-d'));
        $this->assertEquals('2022-06-09', DateTimeUtil::parseRepublicEra('111/06/09')->format('Y-m-d'));
        $this->assertEquals('2022-06-09', DateTimeUtil::parseRepublicEra('111,06,09')->format('Y-m-d'));
        $this->assertEquals('2022-06-09', DateTimeUtil::parseRepublicEra('111 06 09')->format('Y-m-d'));
        $this->assertEquals('2022-06-09', DateTimeUtil::parseRepublicEra('111_06_09')->format('Y-m-d'));
        $this->assertEquals(Carbon::now()->format('Y-m-d'), DateTimeUtil::parseRepublicEra('')->format('Y-m-d'));
        $this->assertEquals(Carbon::now()->format('Y-m-d'), DateTimeUtil::parseRepublicEra('error string')->format('Y-m-d'));
    }
}
