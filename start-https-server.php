<?php

require_once __DIR__ . '/vendor/autoload.php';

use React\EventLoop\Factory;
use React\Http\Server;
use React\Socket\SocketServer;
use React\Socket\SecureServer;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function React\Promise\resolve;

// Load environment variables
if (file_exists('.env')) {
    (new Dotenv())->bootEnv('.env');
}

// Load the Symfony kernel
$kernel = new Kernel($_SERVER['APP_ENV'], $_SERVER['APP_DEBUG']);
$kernel->boot();

// Load the Symfony framework
$container = $kernel->getContainer();
$framework = $container->get('framework.http_cache')->getCache($kernel);

// Define the request handler
$requestHandler = function (Request $request) use ($framework) {
    $symfonyResponse = $framework->handle($request);
    return new Response(
        $symfonyResponse->getStatusCode(),
        $symfonyResponse->headers->all(),
        $symfonyResponse->getContent()
    );
};

// Create the event loop
$loop = Factory::create();

// Create a TCP server
$socket = new SocketServer('127.0.0.1:8000', null, $loop);

// Load the certificate and private key
$context = stream_context_create([
    'ssl' => [
        'local_cert' => __DIR__ . '/cert.pem',
        'local_pk' => __DIR__ . '/key.pem',
        'allow_self_signed' => true,
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]);

// Create an SSL server
$secureSocket = new SecureServer($socket, $context, $loop);

// Define the HTTP server
$httpServer = new Server(function (Request $request) use ($requestHandler) {
    return resolve($requestHandler($request));
});

// Bind the HTTP server to the SSL server
$httpServer->listen($secureSocket);

// Run the event loop
$loop->run();