<?php

declare(strict_types=1);

use App\Services\CategoryService;
use App\Services\FavoriteService;
use App\Services\ReviewService;
use App\Services\ToolService;
use App\Services\UserService;
use App\Services\Validation;
use App\Services\WeatherException;
use App\Services\WeatherService;

function requestBody(): array
{
    return Flight::request()->data->getData();
}

function respond(callable $operation, int $status = 200, string $missing = 'Record not found.'): void
{
    try {
        $result = $operation();
        if ($result === null || $result === false) {
            Flight::json(['error' => $missing], 404);
            return;
        }

        Flight::json($result === true ? ['deleted' => true] : $result, $status);
    } catch (InvalidArgumentException $exception) {
        Flight::json(['error' => $exception->getMessage()], 400);
    } catch (WeatherException $exception) {
        Flight::json(['error' => $exception->getMessage()], $exception->getCode());
    } catch (Throwable $exception) {
        error_log((string) $exception);
        Flight::json(['error' => 'An unexpected server error occurred.'], 500);
    }
}

function registerCrudRoutes(string $path, string $service, string $entity): void
{
    $missing = "$entity not found.";
    Flight::route("GET /$path", static fn () => respond(
        static fn () => (new $service())->list(Flight::request()->query->getData())
    ));
    Flight::route("POST /$path", static fn () => respond(
        static fn () => (new $service())->create(requestBody()), 201
    ));
    Flight::route("GET /$path/@id", static fn ($id) => respond(
        static fn () => (new $service())->find(Validation::integer($id, 'id')), 200, $missing
    ));
    Flight::route("PUT /$path/@id", static fn ($id) => respond(
        static fn () => (new $service())->update(Validation::integer($id, 'id'), requestBody(), true), 200, $missing
    ));
    Flight::route("PATCH /$path/@id", static fn ($id) => respond(
        static fn () => (new $service())->update(Validation::integer($id, 'id'), requestBody(), false), 200, $missing
    ));
    Flight::route("DELETE /$path/@id", static fn ($id) => respond(
        static fn () => (new $service())->delete(Validation::integer($id, 'id')), 200, $missing
    ));
}

Flight::map('notFound', static fn () => Flight::json(['error' => 'Endpoint not found.'], 404));

Flight::route('GET /', static fn () => respond(static fn () => [
    'name' => 'Bushcraft Tools API',
    'milestone' => 3,
    'entities' => ['users', 'categories', 'tools', 'reviews', 'favorites'],
]));

registerCrudRoutes('users', UserService::class, 'User');
registerCrudRoutes('categories', CategoryService::class, 'Category');
registerCrudRoutes('tools', ToolService::class, 'Tool');
registerCrudRoutes('reviews', ReviewService::class, 'Review');
registerCrudRoutes('favorites', FavoriteService::class, 'Favorite');

Flight::route('GET /weather', static fn () => respond(
    static fn () => (new WeatherService())->current(Flight::request()->query->city)
));
