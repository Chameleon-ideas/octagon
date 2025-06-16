<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\SportController;
use App\Http\Controllers\API\TeamController;
use App\Http\Controllers\API\LikeController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\LocationController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\SportController as ControllersSportController;
use App\Http\Controllers\API\UserTeamProfileController;
use App\Http\Controllers\API\UserGroupController;
use App\Http\Controllers\API\UserGroupMemberController;

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

// Update live score data cron
Route::get('v242/update_sport_score_data', [ControllersSportController::class, 'update_sport_score_data']);
Route::get('v242/add_update_sport_teams', [ControllersSportController::class, 'add_update_sport_teams']);



Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);
Route::post('social-auth', [UserController::class, 'socialAuth']);
Route::post('forgot-password', [UserController::class, 'forgotPassword']);
Route::post('otp-create', [UserController::class, 'otpCreate']);
Route::post('otp-verify', [UserController::class, 'otpVerify']);
Route::post('reset-password', [UserController::class, 'resetPassword']);
Route::post('sport-list', [SportController::class, 'sportDetails']);
Route::post('team-list', [TeamController::class, 'teamDetails']);
Route::post('sync-soccer-countries', [SportController::class, 'syncSoccerCountries']);
Route::post('sync-soccer-leagues', [SportController::class, 'syncSoccerLeagues']);
Route::post('sync-soccer-teams', [SportController::class, 'syncSoccerTeams']);
Route::post('sync-basketball-countries', [SportController::class, 'syncBasketballCountries']);
Route::post('sync-basketball-leagues', [SportController::class, 'syncBasketballLeagues']);
Route::post('sync-basketball-teams', [SportController::class, 'syncBasketballTeams']);
Route::post('sync-cricket-leagues', [SportController::class, 'syncCricketLeagues']);
Route::post('sync-cricket-teams', [SportController::class, 'syncCricketTeams']);
Route::post('sync-baseball-teams', [SportController::class, 'syncBaseballTeams']);
Route::post('sync-ice-hockey-teams', [SportController::class, 'syncIceHockeyTeams']);
Route::post('sync-cricket-hockey-logos', [SportController::class, 'syncCricketHockeyLogos']);
Route::post('sync-nfl-teams', [SportController::class, 'syncNflTeams']);

Route::post('/test-upload', function (Request $request) {
    if ($request->hasFile('video')) {
        $file = $request->file('video');
        $filename = time() . '_' . $file->getClientOriginalName();

        if (!file_exists(public_path('video/test'))) {
            mkdir(public_path('video/test'), 0755, true);
        }

        $filePath = public_path('video/test/' . $filename);
        if (move_uploaded_file($file->getRealPath(), $filePath)) {
            return 'File uploaded to ' . $filePath;
        } else {
            return 'Failed to upload the file';
        }
    } else {
        return 'No video file uploaded';
    }
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('user-details', [UserController::class, 'userDetails']);
    Route::post('user-profile', [UserController::class, 'userProfile']);
    Route::post('user-update', [UserController::class, 'userProfileUpdate']);
    Route::post('user-profile-access', [UserController::class, 'setProfileAccess']);
    Route::post('user-upload-file', [UserController::class, 'uploadFiles']);

    Route::post('user-block', [UserController::class, 'blockUser']);
    Route::post('user-remove', [UserController::class, 'removeUser']);
    Route::post('user-unblock', [UserController::class, 'unBlockUser']);
    Route::post('user-block-list', [UserController::class, 'blockUserList']);

    Route::post('user-remove-file', [UserController::class, 'deleteUserFiles']);
    Route::post('save-user-sports', [SportController::class, 'saveUserSports']);
    Route::post('save-user-teams', [TeamController::class, 'saveUserTeams']);
    Route::post('get-team-page', [TeamController::class, 'fetchTeams']);
    Route::post('save-user-post', [UserController::class, 'saveUserPost']);
    Route::post('save-user-comment', [UserController::class, 'saveUserComment']);
    Route::post('delete-user-comment', [UserController::class, 'deleteComment']);
    Route::post('get-user-comment', [UserController::class, 'getUserComment']);
    Route::post('get-user-posts', [UserController::class, 'getUserPosts']);
    Route::post('set-user-followers', [UserController::class, 'setUserFollows']);
    Route::post('get-user-followers', [UserController::class, 'getUserFollows']);
    Route::post('remove-following', [UserController::class, 'removeUserFollowing']);
    Route::post('search-users', [UserController::class, 'searchUsers']);
    Route::post('save-likes', [LikeController::class, 'saveUserLikes']);
    Route::post('get-likes', [LikeController::class, 'getLikes']);
    Route::post('get-user-post-file', [UserController::class, 'getUserPostFile']);
    Route::post('post-report', [UserController::class, 'showReportTitle']);
    Route::post('save-post-report', [UserController::class, 'reportPost']);
    Route::post('get-like-posts', [PostController::class, 'getLikedPost']);
    Route::post('save-post-favorite', [PostController::class, 'savePostFavorite']);
    Route::post('get-post-favorite', [PostController::class, 'getFavorite']);
    Route::post('save-post', [PostController::class, 'savePostSave']);
    Route::post('get-save-post', [PostController::class, 'getSavePost']);
    Route::post('location-list', [LocationController::class, 'getLocation']);
    Route::post('get-post-details', [PostController::class, 'getPostDetails']);
    Route::post('get-tranding-list', [PostController::class, 'trandingList']);
    Route::post('edit-user-post', [UserController::class, 'editUserPost']);
    Route::post('delete-user-post', [UserController::class, 'deleteUserPost']);
    Route::post('notification-list', [NotificationController::class, 'getNotification']);
    Route::post('notification-update', [NotificationController::class, 'updateNotification']);

    Route::post('user-groups-create', [UserGroupController::class, 'saveUserGroup']);
    Route::post('user-groups-get', [UserGroupController::class, 'getGroup']);
    Route::post('user-groups-get-by-id', [UserGroupController::class, 'getGroupById']);
    Route::post('user-groups-update', [UserGroupController::class, 'updateGroup']);
    Route::post('user-groups-list', [UserController::class, 'userGroupList']);
    Route::post('user-show-group', [UserGroupController::class, 'showUserGroup']);

    Route::post('groups-member-create', [UserGroupMemberController::class, 'saveUserGroupMember']);
    Route::post('groups-member-get', [UserGroupMemberController::class, 'getGroupMember']);
    Route::post('groups-member-get-by-id', [UserGroupMemberController::class, 'getGroupById']);
    Route::post('groups-member-delete', [UserGroupMemberController::class, 'deleteGroupMember']);

    Route::prefix('team-profiles')->group(function () {
        Route::post('/create', [UserTeamProfileController::class, 'create']);
        Route::post('/update/{id}', [UserTeamProfileController::class, 'update']);
        Route::post('/delete/{id}', [UserTeamProfileController::class, 'delete']);
        Route::get('/get/{id}', [UserTeamProfileController::class, 'getOne']);
        Route::get('/all', [UserTeamProfileController::class, 'getAll']);
    });

});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
