<?php

namespace Mattjmattj\Holdem\RangeParser;

final class RangeParser
{
    public function split(string $range): array
    {
        $hands = [];
        $subranges = explode(',', $range);
        foreach ($subranges as $subrange) {
            $subrangeHands = $this->splitSubrange($subrange);
            $hands = [...$hands, ...$subrangeHands];
        }
        $hands = array_unique($hands);
        return $hands;
    }

    private function splitSubrange(string $subrange): array
    {
        static $cards = '23456789TJQKA';

        // single card
        if (preg_match('/^[2-9TJQKA]{2}[so]?$/', $subrange)) {
            return [$subrange];
        }

        $r = [];

        // +
        if (preg_match('/^(?<hicard>[2-9TJQKA])(?<locard>[2-9TJQKA])(?<suit>[so]?)\+$/', $subrange, $matches)) {
            // pairs
            if ($matches['hicard'] === $matches['locard']) {
                $k = strpos($cards, $matches['hicard']);
                for ($i = $k; $i < strlen($cards); ++$i) {
                    $r[] = "$cards[$i]$cards[$i]";
                }
            } else { //other + ranges
                $kHi = strpos($cards, $matches['hicard']);
                $kLo = strpos($cards, $matches['locard']);
                $suit = $matches['suit'] ?? '';
                for ($i = $kLo; $i < $kHi; ++$i) {
                    $r[] = "$cards[$kHi]$cards[$i]$suit";
                }
            }
        }
        // XY-WZ ranges
        //  pairs
        elseif (preg_match('/^(?<hicard>[2-9TJQKA])\1-(?<locard>[2-9TJQKA])\2$/', $subrange, $matches)) {
            $kHi = strpos($cards, $matches['hicard']);
            $kLo = strpos($cards, $matches['locard']);
            for ($i = $kLo; $i <= $kHi; ++$i) {
                $r[] = "$cards[$i]$cards[$i]";
            }
        }
        //  KTs-K6s kind of range
        elseif (preg_match('/^(?<hicard>[2-9TJQKA])(?<toplocard>[2-9TJQKA])(?<suit>[so])-\1(?<bottomlocard>[2-9TJQKA])\3$/', $subrange, $matches)) {
            $kBottomLo = strpos($cards, $matches['bottomlocard']);
            $kTopLo = strpos($cards, $matches['toplocard']);
            $suit = $matches['suit'] ?? '';
            for ($i = $kBottomLo; $i <= $kTopLo; ++$i) {
                $r[] = "$matches[hicard]$cards[$i]$suit";
            }
        }

        return $r;
    }

    public function compact(array $hands): string
    {
        if (1 === count($hands)) {
            return reset($hands);
        }

        $pairs = [
            'A' => false, 'K' => false, 'Q' => false, 'J' => false, 'T' => false,
            '9' => false, '8' => false, '7' => false, '6' => false, '5' => false,
            '4' => false, '3' => false, '2' => false,
        ];

        $suited = [
            'A' => [
                'K' => false, 'Q' => false, 'J' => false, 'T' => false,
                '9' => false, '8' => false, '7' => false, '6' => false, '5' => false,
                '4' => false, '3' => false, '2' => false,
            ],
            'K' => [
                'Q' => false, 'J' => false, 'T' => false,
                '9' => false, '8' => false, '7' => false, '6' => false, '5' => false,
                '4' => false, '3' => false, '2' => false,
            ],
            'Q' => [
                'J' => false, 'T' => false,
                '9' => false, '8' => false, '7' => false, '6' => false, '5' => false,
                '4' => false, '3' => false, '2' => false,
            ],
            'J' => [
                'T' => false,
                '9' => false, '8' => false, '7' => false, '6' => false, '5' => false,
                '4' => false, '3' => false, '2' => false,
            ],
            'T' => [
                '9' => false, '8' => false, '7' => false, '6' => false, '5' => false,
                '4' => false, '3' => false, '2' => false,
            ],
            '9' => [
                '8' => false, '7' => false, '6' => false, '5' => false,
                '4' => false, '3' => false, '2' => false,
            ],
            '8' => [
                '7' => false, '6' => false, '5' => false,
                '4' => false, '3' => false, '2' => false,
            ],
            '7' => [
                '6' => false, '5' => false,
                '4' => false, '3' => false, '2' => false,
            ],
            '6' => [
                '5' => false,
                '4' => false, '3' => false, '2' => false,
            ],
            '5' => [
                '4' => false, '3' => false, '2' => false,
            ],
            '4' => [
                '3' => false, '2' => false,
            ],
            '3' => [
                '2' => false,
            ],
        ];

        $offsuited = $suited;

        foreach ($hands as $k => $hand) {
            if (preg_match('/^([AKQJT2-9])\1$/', $hand, $matches)) {
                $pairs[$matches[1]] = true;
            } elseif (preg_match('/^(?<hi>[AKQJT2-9])(?<lo>[AKQJT2-9])(?<suit>[so])$/', $hand, $matches)) {
                if ('s' === $matches['suit']) {
                    $suited[$matches['hi']][$matches['lo']] = true;
                } else {
                    $offsuited[$matches['hi']][$matches['lo']] = true;
                }
            }
        }

        $rangeParts = [];

        $currentRange = [];
        foreach ($pairs as $card => $hasPair) {
            if ($hasPair) {
                $currentRange[] = "$card$card";
            } elseif (!empty($currentRange)) {
                $rangeParts[] = $this->compactSubRange($currentRange, 'AA');
                $currentRange = [];
            }
        }

        $rangeParts = [
            ...$rangeParts,
            ...$this->compactNonPairs($suited, 's'),
            ...$this->compactNonPairs($offsuited, 'o'),
        ];

        return implode(',', $rangeParts);
    }

    private function compactNonPairs(array $tree, string $suit): array
    {
        static $cards = '23456789TJQKA';
        $rangeParts = [];
        foreach ($tree as $hiCard => $otherCards) {
            $currentRange = [];
            $topHand = $hiCard . $cards[strpos($cards, $hiCard) - 1] . $suit;
            foreach ($otherCards as $otherCard => $hasCard) {
                if ($hasCard) {
                    $currentRange[] = "$hiCard$otherCard$suit";
                } elseif (!empty($currentRange)) {
                    $rangeParts[] = $this->compactSubRange($currentRange, $topHand);
                    $currentRange = [];
                }
            }
        }
        return $rangeParts;
    }

    private function compactSubRange(array $subrange, string $topHand): string
    {
        $first = array_shift($subrange);
        $last = array_pop($subrange);
        if (isset($last)) {
            if ($topHand === $first) {
                return "${last}+";
            } else {
                return "${first}-${last}";
            }
        } else {
            return "${first}";
        }
    }
}
