<?php

namespace Mattjmattj\RangeParser\Test;

use Mattjmattj\RangeParser\RangeParser;
use PHPUnit\Framework\TestCase;

final class RangeParserTest extends TestCase
{
    /**
     * @test
     * @dataProvider ranges
     */
    public function shouldSplitARangeIntoHands(string $range, array $hands)
    {
        $parser = new RangeParser;
        $this->assertEqualsCanonicalizing($parser->split($range), $hands);
    }


    /**
     * @test
     * @dataProvider ranges
     */
    public function shouldCompactHandsIntoARange(string $range, array $hands)
    {
        $parser = new RangeParser;
        $this->assertEqualsCanonicalizing($parser->compact($hands), $range);
    }

    public function ranges()
    {
        return [];
    }
}