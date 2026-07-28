<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PopController;
use App\Http\Controllers\OltController;
use App\Http\Controllers\PonController;
use App\Http\Controllers\OdcController;
use App\Http\Controllers\OdpController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PoleController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\PonPortController;
use App\Http\Controllers\OltPortController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ExportController;

// =========================================================================
// API ROUTES (Supports legacy .php extension and both standard and prefixed URLs)
// =========================================================================

$registerApiRoutes = function () {
    Route::any('/auth.php', [AuthController::class, 'handle']);
    Route::any('/pop.php', [PopController::class, 'handle']);
    Route::any('/olt.php', [OltController::class, 'handle']);
    Route::any('/pon.php', [PonController::class, 'handle']);
    Route::any('/odc.php', [OdcController::class, 'handle']);
    Route::any('/odp.php', [OdpController::class, 'handle']);
    Route::any('/users.php', [UserController::class, 'handle']);
    Route::any('/pole.php', [PoleController::class, 'handle']);
    Route::any('/ports.php', [PortController::class, 'handle']);
    Route::any('/pon-port.php', [PonPortController::class, 'handle']);
    Route::any('/olt-port.php', [OltPortController::class, 'handle']);
    Route::any('/upload.php', [UploadController::class, 'handle']);
    Route::any('/export.php', [ExportController::class, 'handle']);
};

Route::prefix('api')->group($registerApiRoutes);
Route::prefix('fiber-manager/api')->group($registerApiRoutes);

// =========================================================================
// WEB VIEWS ROUTES (Supports legacy .html URLs and clean routing paths)
// =========================================================================

$registerWebRoutes = function () {
    Route::get('/', function () { return view('index'); });
    Route::get('/index.html', function () { return view('index'); });
    
    Route::get('/login', function () { return view('login'); })->name('login');
    Route::get('/login.html', function () { return view('login'); });
    
    Route::get('/pop-management', function () { return view('pop-management'); });
    Route::get('/pop-management.html', function () { return view('pop-management'); });
    
    Route::get('/users', function () { return view('users'); });
    Route::get('/users.html', function () { return view('users'); });
    
    Route::get('/export', function () { return view('export'); });
    Route::get('/export.html', function () { return view('export'); });
};

$registerWebRoutes();
Route::prefix('fiber-manager')->group($registerWebRoutes);
