<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2019  Damian Długosz
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use Bigfoot\PHPacto\Controller\MockController;
use Bigfoot\PHPacto\Factory\SerializerFactory;
use Bigfoot\PHPacto\Loader\PactLoader;
use Bigfoot\PHPacto\Logger\StdoutLogger;
use Bigfoot\PHPacto\Matcher\Mismatches\MismatchCollection;
use Http\Factory\Discovery\HttpFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

require __DIR__ . '/bootstrap.php';

if (false !== ($allowOrigin = getenv('ALLOW_ORIGIN'))) {
    if ('all' === strtolower($allowOrigin)) {
        $allowOrigin = '*';
    }
} else {
    $allowOrigin = null;
}

$logger = new StdoutLogger();

$handler = function(RequestInterface $request) use ($logger, $allowOrigin): ResponseInterface {
    if (
        isset($allowOrigin)
        && 'OPTIONS' === $request->getMethod()
        && $request->hasHeader('Access-Control-Request-Method')
    ) {
        return HttpFactory::responseFactory()->createResponse(418)
            ->withAddedHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD')
            ->withAddedHeader('Access-Control-Allow-Credentials', 'True')
            ->withAddedHeader('Access-Control-Allow-Headers', '*')
            ->withAddedHeader('Access-Control-Allow-Origin', '*');
    }

    $logger->log(sprintf(
        '[%s] %s: %s',
        date('Y-m-d H:i:s'),
        $_SERVER['REQUEST_METHOD'] ?? '',
        $_SERVER['REQUEST_URI'] ?? ''
    ));

    try {
        $headerContract = $request->getHeaderLine('PHPacto-Contract');

        $pacts = (new PactLoader(SerializerFactory::getInstance()))
            ->loadFromPath($headerContract ? CONTRACTS_DIR . $headerContract : CONTRACTS_DIR);

        if (0 === count($pacts)) {
            throw new \Exception(sprintf('No Pacts found in %s', realpath(CONTRACTS_DIR)));
        }

        $controller = new MockController($logger, $pacts);

        $response = $controller->action($request);

        $logger->log(sprintf('Pact responded with Status Code %d', $response->getStatusCode()));

        if (null !== $allowOrigin) {
            $response = $response
                ->withAddedHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD')
                ->withAddedHeader('Access-Control-Allow-Credentials', 'True')
                ->withAddedHeader('Access-Control-Allow-Headers', '*')
                ->withAddedHeader('Access-Control-Allow-Origin', $allowOrigin);
        }

        return $response;
    } catch (MismatchCollection $mismatches) {
        $stream = HttpFactory::streamFactory()->createStreamFromFile('php://memory', 'rw');
        $stream->write(json_encode([
            'message' => $mismatches->getMessage(),
            'contracts' => $mismatches->toArray(),
        ]));

        $logger->log($mismatches->getMessage());

        return HttpFactory::responseFactory()->createResponse(418)
            ->withAddedHeader('Content-type', 'application/json')
            ->withBody($stream);
    } catch (\Throwable $t) {
        function throwableToArray(\Throwable $t): array
        {
            return [
                'message' => $t->getMessage(),
                'trace' => $t->getTrace(),
                'line' => $t->getLine(),
                'file' => $t->getFile(),
                'code' => $t->getCode(),
                'previous' => $t->getPrevious() ? throwableToArray($t->getPrevious()) : null,
            ];
        };

        $stream = HttpFactory::streamFactory()->createStreamFromFile('php://memory', 'rw');
        $stream->write(json_encode(throwableToArray($t)));

        $logger->log($t->getMessage());

        return HttpFactory::responseFactory()->createResponse(418)
            ->withAddedHeader('Content-type', 'application/json')
            ->withBody($stream);
    }
};

$server = Zend\Diactoros\Server::createServer($handler, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
$server->listen();
