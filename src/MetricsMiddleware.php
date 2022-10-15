<?php

declare(strict_types=1);

namespace WyriHaximus\React\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use React\Promise\PromiseInterface;
use WyriHaximus\Metrics\Printer\Prometheus;
use WyriHaximus\Metrics\Registry;

use function React\Promise\resolve;

use const WyriHaximus\Constants\HTTPStatusCodes\OK;

final class MetricsMiddleware
{
    public const RESPONSE_HEADERS = ['Content-Type' => 'text/plain'];

    public function __construct(
        private Registry $registry,
        private string $path,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, callable $next): PromiseInterface
    {
        if ($request->getUri()->getPath() === $this->path) {
            return resolve(new Response(OK, self::RESPONSE_HEADERS, $this->registry->print(new Prometheus())));
        }

        return $next($request);
    }
}
