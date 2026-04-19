<?php
// ============================================================
// Fargny Booking System — API Router
// ============================================================

require_once __DIR__ . '/config.php';
cors_headers();

// Parse the request path relative to /api/
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$basePath = dirname($_SERVER['SCRIPT_NAME']); // e.g. /api
$path = substr(parse_url($requestUri, PHP_URL_PATH), strlen($basePath));
$path = '/' . ltrim($path, '/');
$method = $_SERVER['REQUEST_METHOD'];

// Simple router: match path segments
$segments = array_values(array_filter(explode('/', $path)));
$resource = $segments[0] ?? '';
$action   = $segments[1] ?? '';
$id       = $segments[2] ?? '';

// Route to the appropriate handler file
switch ($resource) {
    case 'auth':
        require_once __DIR__ . '/auth.php';
        handle_auth($action, $method);
        break;

    case 'bookings':
        require_once __DIR__ . '/bookings.php';
        handle_bookings($action, $id, $method);
        break;

    case 'branches':
        require_once __DIR__ . '/branches.php';
        handle_branches($action, $method);
        break;

    case 'users':
        require_once __DIR__ . '/users.php';
        handle_users($action, $method);
        break;

    case 'admin':
        require_once __DIR__ . '/admin.php';
        handle_admin($action, $id, $method);
        break;

    case 'feedback':
        require_once __DIR__ . '/feedback.php';
        handle_feedback($action, $method);
        break;

    case 'phase-config':
        require_once __DIR__ . '/phase-config.php';
        handle_phase_config($action, $method);
        break;

    case 'board-events':
        require_once __DIR__ . '/board-events.php';
        handle_board_events($action, $id, $method);
        break;

    case 'payments':
        require_once __DIR__ . '/payments.php';
        handle_payments($action, $method);
        break;

    case 'stats':
        require_once __DIR__ . '/stats.php';
        handle_stats($method);
        break;

    case 'email':
        require_once __DIR__ . '/email.php';
        // Email is only called internally by other endpoints
        json_error('Not a public endpoint', 404);
        break;

    default:
        json_response([
            'success' => true,
            'message' => 'Fargny Booking API',
            'version' => '1.0.0',
            'endpoints' => ['auth', 'bookings', 'branches', 'users', 'admin', 'feedback', 'phase-config', 'board-events', 'payments', 'stats']
        ]);
}
