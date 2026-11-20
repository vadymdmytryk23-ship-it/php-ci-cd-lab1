<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../../app/Services/DataProcessor.php';

final class DataProcessorTest extends TestCase
{
    public function test_natural_numbers_sum(): void
    {
        $this->assertSame(66, sumNaturalNumbers([12, 7, 25, 4, 18]));
    }

    public function test_last_three_words(): void
    {
        $text = 'Система створює рахунки за комунальні послуги';

        $this->assertSame(['за', 'комунальні', 'послуги'], getLastThreeWords($text));
    }

    public function test_words_are_shuffled_without_loss(): void
    {
        $text = 'один два три чотири';
        $result = explode(' ', shuffleWords($text));
        $expected = explode(' ', $text);

        sort($result);
        sort($expected);

        $this->assertSame($expected, $result);
    }
}
