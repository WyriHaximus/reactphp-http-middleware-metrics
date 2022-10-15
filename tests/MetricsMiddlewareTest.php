<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use React\Http\Message\ServerRequest;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\Metrics\Configuration;
use WyriHaximus\Metrics\InMemory\Registry;
use WyriHaximus\React\Http\Middleware\MetricsMiddleware;

use function RingCentral\Psr7\stream_for;

use const WyriHaximus\Constants\HTTPStatusCodes\OK;

final class MetricsMiddlewareTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function metrics(): void
    {
        $request  = (new ServerRequest('GET', 'https://example.com/metrics'))->withBody(stream_for('foo.bar'));
        $registry = new Registry(Configuration::create());
        $registry->counter('metric', 'description')->counter()->incr();
        $middleware      = new MetricsMiddleware($registry, '/metrics');
        $metricsResponse = $this->await($middleware($request, static fn (ServerRequestInterface $request): ResponseInterface => new Response(
            OK,
            MetricsMiddleware::RESPONSE_HEADERS,
            'lol nope'
        )));

        self::assertStringContainsString('# HELP metric_total description', (string) $metricsResponse->getBody()); /** @phpstan-ignore-line */
        self::assertStringContainsString('# TYPE metric_total counter', (string) $metricsResponse->getBody()); /** @phpstan-ignore-line */
        self::assertStringContainsString('metric_total 1', (string) $metricsResponse->getBody()); /** @phpstan-ignore-line */
    }
}
