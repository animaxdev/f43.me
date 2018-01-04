<?php

namespace Tests\FeedBundle\Converter;

use Api43\FeedBundle\Converter\ConverterChain;
use PHPUnit\Framework\TestCase;

class ConverterChainTest extends TestCase
{
    public function testConvert()
    {
        $converter = $this->getMockBuilder('Api43\FeedBundle\Converter\AbstractConverter')
            ->disableOriginalConstructor()
            ->getMock();

        $converter->expects($this->once())
            ->method('convert')
            ->will($this->returnValue('changed'));

        $converterChain = new ConverterChain();
        $converterChain->addConverter($converter, 'alias');

        $this->assertSame('changed', $converterChain->convert('url'));
    }
}
