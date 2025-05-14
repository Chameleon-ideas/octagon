<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SportController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\API\FirebaseController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', [AdminController::class, 'index']);
Route::post('/checklogin', [AdminController::class, 'checkLogin']);
Route::get('/login', [AdminController::class, 'index'])->name('login');
Route::get('/successlogin', [AdminController::class, 'successLogin']);
Route::get('/logout', [AdminController::class, 'logout'])->name('logout');
Route::get('/errorlogin', [AdminController::class, 'errorLogin'])->name('errorlogin');

Route::get('/sendNotification', [FirebaseController::class, 'sendNotification']);

Route::get('/account-setting', [AdminController::class, 'accountSetting']);


Route::get('/users-list', [AdminController::class, 'usersList']);
Route::post('/save-user', [AdminController::class, 'saveUser']);
Route::post('/delete-user', [AdminController::class, 'deleteUser']);
Route::post('/delete-records', [AdminController::class, 'deleteRecords']);
Route::post('/upload-photo', [AdminController::class, 'uploadPhoto']);
Route::post('/change-password', [AdminController::class, 'changePassword']);
Route::get('/forgot-password', [AdminController::class, 'forgotPassword']);
Route::post('/password-mail', [AdminController::class, 'passwordMail']);
Route::post('/reset-password', [AdminController::class, 'resetPassword']);

Route::get('/posts-list', [AdminController::class, 'postsList'])->name('posts-list');
Route::post('/save-post', [AdminController::class, 'savePost']);
Route::post('/delete-post', [AdminController::class, 'deletePost']);
Route::post('/delete-post-report', [AdminController::class, 'deletePostReport']);
Route::get('/post-report-list', [AdminController::class, 'postreportList'])->name('post-report-list');

Route::get('/sports-list', [AdminController::class, 'sportsList']);
Route::post('/save-sport', [AdminController::class, 'saveSport']);
Route::post('/delete-sport', [AdminController::class, 'deleteSport']);

Route::get('/dashboard', [DashboardController::class, 'index']);
Route::post('storeSportDB', [SportController::class, 'storeSportDB']);
Route::post('storeTeamDB', [TeamController::class, 'storeTeamDB']);


Route::get('comment-list', [TestController::class, 'commentList']);

Route::get('teams-list', [AdminController::class, 'teamsList']);
Route::post('delete-team', [AdminController::class, 'deleteTeam']);
Route::post('save-team', [AdminController::class, 'saveTeam']);

