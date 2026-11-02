<?php

use App\Dao\CategoryDao;
use App\Dao\FavoriteDao;
use App\Dao\ReviewDao;
use App\Dao\ToolDao;
use App\Dao\UserDao;

function requestBody(): array
{
    $data = Flight::request()->data->getData();
    return is_array($data) ? $data : [];
}

function jsonOk(mixed $payload, int $status = 200): void
{
    Flight::json($payload, $status);
}

function jsonError(string $message, int $status): void
{
    Flight::json(['error' => $message], $status);
}

function queryParam(string $key): ?string
{
    $value = Flight::request()->query->{$key} ?? null;
    if ($value === null || $value === '') {
        return null;
    }

    return (string) $value;
}

/**
 * @param callable(): mixed $callback
 */
function respond(callable $callback, int $successStatus = 200): void
{
    try {
        $result = $callback();
        jsonOk($result, $successStatus);
    } catch (InvalidArgumentException $e) {
        jsonError($e->getMessage(), 400);
    } catch (Throwable $e) {
        jsonError($e->getMessage(), 500);
    }
}

/**
 * @param callable(): mixed $callback
 */
function respondOr404(callable $callback, string $notFoundMessage): void
{
    try {
        $result = $callback();
        if ($result === null || $result === false) {
            jsonError($notFoundMessage, 404);
            return;
        }
        jsonOk($result === true ? ['deleted' => true] : $result);
    } catch (InvalidArgumentException $e) {
        jsonError($e->getMessage(), 400);
    } catch (Throwable $e) {
        jsonError($e->getMessage(), 500);
    }
}

function requireFields(array $data, array $fields): ?string
{
    foreach ($fields as $field) {
        if (!array_key_exists($field, $data) || $data[$field] === '' || $data[$field] === null) {
            return "Missing required field: {$field}";
        }
    }

    return null;
}

Flight::route('GET /', function () {
    jsonOk([
        'name' => 'Bushcraft Tools API',
        'milestone' => 2,
        'entities' => ['users', 'categories', 'tools', 'reviews', 'favorites'],
    ]);
});

// --- Users -----------------------------------------------------------------

Flight::route('GET /users', function () {
    respond(static fn () => (new UserDao())->getAll());
});

Flight::route('GET /users/@id', function ($id) {
    respondOr404(static fn () => (new UserDao())->getById((int) $id), 'User not found');
});

Flight::route('POST /users', function () {
    $data = requestBody();
    $missing = requireFields($data, ['email', 'password', 'display_name']);
    if ($missing !== null) {
        jsonError($missing, 400);
        return;
    }

    respond(static fn () => (new UserDao())->insert($data), 201);
});

Flight::route('PUT /users/@id', function ($id) {
    $data = requestBody();
    $missing = requireFields($data, ['email', 'display_name', 'role']);
    if ($missing !== null) {
        jsonError($missing, 400);
        return;
    }

    respondOr404(static fn () => (new UserDao())->update((int) $id, $data), 'User not found');
});

Flight::route('PATCH /users/@id', function ($id) {
    respondOr404(static fn () => (new UserDao())->update((int) $id, requestBody()), 'User not found');
});

Flight::route('DELETE /users/@id', function ($id) {
    respondOr404(static fn () => (new UserDao())->delete((int) $id), 'User not found');
});

// --- Categories ------------------------------------------------------------

Flight::route('GET /categories', function () {
    respond(static fn () => (new CategoryDao())->getAll());
});

Flight::route('GET /categories/@id', function ($id) {
    respondOr404(static fn () => (new CategoryDao())->getById((int) $id), 'Category not found');
});

Flight::route('POST /categories', function () {
    $data = requestBody();
    $missing = requireFields($data, ['name', 'slug']);
    if ($missing !== null) {
        jsonError($missing, 400);
        return;
    }

    respond(static fn () => (new CategoryDao())->insert($data), 201);
});

Flight::route('PUT /categories/@id', function ($id) {
    $data = requestBody();
    $missing = requireFields($data, ['name', 'slug']);
    if ($missing !== null) {
        jsonError($missing, 400);
        return;
    }

    respondOr404(static fn () => (new CategoryDao())->update((int) $id, $data), 'Category not found');
});

Flight::route('PATCH /categories/@id', function ($id) {
    respondOr404(static fn () => (new CategoryDao())->update((int) $id, requestBody()), 'Category not found');
});

Flight::route('DELETE /categories/@id', function ($id) {
    respondOr404(static fn () => (new CategoryDao())->delete((int) $id), 'Category not found');
});

// --- Tools -----------------------------------------------------------------

Flight::route('GET /tools', function () {
    respond(static function () {
        $dao = new ToolDao();
        $categoryId = queryParam('category_id');
        if ($categoryId !== null) {
            return $dao->getByCategoryId((int) $categoryId);
        }

        return $dao->getAll();
    });
});

Flight::route('GET /tools/@id', function ($id) {
    respondOr404(static fn () => (new ToolDao())->getById((int) $id), 'Tool not found');
});

Flight::route('POST /tools', function () {
    $data = requestBody();
    $missing = requireFields($data, ['category_id', 'name', 'price']);
    if ($missing !== null) {
        jsonError($missing, 400);
        return;
    }

    respond(static fn () => (new ToolDao())->insert($data), 201);
});

Flight::route('PUT /tools/@id', function ($id) {
    $data = requestBody();
    $missing = requireFields($data, ['category_id', 'name', 'price', 'stock']);
    if ($missing !== null) {
        jsonError($missing, 400);
        return;
    }

    respondOr404(static fn () => (new ToolDao())->update((int) $id, $data), 'Tool not found');
});

Flight::route('PATCH /tools/@id', function ($id) {
    respondOr404(static fn () => (new ToolDao())->update((int) $id, requestBody()), 'Tool not found');
});

Flight::route('DELETE /tools/@id', function ($id) {
    respondOr404(static fn () => (new ToolDao())->delete((int) $id), 'Tool not found');
});

// --- Reviews ---------------------------------------------------------------

Flight::route('GET /reviews', function () {
    respond(static function () {
        $dao = new ReviewDao();
        $toolId = queryParam('tool_id');
        if ($toolId !== null) {
            return $dao->getByToolId((int) $toolId);
        }
        $userId = queryParam('user_id');
        if ($userId !== null) {
            return $dao->getByUserId((int) $userId);
        }

        return $dao->getAll();
    });
});

Flight::route('GET /reviews/@id', function ($id) {
    respondOr404(static fn () => (new ReviewDao())->getById((int) $id), 'Review not found');
});

Flight::route('POST /reviews', function () {
    $data = requestBody();
    $missing = requireFields($data, ['user_id', 'tool_id', 'rating']);
    if ($missing !== null) {
        jsonError($missing, 400);
        return;
    }

    $rating = (int) $data['rating'];
    if ($rating < 1 || $rating > 5) {
        jsonError('Rating must be between 1 and 5', 400);
        return;
    }

    respond(static fn () => (new ReviewDao())->insert($data), 201);
});

Flight::route('PUT /reviews/@id', function ($id) {
    $data = requestBody();
    $missing = requireFields($data, ['user_id', 'tool_id', 'rating']);
    if ($missing !== null) {
        jsonError($missing, 400);
        return;
    }

    $review = (new ReviewDao())->update((int) $id, $data);
    $review === null ? jsonError('Review not found', 404) : jsonOk($review);
});

Flight::route('PATCH /reviews/@id', function ($id) {
    $data = requestBody();
    if (isset($data['rating'])) {
        $rating = (int) $data['rating'];
        if ($rating < 1 || $rating > 5) {
            jsonError('Rating must be between 1 and 5', 400);
            return;
        }
    }

    respondOr404(static fn () => (new ReviewDao())->update((int) $id, $data), 'Review not found');
});

Flight::route('DELETE /reviews/@id', function ($id) {
    respondOr404(static fn () => (new ReviewDao())->delete((int) $id), 'Review not found');
});

// --- Favorites -------------------------------------------------------------

Flight::route('GET /favorites', function () {
    respond(static function () {
        $dao = new FavoriteDao();
        $userId = queryParam('user_id');
        if ($userId !== null) {
            return $dao->getByUserId((int) $userId);
        }

        return $dao->getAll();
    });
});

Flight::route('GET /favorites/@id', function ($id) {
    respondOr404(static fn () => (new FavoriteDao())->getById((int) $id), 'Favorite not found');
});

Flight::route('POST /favorites', function () {
    $data = requestBody();
    $missing = requireFields($data, ['user_id', 'tool_id']);
    if ($missing !== null) {
        jsonError($missing, 400);
        return;
    }

    respond(static fn () => (new FavoriteDao())->insert($data), 201);
});

Flight::route('PUT /favorites/@id', function ($id) {
    $data = requestBody();
    $missing = requireFields($data, ['user_id', 'tool_id']);
    if ($missing !== null) {
        jsonError($missing, 400);
        return;
    }

    respondOr404(static fn () => (new FavoriteDao())->update((int) $id, $data), 'Favorite not found');
});

Flight::route('PATCH /favorites/@id', function ($id) {
    respondOr404(static fn () => (new FavoriteDao())->update((int) $id, requestBody()), 'Favorite not found');
});

Flight::route('DELETE /favorites/@id', function ($id) {
    respondOr404(static fn () => (new FavoriteDao())->delete((int) $id), 'Favorite not found');
});
