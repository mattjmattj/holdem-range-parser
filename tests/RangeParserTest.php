<?php

namespace Mattjmattj\Holdem\RangeParser\Test;

use Mattjmattj\Holdem\RangeParser\RangeParser;
use PHPUnit\Framework\TestCase;

final class RangeParserTest extends TestCase
{
    /**
     * @test
     * @dataProvider ranges
     */
    public function shouldSplitARangeIntoHands(string $range, array $hands)
    {
        $parser = new RangeParser();
        $this->assertEqualsCanonicalizing($hands, $parser->split($range));
    }


    /**
     * @test
     * @dataProvider ranges
     */
    public function shouldCompactHandsIntoARange(string $range, array $hands)
    {
        $parser = new RangeParser();
        $this->assertEqualsCanonicalizing($range, $parser->compact($hands));
    }

    public function ranges()
    {
        // single hands
        yield ['AKo', ['AKo']];
        yield ['AKs', ['AKs']];
        yield ['TT', ['TT']];

        // pairs
        yield ['KK-88', ['KK','QQ','JJ','TT','99','88']];

        //
        yield ['AQo-ATo', ['AQo','AJo','ATo']];
        yield ['AQs-ATs', ['AQs','AJs','ATs']];

        // +
        yield ['ATs+', ['AKs','AQs','AJs','ATs']];
        yield ['Q7o+', ['QJo','QTo','Q9o','Q8o','Q7o']];
        yield ['88+', ['AA','KK','QQ','JJ','TT','99','88']];

        // combined ranges
        yield ['KK-TT,AQs-A9s,AKo,KJo+', ['KK','QQ','JJ','TT','AQs','AJs','ATs','A9s','AKo','KJo','KQo']];
    }
}
