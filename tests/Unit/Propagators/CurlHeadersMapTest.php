<?php

namespace DDTrace\Tests\Unit\Propagators;

use DDTrace\Propagators\CurlHeadersMap;
use DDTrace\SpanContext;
use PHPUnit\Framework;

final class CurlHeadersMapTest extends Framework\TestCase
{
    const BAGGAGE_ITEM_KEY = 'test_key';
    const BAGGAGE_ITEM_VALUE = 'test_value';
    const TRACE_ID = '1c42b4de015cc315';
    const SPAN_ID = '1c42b4de015cc316';

    public function testInjectSpanContextIntoCarrier()
    {

        $rootContext = SpanContext::createAsRoot([self::BAGGAGE_ITEM_KEY => self::BAGGAGE_ITEM_VALUE]);
        $context = SpanContext::createAsChild($rootContext);

        $carrier = [];

        (new CurlHeadersMap())->inject($context, $carrier);

        $this->assertEquals([
            'x-datadog-trace-id: ' . $rootContext->getTraceId(),
            'x-datadog-parent-id: ' . $context->getSpanId(),
            'ot-baggage-' . self::BAGGAGE_ITEM_KEY . ': ' . self::BAGGAGE_ITEM_VALUE,
        ], array_values($carrier));
    }

    public function testExistingUserHeadersAreHonored()
    {

        $rootContext = SpanContext::createAsRoot([self::BAGGAGE_ITEM_KEY => self::BAGGAGE_ITEM_VALUE]);
        $context = SpanContext::createAsChild($rootContext);

        $carrier = [
            'existing: headers',
        ];

        (new CurlHeadersMap())->inject($context, $carrier);

        $this->assertEquals([
            'existing: headers',
            'x-datadog-trace-id: ' . $rootContext->getTraceId(),
            'x-datadog-parent-id: ' . $context->getSpanId(),
            'ot-baggage-' . self::BAGGAGE_ITEM_KEY . ': ' . self::BAGGAGE_ITEM_VALUE,
        ], array_values($carrier));
    }

    public function testExistingDistributedTracingHeadersAreReplaced()
    {

        $rootContext = SpanContext::createAsRoot([self::BAGGAGE_ITEM_KEY => self::BAGGAGE_ITEM_VALUE]);
        $context = SpanContext::createAsChild($rootContext);

        $carrier = [
            'existing: headers',
            'x-datadog-trace-id: trace',
            'x-datadog-parent-id: parent',
            'ot-baggage-' . self::BAGGAGE_ITEM_KEY . ': baggage',
        ];

        (new CurlHeadersMap())->inject($context, $carrier);

        $this->assertEquals([
            'existing: headers',
            'x-datadog-trace-id: ' . $rootContext->getTraceId(),
            'x-datadog-parent-id: ' . $context->getSpanId(),
            'ot-baggage-' . self::BAGGAGE_ITEM_KEY . ': ' . self::BAGGAGE_ITEM_VALUE,
        ], array_values($carrier));
    }
}
