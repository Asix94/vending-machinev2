<?php

namespace App\Controller;

use PDO;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

final class HelloController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function __invoke(): Response
    {
        try {
            $connection = new PDO(
                sprintf(
                    'pgsql:host=%s;port=%s;dbname=%s',
                    getenv('DB_HOST') ?: 'db',
                    getenv('DB_PORT') ?: '5432',
                    getenv('POSTGRES_DB') ?: 'app',
                ),
                getenv('POSTGRES_USER') ?: 'app',
                getenv('POSTGRES_PASSWORD') ?: 'app',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
            );
            $connection->query('SELECT 1');
        } catch (Throwable) {
            return new Response('Environment unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return new Response(
            '<!doctype html><html lang="en"><meta charset="utf-8"><title>App template</title>'
            .'<body><main><h1>Hello World</h1><p>Docker environment is working.</p></main></body></html>',
        );
    }
}
