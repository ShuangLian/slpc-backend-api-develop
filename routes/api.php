<?php

use App\Models\PermissionConstraint;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1'], function () {
    Route::post('login', [\App\Http\Controllers\API\AuthController::class, 'login']);
    Route::post('register', [\App\Http\Controllers\API\AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('users', \App\Http\Controllers\API\UserController::class);
        Route::apiResource('activity-checkins', \App\Http\Controllers\API\ActivityCheckinController::class);
        Route::apiResource('intercessions', \App\Http\Controllers\API\IntercessionController::class);
        Route::get('homepage', [\App\Http\Controllers\API\HomepageController::class, 'index']);
        Route::get('country-codes', [\App\Http\Controllers\API\CountryCodeController::class, 'index']);
        Route::post('images', [\App\Http\Controllers\API\ImageController::class, 'uploadImages']);
        Route::get('dedications', [\App\Http\Controllers\API\DedicationController::class, 'index']);
        Route::get('dedications/summary', [\App\Http\Controllers\API\DedicationController::class, 'summary']);
        Route::post('user-profiles', [\App\Http\Controllers\API\UserProfileController::class, 'updateIdentifyId']);
        Route::get('account-titles', [\App\Http\Controllers\API\AccountTitleController::class, 'index']);
        Route::get('church-roles', [\App\Http\Controllers\API\ChurchRoleController::class, 'index']);
        Route::get('ministries', [\App\Http\Controllers\API\MinistryController::class, 'index']);
        Route::apiResource('reviews', \App\Http\Controllers\API\ReviewController::class)
            ->only(['index', 'destroy']);
        Route::apiResource('postal-codes', \App\Http\Controllers\API\PostalCodeController::class)
            ->only(['index']);
    });
});

Route::group(['prefix' => 'admin'], function () {
    Route::post('auth/signup', [\App\Http\Controllers\API\Admin\AuthController::class, 'signup']);
    Route::post('auth/login', [\App\Http\Controllers\API\Admin\AuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::middleware('permission:' . PermissionConstraint::PAGE_USER)->group(function () {
            Route::apiResource('users', \App\Http\Controllers\API\Admin\UserController::class);
            Route::post('users/restore/{user_id}', [\App\Http\Controllers\API\Admin\UserController::class, 'restore']);
        });

        Route::middleware('permission:' . PermissionConstraint::PAGE_EVENT)->group(function () {
            Route::apiResource('events', \App\Http\Controllers\API\Admin\ActivityController::class);
            Route::apiResource('series-events', \App\Http\Controllers\API\Admin\SeriesActivityController::class);
            Route::apiResource('event-categories', \App\Http\Controllers\API\Admin\ActivityCategoryController::class);
            Route::get('event-checkins', [\App\Http\Controllers\API\Admin\ActivityCheckinController::class, 'index']);
            Route::get('event-download', [\App\Http\Controllers\API\Admin\ActivityCheckinController::class, 'download']);
        });

        Route::middleware('permission:' . PermissionConstraint::PAGE_EQUIPMENT)->group(function () {
            Route::apiResource('equipments', \App\Http\Controllers\API\Admin\ActivityController::class);
            Route::apiResource('series-equipments', \App\Http\Controllers\API\Admin\SeriesActivityController::class);
            Route::apiResource('equipment-categories', \App\Http\Controllers\API\Admin\ActivityCategoryController::class);
            Route::get('equipment-checkins', [\App\Http\Controllers\API\Admin\ActivityCheckinController::class, 'index']);
        });

        Route::middleware('permission:' . PermissionConstraint::PAGE_VISIT)->group(function () {
            Route::apiResource('visits', \App\Http\Controllers\API\Admin\VisitController::class);
            Route::post('visit-plans', [\App\Http\Controllers\API\Admin\VisitPlanController::class, 'store']);
            Route::get('visits-summary', [\App\Http\Controllers\API\Admin\VisitController::class, 'summary']);
            Route::get('visits-export', [\App\Http\Controllers\API\Admin\VisitController::class, 'export']);
            Route::get('visit-reasons', [\App\Http\Controllers\API\Admin\VisitReasonController::class, 'index']);
        });

        Route::middleware('permission:' . PermissionConstraint::PAGE_INTERCESSION)->group(function () {
            Route::apiResource('intercessions', \App\Http\Controllers\API\Admin\IntercessionController::class);
            Route::post('intercessions/printed-status', [\App\Http\Controllers\API\Admin\IntercessionController::class, 'updatePrintedStatus']);
        });

        Route::middleware('permission:' . PermissionConstraint::PAGE_DEDICATION)->group(function () {
            Route::apiResource('dedication', \App\Http\Controllers\API\Admin\DedicationController::class);
            Route::get('account-titles', [\App\Http\Controllers\API\Admin\AccountTitleController::class, 'index']);
            Route::post('dedication/upload', [\App\Http\Controllers\API\Admin\DedicationController::class, 'dedicationFormatCheck']);
        });

        Route::middleware('permission:' . PermissionConstraint::PAGE_REVIEW)->group(function () {
            Route::apiResource('reviews', \App\Http\Controllers\API\Admin\ReviewController::class);
            Route::get('reviews-homepage', [\App\Http\Controllers\API\Admin\ReviewController::class, 'homepage']);
        });

        Route::middleware('permission:' . PermissionConstraint::PAGE_PERMISSION)->group(function () {
            Route::apiResource('roles', \App\Http\Controllers\API\Admin\RoleController::class);
            Route::apiResource('permission-constraints', \App\Http\Controllers\API\Admin\PermissionConstraintController::class);
        });

        //todo delete path
        Route::apiResource('activities', \App\Http\Controllers\API\Admin\ActivityController::class);
        Route::apiResource('series-activities', \App\Http\Controllers\API\Admin\SeriesActivityController::class);
        Route::apiResource('activity-categories', \App\Http\Controllers\API\Admin\ActivityCategoryController::class);
        Route::get('activity-checkins', [\App\Http\Controllers\API\Admin\ActivityCheckinController::class, 'index']);
        

        Route::apiResource('admins', \App\Http\Controllers\API\Admin\AdminController::class);
        Route::get('country-codes', [\App\Http\Controllers\API\Admin\CountryCodeController::class, 'index']);
        Route::get('homepage', [\App\Http\Controllers\API\Admin\HomepageController::class, 'index']);
        Route::get('homepage/birthday', [\App\Http\Controllers\API\Admin\HomepageController::class, 'birthday']);
        Route::get('homepage/activities', [\App\Http\Controllers\API\Admin\HomepageController::class, 'activities']);
        Route::get('homepage/activity-checkins/count', [\App\Http\Controllers\API\Admin\HomepageController::class, 'activityCheckinsCount']);
        Route::post('auth/logout', [\App\Http\Controllers\API\Admin\AuthController::class, 'logout']);
        Route::apiResource('zones', \App\Http\Controllers\API\Admin\ZoneController::class);
        Route::apiResource('ministries', \App\Http\Controllers\API\Admin\MinistryController::class);

        Route::post('permission-constraints/import', [\App\Http\Controllers\API\Admin\ImportController::class, 'permissionConstraintImport']);
        Route::get('users-export', [\App\Http\Controllers\API\Admin\UserController::class, 'exportUserNotRegistered']);
        Route::apiResource('postal-codes', \App\Http\Controllers\API\Admin\PostalCodeController::class)
            ->only(['index']);

        Route::post('users/zone/auto-generate', [\App\Http\Controllers\API\Admin\UserController::class, 'addZoneIdFromPostalCodeId']);
    });
});
