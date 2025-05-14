<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sport;
use App\Models\User;
use App\Models\UserLikes;
use App\Models\UserPosts;
use App\Models\UserComments;
use App\Models\UserFollows;
use App\Models\PostFavorite;
use App\Models\PostSave;
use App\Models\UserPostImageVideo;
use App\Models\UserSport;
use App\Models\UserTeam;
use App\Models\Team;
use App\Models\Notification;
use App\Models\UserBlock;
use App\Models\FireBase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{

    public $successStatus = 200;

    public function savePostFavorite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'favorite' => 'required',
            'post_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $input = $request->all();
        $user = Auth::user();
        if (isset($input['favorite'])) {
            $favorite = $input['favorite'];
            $post_id = $input['post_id'];
            if ($favorite == 1) {
                // Add favorite
                $f = 0;
                $userPost = UserPosts::where('id', $post_id)
                    ->first();
                if ($userPost) {
                    $f = 1;
                }

                if ($f == 1) {
                    $postFavorite = PostFavorite::where('user_id', $user->id)
                        ->where('post_id', $post_id)
                        ->first();
                    if (!$postFavorite) {
                        $postFavoriteRecord = new PostFavorite();
                        $postFavoriteRecord->user_id = $user->id;
                        $postFavoriteRecord->post_id = $post_id;
                        $postFavoriteRecord->save();

                        $newNotification = new Notification();
                        $newNotification->user1 = $user->id;
                        $newNotification->user2 = $userPost->user_id;
                        $newNotification->type_id = $post_id;
                        $notMessage = $user->name . " favorites your post";
                        $newNotification->notification = $notMessage;
                        $newNotification->type = 2;
                        $newNotification->save();
                        $userDetails = User::where('id', $userPost->user_id)->first();
                        $fireBase = new FireBase();
                        $fireBase->curlCall($userDetails->fcm_token, $notMessage, $notMessage);
                    }
                }
            } else if ($favorite == 0) {
                PostFavorite::where('user_id', $user->id)
                    ->where('post_id', $post_id)
                    ->delete();
            }
            $fRecord1 = PostFavorite::where('user_id', $user->id)
                ->where('post_id', $post_id)
                ->count();
            $record = [
                'favorite' => $fRecord1
            ];
            return response()->json(['success' => $record], $this->successStatus);
            //echo "<pre>";print_r($sports);die;
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    public function savePostSave(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'save' => 'required',
            'post_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $input = $request->all();
        $user = Auth::user();
        if (isset($input['save'])) {
            $save = $input['save'];
            $post_id = $input['post_id'];
            if ($save == 1) {
                // Add save
                $f = 0;
                $userPost = UserPosts::where('id', $post_id)
                    ->first();
                if ($userPost) {
                    $f = 1;
                }

                if ($f == 1) {
                    $postSave = PostSave::where('user_id', $user->id)
                        ->where('post_id', $post_id)
                        ->first();
                    if (!$postSave) {
                        $postSaveRecord = new PostSave();
                        $postSaveRecord->user_id = $user->id;
                        $postSaveRecord->post_id = $post_id;
                        $postSaveRecord->save();

                        $newNotification = new Notification();
                        $newNotification->user1 = $user->id;
                        $newNotification->user2 = $userPost->user_id;
                        $newNotification->type_id = $post_id;
                        $notMessage = $user->name . " saves your post";
                        $newNotification->notification = $notMessage;
                        $newNotification->type = 2;
                        $newNotification->save();
                        $userDetails = User::where('id', $userPost->user_id)->first();
                        $fireBase = new FireBase();
                        $fireBase->curlCall($userDetails->fcm_token, $notMessage, $notMessage);
                        //dd('1');
                    }
                }
            } else if ($save == 0) {
                PostSave::where('user_id', $user->id)
                    ->where('post_id', $post_id)
                    ->delete();
            }
            $fRecord1 = PostSave::where('user_id', $user->id)
                ->count();
            $record = [
                'Save Post' => $fRecord1
            ];
            return response()->json(['success' => $record], $this->successStatus);
            //echo "<pre>";print_r($sports);die;
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    public function getFavorite(Request $request)
    {
        $limit = 10;
        $page = 1;
        $user = Auth::user();
        $getUserPosts1 = PostFavorite::where('user_id', $user->id)
            ->get('post_id')
            ->toArray();
        $userBlockIds = UserBlock::where('block_user_id', $user->id)
            ->pluck('user_id')->toArray();
        $getPostQuery = UserPosts::whereIn('id', $getUserPosts1)
            ->whereNotIn('user_id', $userBlockIds)
            ->where('is_deleted', '0')->orderBy('updated_at', 'desc');
        $total_rows = $getPostQuery->count();
        $total_page = isset($input['limit']) ? $input['limit'] : $limit;
        $page_size = ceil($total_rows / $total_page);
        $currentpage = isset($input['page_no']) ? $input['page_no'] : $page;
        $offset = $currentpage * $total_page - $total_page;
        $getPostQuery->take($total_page)->offset($offset);
        $getUserPosts = $getPostQuery->get()->toArray();
        //dd(\DB::getQueryLog());
        $more_page = false;
        if ($currentpage < $page_size) {
            $more_page = true;
        }
        if (count($getUserPosts) > 0) {
            $userPostIdArr = $userPostCommentsArr = $finalArr = $userPostFilesArr = $parentCommentIdArr = $childCommentArr = array();
            for ($p = 0; $p < count($getUserPosts); $p++) {
                $userPostIdArr[] = $getUserPosts[$p]['id'];
            }

            $getUserPostLikes = UserLikes::whereIn('content_id', $userPostIdArr)->where('type', 1)->get()->toArray();
            for ($l = 0; $l < count($getUserPostLikes); $l++) {
                if (isset($userPostLikeArr[$getUserPostLikes[$l]['content_id']])) {
                    $userPostLikeArr[$getUserPostLikes[$l]['content_id']] += 1;
                } else {
                    $userPostLikeArr[$getUserPostLikes[$l]['content_id']] = 1;
                }
            }
            //echo "<pre>";print_r($userPostLikeArr);die;
            $getUserPostComments = UserComments::with('users')->whereIn('post_id', $userPostIdArr)->orderBy('created_at', 'desc')->get()->toArray();
            $getUserPostImage = UserPostImageVideo::whereIn('post_id', $userPostIdArr)->orderBy('created_at', 'desc')->get()->toArray();
            for ($v = 0; $v < count($getUserPostImage); $v++) {
                $userPostFilesArr[$getUserPostImage[$v]['post_id']][] = $getUserPostImage[$v];
            }
            //echo "<pre>";print_r($userPostFilesArr);die;
            for ($c = 0; $c < count($getUserPostComments); $c++) {
                if ($getUserPostComments[$c]['parent_comment_id'] > 0) {
                    $childCommentArr[$getUserPostComments[$c]['post_id']][$getUserPostComments[$c]['parent_comment_id']][] = $getUserPostComments[$c];
                } else {
                    $userPostCommentsArr[$getUserPostComments[$c]['post_id']][] = $getUserPostComments[$c];
                }
            }
            for ($pa = 0; $pa < count($getUserPosts); $pa++) {
                $likeCount = $commentCount = 0;
                $getUserPosts[$pa]['user_name'] = $user->name;
                $getUserPosts[$pa]['mobile'] = $user->mobile;
                $getUserPosts[$pa]['email'] = $user->email;
                $getUserPosts[$pa]['photo'] = $user->photo;
                $getUserPosts[$pa]['gender'] = $user->gender;
                $getUserPosts[$pa]['background'] = $user->background;
                $getUserPosts[$pa]['country'] = $user->country;
                $getUserPosts[$pa]['bio'] = $user->bio;
                $getUserPosts[$pa]['dob'] = $user->dob;

                /// save by me
                $postSave = PostSave::where('post_id', $getUserPosts[$pa]['id'])->where(
                    'user_id',
                    $user->id
                )->count();
                $getUserPosts[$pa]['save_by_me'] = 0;
                if ($postSave > 0) {
                    $getUserPosts[$pa]['save_by_me'] = 1;
                }

                /// like by me
                $saveLikes = UserLikes::where('content_id', $getUserPosts[$pa]['id'])
                    ->where('type', 1)
                    ->where('user_id', $user->id)
                    ->count();
                $getUserPosts[$pa]['like_by_me'] = 0;
                if ($saveLikes > 0) {
                    $getUserPosts[$pa]['like_by_me'] = 1;
                }

                // is_user_follow
                $userFollows = UserFollows::where('user_id', $user->id)
                    ->where('user_follow_id', $getUserPosts[$pa]['user_id'])
                    ->count();
                $getUserPosts[$pa]['is_user_follow'] = 0;
                if ($userFollows > 0) {
                    $getUserPosts[$pa]['is_user_follow'] = 1;
                }

                // follow by me
                $userFollows1 = UserFollows::where('user_follow_id', $user->id)
                    ->where('user_id', $getUserPosts[$pa]['user_id'])
                    ->count();
                $getUserPosts[$pa]['follow_by_me'] = 0;
                if ($userFollows1 > 0) {
                    $getUserPosts[$pa]['follow_by_me'] = 1;
                }



                $getUserSport = UserSport::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')->get('sport_id')->toArray();
                if (count($getUserSport) > 0) {
                    $sportInfo = Sport::whereIn('id', $getUserSport)->get()->toArray();
                    for ($i = 0; $i < count($sportInfo); $i++) {
                        $getTeam = UserTeam::where('user_id', $getUserPosts[$pa]['user_id'])->where('sport_id', $sportInfo[$i]['id'])->orderBy('created_at', 'desc')->get('team_id')->toArray();
                        if (count($getTeam) > 0) {
                            $teamDetails = Team::whereIn('id', $getTeam)->where('status', '0')->get()->toArray();
                            if (!empty($teamDetails)) {
                                foreach ($teamDetails as $tKey => $teamDetail) {
                                    $teamDetails[$tKey]['strTeamLogo'] = asset($teamDetail['strTeamLogo']);
                                }
                            }
                            $sportInfo[$i]['team'] = $teamDetails;
                        } else {
                            $sportInfo[$i]['team'] = array();
                        }
                    }
                    $getUserPosts[$pa]['sport_info'] = $sportInfo;
                } else {
                    $getUserPosts[$pa]['sport_info'] = array();
                }


                if (isset($userPostLikeArr[$getUserPosts[$pa]['id']])) {
                    $likeCount = $userPostLikeArr[$getUserPosts[$pa]['id']];
                }
                //echo "<pre>";print_r($childCommentArr);die;
                $getUserPosts[$pa]['likes'] = $likeCount;
                if (isset($userPostCommentsArr[$getUserPosts[$pa]['id']])) {
                    $userMainCommentArr = $userPostCommentsArr[$getUserPosts[$pa]['id']];
                    $commentCount += count($userMainCommentArr);
                    $getUserPosts[$pa]['comments'] = $userMainCommentArr;
                    for ($f = 0; $f < count($userMainCommentArr); $f++) {
                        if (isset($childCommentArr[$getUserPosts[$pa]['id']][$userMainCommentArr[$f]['id']])) {
                            $childCommentArr = $childCommentArr[$getUserPosts[$pa]['id']][$userMainCommentArr[$f]['id']];
                            $getUserPosts[$pa]['comments'][$f]['comments'] = $childCommentArr;
                            $commentCount += count($childCommentArr);
                        }
                    }
                } else {
                    $getUserPosts[$pa]['comments'] = array();
                }
                $getUserPosts[$pa]['comments_count'] = $commentCount;
                if (isset($userPostFilesArr[$getUserPosts[$pa]['id']])) {
                    $getUserPosts[$pa]['videos'] = array();
                    $getUserPosts[$pa]['images'] = array();
                    $userPostFiles = $userPostFilesArr[$getUserPosts[$pa]['id']];
                    for ($b = 0; $b < count($userPostFiles); $b++) {
                        if ($userPostFiles[$b]['file_path'] != '') {
                            $userPostFiles[$b]['file_path'] = asset($userPostFiles[$b]['file_path']);
                        }
                        $userPostFiles[$b]['share_url'] = asset($userPostFiles[$b]['share_url']);
                        if ($userPostFiles[$b]['type'] == "0") {
                            $getUserPosts[$pa]['images'][] = $userPostFiles[$b];
                        } elseif ($userPostFiles[$b]['type'] == "1") {
                            $getUserPosts[$pa]['videos'][] = $userPostFiles[$b];
                        }
                    }
                    //echo "<pre>";print_r($getUserPosts);die;
                } else {
                    $getUserPosts[$pa]['videos'] = array();
                    $getUserPosts[$pa]['images'] = array();
                }
                $getUserPosts[$pa]['common_count'] = $likeCount;
                if ($commentCount > $likeCount) {
                    $getUserPosts[$pa]['common_count'] = $commentCount;
                }
                $finalArr[] = $getUserPosts[$pa];
            }
            //echo "<pre>";print_r($finalArr);die;
            $keys = array_column($finalArr, 'common_count');
            array_multisort($keys, SORT_DESC, $finalArr);
            return response()->json(['success' => $finalArr, 'total' => $total_rows, 'size' => $total_page, 'total_page' => $page_size, 'page' => $currentpage, 'more' => $more_page], $this->successStatus);
        } else {
            return response()->json(['error' => 'Post data not found'], $this->successStatus);
        }
        return response()->json(['error' => 'Unauthorised'], 401);
    }

    public function getSavePost(Request $request)
    {
        $limit = 10;
        $page = 1;
        $user = Auth::user();
        $getUserPosts1 = PostSave::where('user_id', $user->id)
            ->get('post_id')
            ->toArray();
        //dd($getUserPosts1);
        $userBlockIds = UserBlock::where('block_user_id', $user->id)
            ->pluck('user_id')->toArray();
        //dd($userBlockIds);
        $getPostQuery = UserPosts::whereIn('id', $getUserPosts1)
            ->whereNotIn('user_id', $userBlockIds)
            ->where('is_deleted', '0')->orderBy('updated_at', 'desc');
        $total_rows = $getPostQuery->count();
        $total_page = isset($input['limit']) ? $input['limit'] : $limit;
        $page_size = ceil($total_rows / $total_page);
        $currentpage = isset($input['page_no']) ? $input['page_no'] : $page;
        $offset = $currentpage * $total_page - $total_page;
        $getPostQuery->take($total_page)->offset($offset);
        $getUserPosts = $getPostQuery->get()->toArray();
        //dd($getUserPosts);
        //dd(\DB::getQueryLog());
        $more_page = false;
        if ($currentpage < $page_size) {
            $more_page = true;
        }
        if (count($getUserPosts) > 0) {
            $userPostIdArr = $userPostCommentsArr = $finalArr = $userPostFilesArr = $parentCommentIdArr = $childCommentArr = array();
            for ($p = 0; $p < count($getUserPosts); $p++) {
                $userPostIdArr[] = $getUserPosts[$p]['id'];
            }

            $getUserPostLikes = UserLikes::whereIn('content_id', $userPostIdArr)->where('type', 1)->get()->toArray();
            for ($l = 0; $l < count($getUserPostLikes); $l++) {
                if (isset($userPostLikeArr[$getUserPostLikes[$l]['content_id']])) {
                    $userPostLikeArr[$getUserPostLikes[$l]['content_id']] += 1;
                } else {
                    $userPostLikeArr[$getUserPostLikes[$l]['content_id']] = 1;
                }
            }
            //echo "<pre>";print_r($userPostLikeArr);die;
            $getUserPostComments = UserComments::with('users')->whereIn('post_id', $userPostIdArr)->orderBy('created_at', 'desc')->get()->toArray();
            $getUserPostImage = UserPostImageVideo::whereIn('post_id', $userPostIdArr)->orderBy('created_at', 'desc')->get()->toArray();
            for ($v = 0; $v < count($getUserPostImage); $v++) {
                $userPostFilesArr[$getUserPostImage[$v]['post_id']][] = $getUserPostImage[$v];
            }
            //echo "<pre>";print_r($userPostFilesArr);die;
            for ($c = 0; $c < count($getUserPostComments); $c++) {
                if ($getUserPostComments[$c]['parent_comment_id'] > 0) {
                    $childCommentArr[$getUserPostComments[$c]['post_id']][$getUserPostComments[$c]['parent_comment_id']][] = $getUserPostComments[$c];
                } else {
                    $userPostCommentsArr[$getUserPostComments[$c]['post_id']][] = $getUserPostComments[$c];
                }
            }
            for ($pa = 0; $pa < count($getUserPosts); $pa++) {
                $likeCount = $commentCount = 0;
                $findPostUser = User::where('id', $getUserPosts[$pa]['user_id'])->first();
                $getUserPosts[$pa]['user_name'] = $findPostUser->name ??  '';
                $getUserPosts[$pa]['mobile'] = $findPostUser->mobile ?? '';
                $getUserPosts[$pa]['email'] = $findPostUser->email ?? '';
                $getUserPosts[$pa]['photo'] = $findPostUser->photo ?? '';
                $getUserPosts[$pa]['gender'] = $findPostUser->gender ?? '';
                $getUserPosts[$pa]['background'] = $findPostUser->background ?? '';
                $getUserPosts[$pa]['country'] = $findPostUser->country ?? '';
                $getUserPosts[$pa]['bio'] = $findPostUser->bio ?? '';
                $getUserPosts[$pa]['dob'] = $findPostUser->dob ?? '';

                /// save by me
                $postSave = PostSave::where('post_id', $getUserPosts[$pa]['id'])->where(
                    'user_id',
                    $user->id
                )->count();
                $getUserPosts[$pa]['save_by_me'] = 0;
                if ($postSave > 0) {
                    $getUserPosts[$pa]['save_by_me'] = 1;
                }

                /// like by me
                $saveLikes = UserLikes::where('content_id', $getUserPosts[$pa]['id'])
                    ->where('type', 1)
                    ->where('user_id', $user->id)
                    ->count();
                $getUserPosts[$pa]['like_by_me'] = 0;
                if ($saveLikes > 0) {
                    $getUserPosts[$pa]['like_by_me'] = 1;
                }

                // is_user_follow
                $userFollows = UserFollows::where('user_id', $user->id)
                    ->where('user_follow_id', $getUserPosts[$pa]['user_id'])
                    ->count();
                $getUserPosts[$pa]['is_user_follow'] = 0;
                if ($userFollows > 0) {
                    $getUserPosts[$pa]['is_user_follow'] = 1;
                }

                // follow by me
                $userFollows1 = UserFollows::where('user_follow_id', $user->id)
                    ->where('user_id', $getUserPosts[$pa]['user_id'])
                    ->count();
                $getUserPosts[$pa]['follow_by_me'] = 0;
                if ($userFollows1 > 0) {
                    $getUserPosts[$pa]['follow_by_me'] = 1;
                }


                $getUserSport = UserSport::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')->get('sport_id')->toArray();
                if (count($getUserSport) > 0) {
                    $sportInfo = Sport::whereIn('id', $getUserSport)->get()->toArray();
                    for ($i = 0; $i < count($sportInfo); $i++) {
                        $getTeam = UserTeam::where('user_id', $getUserPosts[$pa]['user_id'])->where('sport_id', $sportInfo[$i]['id'])->orderBy('created_at', 'desc')->get('team_id')->toArray();
                        if (count($getTeam) > 0) {
                            $teamDetails = Team::whereIn('id', $getTeam)->where('status', '0')->get()->toArray();
                            if (!empty($teamDetails)) {
                                foreach ($teamDetails as $tKey => $teamDetail) {
                                    $teamDetails[$tKey]['strTeamLogo'] = asset($teamDetail['strTeamLogo']);
                                }
                            }
                            $sportInfo[$i]['team'] = $teamDetails;
                        } else {
                            $sportInfo[$i]['team'] = array();
                        }
                    }
                    $getUserPosts[$pa]['sport_info'] = $sportInfo;
                } else {
                    $getUserPosts[$pa]['sport_info'] = array();
                }


                if (isset($userPostLikeArr[$getUserPosts[$pa]['id']])) {
                    $likeCount = $userPostLikeArr[$getUserPosts[$pa]['id']];
                }
                //echo "<pre>";print_r($childCommentArr);die;
                $getUserPosts[$pa]['likes'] = $likeCount;
                if (isset($userPostCommentsArr[$getUserPosts[$pa]['id']])) {
                    $userMainCommentArr = $userPostCommentsArr[$getUserPosts[$pa]['id']];
                    $commentCount += count($userMainCommentArr);
                    $getUserPosts[$pa]['comments'] = $userMainCommentArr;
                    for ($f = 0; $f < count($userMainCommentArr); $f++) {
                        if (isset($childCommentArr[$getUserPosts[$pa]['id']][$userMainCommentArr[$f]['id']])) {
                            $childCommentArr = $childCommentArr[$getUserPosts[$pa]['id']][$userMainCommentArr[$f]['id']];
                            $getUserPosts[$pa]['comments'][$f]['comments'] = $childCommentArr;
                            $commentCount += count($childCommentArr);
                        }
                    }
                } else {
                    $getUserPosts[$pa]['comments'] = array();
                }
                $getUserPosts[$pa]['comments_count'] = $commentCount;
                if (isset($userPostFilesArr[$getUserPosts[$pa]['id']])) {
                    $getUserPosts[$pa]['videos'] = array();
                    $getUserPosts[$pa]['images'] = array();
                    $userPostFiles = $userPostFilesArr[$getUserPosts[$pa]['id']];
                    for ($b = 0; $b < count($userPostFiles); $b++) {
                        if ($userPostFiles[$b]['file_path'] != '') {
                            $userPostFiles[$b]['file_path'] = asset($userPostFiles[$b]['file_path']);
                        }
                        $userPostFiles[$b]['share_url'] = asset($userPostFiles[$b]['share_url']);
                        if ($userPostFiles[$b]['type'] == "0") {
                            $getUserPosts[$pa]['images'][] = $userPostFiles[$b];
                        } elseif ($userPostFiles[$b]['type'] == "1") {
                            $getUserPosts[$pa]['videos'][] = $userPostFiles[$b];
                        }
                    }
                    //echo "<pre>";print_r($getUserPosts);die;
                } else {
                    $getUserPosts[$pa]['videos'] = array();
                    $getUserPosts[$pa]['images'] = array();
                }
                $getUserPosts[$pa]['common_count'] = $likeCount;
                if ($commentCount > $likeCount) {
                    $getUserPosts[$pa]['common_count'] = $commentCount;
                }
                $finalArr[] = $getUserPosts[$pa];
            }
            //echo "<pre>";print_r($finalArr);die;
            $keys = array_column($finalArr, 'common_count');
            array_multisort($keys, SORT_DESC, $finalArr);
            return response()->json(['success' => $finalArr, 'total' => $total_rows, 'size' => $total_page, 'total_page' => $page_size, 'page' => $currentpage, 'more' => $more_page], $this->successStatus);
        } else {
            return response()->json(['error' => 'Post data not found'], $this->successStatus);
        }
        return response()->json(['error' => 'Unauthorised'], 401);
    }

    /*public function getLikedPost() {
        $user = Auth::user();
        $postIds = UserLikes::where('user_id', $user->id)
                ->where('type', 1)
                ->get(['content_id']);
        $getUserPosts = UserPosts::whereIn('id', $postIds)
                        ->where('type', 1)
                        ->where('is_deleted', '0')
                        ->orderBy('updated_at', 'desc')
                        ->get()->toArray();
        if (count($getUserPostImage) == 0) {
            return response()->json(['error' => 'Record not founds'], $this->successStatus);
        }
        $userPostIdArr = $finalArr = $userPostFilesArr = $userNameArr = $userPhotoArr = array();
        for ($p = 0; $p < count($getUserPosts); $p++) {
            $userPostIdArr[] = $getUserPosts[$p]['id'];
        }
        $getUserPostImage = UserPostImageVideo::whereIn('post_id', $userPostIdArr)->orderBy('created_at', 'desc')->get()->toArray();
        for ($v = 0; $v < count($getUserPostImage); $v++) {
            $userPostFilesArr[$getUserPostImage[$v]['post_id']][] = $getUserPostImage[$v];
        }
        for ($pa = 0; $pa < count($getUserPosts); $pa++) {
            if (isset($userPostFilesArr[$getUserPosts[$pa]['id']])) {
                $getUserPosts[$pa]['videos'] = array();
                $getUserPosts[$pa]['images'] = array();
                $userPostFiles = $userPostFilesArr[$getUserPosts[$pa]['id']];
                for ($b = 0; $b < count($userPostFiles); $b++) {
                    if ($userPostFiles[$b]['type'] == "0") {
                        $getUserPosts[$pa]['images'][] = $userPostFiles[$b];
                    } elseif ($userPostFiles[$b]['type'] == "1") {
                        $getUserPosts[$pa]['videos'][] = $userPostFiles[$b];
                    }
                }
            } else {
                $getUserPosts[$pa]['videos'] = array();
                $getUserPosts[$pa]['images'] = array();
            }
            $finalArr[] = $getUserPosts[$pa];
        }
        return response()->json(['success' => $finalArr], $this->successStatus);
    }*/
    public function getLikedPost()
    {
        $limit = 10;
        $page = 1;
        $user = Auth::user();
        $getUserPosts1 = UserLikes::where('type', 1)
            ->where('user_id', $user->id)
            ->get('content_id')
            ->toArray();
        $getPostQuery = UserPosts::whereIn('id', $getUserPosts1)
            ->where('is_deleted', '0')->orderBy('updated_at', 'desc');
        $total_rows = $getPostQuery->count();
        $total_page = isset($input['limit']) ? $input['limit'] : $limit;
        $page_size = ceil($total_rows / $total_page);
        $currentpage = isset($input['page_no']) ? $input['page_no'] : $page;
        $offset = $currentpage * $total_page - $total_page;
        $getPostQuery->take($total_page)->offset($offset);
        $getUserPosts = $getPostQuery->get()->toArray();
        //dd(\DB::getQueryLog());
        $more_page = false;
        if ($currentpage < $page_size) {
            $more_page = true;
        }
        if (count($getUserPosts) > 0) {
            $userPostIdArr = $userPostCommentsArr = $finalArr = $userPostFilesArr = $parentCommentIdArr = $childCommentArr = array();
            for ($p = 0; $p < count($getUserPosts); $p++) {
                $userPostIdArr[] = $getUserPosts[$p]['id'];
            }

            $getUserPostLikes = UserLikes::whereIn('content_id', $userPostIdArr)->where('type', 1)->get()->toArray();
            for ($l = 0; $l < count($getUserPostLikes); $l++) {
                if (isset($userPostLikeArr[$getUserPostLikes[$l]['content_id']])) {
                    $userPostLikeArr[$getUserPostLikes[$l]['content_id']] += 1;
                } else {
                    $userPostLikeArr[$getUserPostLikes[$l]['content_id']] = 1;
                }
            }
            //echo "<pre>";print_r($userPostLikeArr);die;
            $getUserPostComments = UserComments::with('users')->whereIn('post_id', $userPostIdArr)->orderBy('created_at', 'desc')->get()->toArray();
            $getUserPostImage = UserPostImageVideo::whereIn('post_id', $userPostIdArr)->orderBy('created_at', 'desc')->get()->toArray();
            for ($v = 0; $v < count($getUserPostImage); $v++) {
                $userPostFilesArr[$getUserPostImage[$v]['post_id']][] = $getUserPostImage[$v];
            }
            //echo "<pre>";print_r($userPostFilesArr);die;
            for ($c = 0; $c < count($getUserPostComments); $c++) {
                if ($getUserPostComments[$c]['parent_comment_id'] > 0) {
                    $childCommentArr[$getUserPostComments[$c]['post_id']][$getUserPostComments[$c]['parent_comment_id']][] = $getUserPostComments[$c];
                } else {
                    $userPostCommentsArr[$getUserPostComments[$c]['post_id']][] = $getUserPostComments[$c];
                }
            }
            for ($pa = 0; $pa < count($getUserPosts); $pa++) {
                $likeCount = $commentCount = 0;
                $getUserPosts[$pa]['user_name'] = $user->name;
                $getUserPosts[$pa]['mobile'] = $user->mobile;
                $getUserPosts[$pa]['email'] = $user->email;
                $getUserPosts[$pa]['photo'] = $user->photo;
                $getUserPosts[$pa]['gender'] = $user->gender;
                $getUserPosts[$pa]['background'] = $user->background;
                $getUserPosts[$pa]['country'] = $user->country;
                $getUserPosts[$pa]['bio'] = $user->bio;
                $getUserPosts[$pa]['dob'] = $user->dob;

                /// save by me
                $postSave = PostSave::where('post_id', $getUserPosts[$pa]['id'])->where(
                    'user_id',
                    $user->id
                )->count();
                $getUserPosts[$pa]['save_by_me'] = 0;
                if ($postSave > 0) {
                    $getUserPosts[$pa]['save_by_me'] = 1;
                }

                /// like by me
                $saveLikes = UserLikes::where('content_id', $getUserPosts[$pa]['id'])
                    ->where('type', 1)
                    ->where('user_id', $user->id)
                    ->count();
                $getUserPosts[$pa]['like_by_me'] = 0;
                if ($saveLikes > 0) {
                    $getUserPosts[$pa]['like_by_me'] = 1;
                }

                // is_user_follow
                $userFollows = UserFollows::where('user_id', $user->id)
                    ->where('user_follow_id', $getUserPosts[$pa]['user_id'])
                    ->count();
                $getUserPosts[$pa]['is_user_follow'] = 0;
                if ($userFollows > 0) {
                    $getUserPosts[$pa]['is_user_follow'] = 1;
                }

                // follow by me
                $userFollows1 = UserFollows::where('user_follow_id', $user->id)
                    ->where('user_id', $getUserPosts[$pa]['user_id'])
                    ->count();
                $getUserPosts[$pa]['follow_by_me'] = 0;
                if ($userFollows1 > 0) {
                    $getUserPosts[$pa]['follow_by_me'] = 1;
                }



                $getUserSport = UserSport::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')->get('sport_id')->toArray();
                if (count($getUserSport) > 0) {
                    $sportInfo = Sport::whereIn('id', $getUserSport)->get()->toArray();
                    for ($i = 0; $i < count($sportInfo); $i++) {
                        $getTeam = UserTeam::where('user_id', $getUserPosts[$pa]['user_id'])->where('sport_id', $sportInfo[$i]['id'])->orderBy('created_at', 'desc')->get('team_id')->toArray();
                        if (count($getTeam) > 0) {
                            $teamDetails = Team::whereIn('id', $getTeam)->where('status', '0')->get()->toArray();
                            if (!empty($teamDetails)) {
                                foreach ($teamDetails as $tKey => $teamDetail) {
                                    $teamDetails[$tKey]['strTeamLogo'] = asset($teamDetail['strTeamLogo']);
                                }
                            }
                            $sportInfo[$i]['team'] = $teamDetails;
                        } else {
                            $sportInfo[$i]['team'] = array();
                        }
                    }
                    $getUserPosts[$pa]['sport_info'] = $sportInfo;
                } else {
                    $getUserPosts[$pa]['sport_info'] = array();
                }


                if (isset($userPostLikeArr[$getUserPosts[$pa]['id']])) {
                    $likeCount = $userPostLikeArr[$getUserPosts[$pa]['id']];
                }
                //echo "<pre>";print_r($childCommentArr);die;
                $getUserPosts[$pa]['likes'] = $likeCount;
                if (isset($userPostCommentsArr[$getUserPosts[$pa]['id']])) {
                    $userMainCommentArr = $userPostCommentsArr[$getUserPosts[$pa]['id']];
                    $commentCount += count($userMainCommentArr);
                    $getUserPosts[$pa]['comments'] = $userMainCommentArr;
                    for ($f = 0; $f < count($userMainCommentArr); $f++) {
                        if (isset($childCommentArr[$getUserPosts[$pa]['id']][$userMainCommentArr[$f]['id']])) {
                            $childCommentArr = $childCommentArr[$getUserPosts[$pa]['id']][$userMainCommentArr[$f]['id']];
                            $getUserPosts[$pa]['comments'][$f]['comments'] = $childCommentArr;
                            $commentCount += count($childCommentArr);
                        }
                    }
                } else {
                    $getUserPosts[$pa]['comments'] = array();
                }
                $getUserPosts[$pa]['comments_count'] = $commentCount;
                if (isset($userPostFilesArr[$getUserPosts[$pa]['id']])) {
                    $getUserPosts[$pa]['videos'] = array();
                    $getUserPosts[$pa]['images'] = array();
                    $userPostFiles = $userPostFilesArr[$getUserPosts[$pa]['id']];
                    for ($b = 0; $b < count($userPostFiles); $b++) {
                        if ($userPostFiles[$b]['file_path'] != '') {
                            $userPostFiles[$b]['file_path'] = asset($userPostFiles[$b]['file_path']);
                        }
                        $userPostFiles[$b]['share_url'] = asset($userPostFiles[$b]['share_url']);
                        if ($userPostFiles[$b]['type'] == "0") {
                            $getUserPosts[$pa]['images'][] = $userPostFiles[$b];
                        } elseif ($userPostFiles[$b]['type'] == "1") {
                            $getUserPosts[$pa]['videos'][] = $userPostFiles[$b];
                        }
                    }
                    //echo "<pre>";print_r($getUserPosts);die;
                } else {
                    $getUserPosts[$pa]['videos'] = array();
                    $getUserPosts[$pa]['images'] = array();
                }
                $getUserPosts[$pa]['common_count'] = $likeCount;
                if ($commentCount > $likeCount) {
                    $getUserPosts[$pa]['common_count'] = $commentCount;
                }
                $finalArr[] = $getUserPosts[$pa];
            }
            //echo "<pre>";print_r($finalArr);die;
            $keys = array_column($finalArr, 'common_count');
            array_multisort($keys, SORT_DESC, $finalArr);
            return response()->json(['success' => $finalArr, 'total' => $total_rows, 'size' => $total_page, 'total_page' => $page_size, 'page' => $currentpage, 'more' => $more_page], $this->successStatus);
        } else {
            return response()->json(['error' => 'Post data not found'], $this->successStatus);
        }
        return response()->json(['error' => 'Unauthorised'], 401);
    }

    public function getPostDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
            'type' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $user = Auth::user();
        $input = $request->all();
        if (isset($request->type) && ($request->type == 1 || $request->type == 2 || $request->type == 3)) {
            $userFollow = UserFollows::where('user_id', $user->id)->get()->toArray();
            $userIdArr = array();
            $userIdArr[] = $user->id;
            for ($f = 0; $f < count($userFollow); $f++) {
                $userIdArr[] = $userFollow[$f]['user_follow_id'];
            }
            //echo "<pre>";print_r($userIdArr);die;
            $getUserPosts = UserPosts::where('id', $request->post_id)->where('type', $request->type)->where('is_deleted', '0')->orderBy('updated_at', 'desc')->get()->toArray();
            if (count($getUserPosts) > 0) {
                $userPostIdArr = $userPostCommentsArr = $finalArr = $userPostFilesArr = $userNameArr = $userPhotoArr = $parentCommentIdArr = $childCommentArr = $userLikeArr = array();
                for ($p = 0; $p < count($getUserPosts); $p++) {
                    $userPostIdArr[] = $getUserPosts[$p]['id'];
                }
                $getUserName = User::whereIn('id', $userIdArr)->get(['id', 'name', 'photo'])->toArray();
                for ($g = 0; $g < count($getUserName); $g++) {
                    $userNameArr[$getUserName[$g]['id']] = $getUserName[$g]['name'];
                    $userPhotoArr[$getUserName[$g]['id']] = $getUserName[$g]['photo'];
                }
                //echo "<pre>";print_r($userNameArr);die;
                $getUserPostLikes = UserLikes::whereIn('content_id', $userPostIdArr)->where('type', 1)->get()->toArray();
                for ($l = 0; $l < count($getUserPostLikes); $l++) {
                    if (isset($userPostLikeArr[$getUserPostLikes[$l]['content_id']])) {
                        $userPostLikeArr[$getUserPostLikes[$l]['content_id']] += 1;
                    } else {
                        $userPostLikeArr[$getUserPostLikes[$l]['content_id']] = 1;
                    }
                    $userLikeArr[$getUserPostLikes[$l]['content_id']][] = $getUserPostLikes[$l];
                }
                //echo "<pre>";print_r($userPostLikeArr);die;
                $getUserPostComments = UserComments::with('users')->whereIn('post_id', $userPostIdArr)->orderBy('created_at', 'desc')->get()->toArray();
                $getUserPostImage = UserPostImageVideo::whereIn('post_id', $userPostIdArr)->orderBy('created_at', 'desc')->get()->toArray();
                for ($v = 0; $v < count($getUserPostImage); $v++) {
                    $userPostFilesArr[$getUserPostImage[$v]['post_id']][] = $getUserPostImage[$v];
                }
                //echo "<pre>";print_r($userPostFilesArr);die;
                for ($c = 0; $c < count($getUserPostComments); $c++) {
                    if ($getUserPostComments[$c]['parent_comment_id'] > 0) {
                        $childCommentArr[$getUserPostComments[$c]['post_id']][$getUserPostComments[$c]['parent_comment_id']][] = $getUserPostComments[$c];
                    } else {
                        $userPostCommentsArr[$getUserPostComments[$c]['post_id']][] = $getUserPostComments[$c];
                    }
                }
                for ($pa = 0; $pa < count($getUserPosts); $pa++) {
                    $userName = "";
                    $likeCount = 0;
                    $photo = "";
                    if (isset($userNameArr[$getUserPosts[$pa]['user_id']])) {
                        $userName = $userNameArr[$getUserPosts[$pa]['user_id']];
                    }
                    $getUserPosts[$pa]['user_name'] = $userName;

                    if (isset($userPhotoArr[$getUserPosts[$pa]['user_id']])) {
                        $photo = $userPhotoArr[$getUserPosts[$pa]['user_id']];
                    }
                    $getUserPosts[$pa]['photo'] = $photo;
                    if (isset($userPostLikeArr[$getUserPosts[$pa]['id']])) {
                        $likeCount = $userPostLikeArr[$getUserPosts[$pa]['id']];
                    }
                    //echo "<pre>";print_r($childCommentArr);die;
                    $getUserPosts[$pa]['likes'] = $likeCount;
                    $userPostLikeArr = array();
                    if (isset($userLikeArr[$getUserPosts[$pa]['id']])) {
                        $userPostLikeArr = $userLikeArr[$getUserPosts[$pa]['id']];
                    }
                    $getUserPosts[$pa]['user_likes'] = $userPostLikeArr;
                    if (isset($userPostCommentsArr[$getUserPosts[$pa]['id']])) {
                        $userMainCommentArr = $userPostCommentsArr[$getUserPosts[$pa]['id']];
                        $getUserPosts[$pa]['comments'] = $userMainCommentArr;
                        for ($f = 0; $f < count($userMainCommentArr); $f++) {
                            if (isset($childCommentArr[$getUserPosts[$pa]['id']][$userMainCommentArr[$f]['id']])) {
                                $childCommentArr1 = $childCommentArr[$getUserPosts[$pa]['id']][$userMainCommentArr[$f]['id']];
                                //echo "<pre>";print_r($childCommentArr);die;
                                $getUserPosts[$pa]['comments'][$f]['comments'] = $childCommentArr1;
                                for ($xa = 0; $xa < count($childCommentArr1); $xa++) {
                                    if (isset($childCommentArr[$getUserPosts[$pa]['id']][$childCommentArr1[$xa]['id']])) {
                                        $childCommentArr2 = $childCommentArr[$getUserPosts[$pa]['id']][$childCommentArr1[$xa]['id']];
                                        $getUserPosts[$pa]['comments'][$f]['comments'][$xa]['comments'] = $childCommentArr2;
                                        for ($ya = 0; $ya < count($childCommentArr2); $ya++) {
                                            if (isset($childCommentArr[$getUserPosts[$pa]['id']][$childCommentArr2[$ya]['id']])) {
                                                $childCommentArr3 = $childCommentArr[$getUserPosts[$pa]['id']][$childCommentArr2[$ya]['id']];
                                                $getUserPosts[$pa]['comments'][$f]['comments'][$xa]['comments'][$ya]['comments'] = $childCommentArr3;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        //echo "<pre>";print_r($getUserPosts);die;
                    } else {
                        $getUserPosts[$pa]['comments'] = array();
                    }
                    if (isset($userPostFilesArr[$getUserPosts[$pa]['id']])) {
                        $getUserPosts[$pa]['videos'] = array();
                        $getUserPosts[$pa]['images'] = array();
                        $userPostFiles = $userPostFilesArr[$getUserPosts[$pa]['id']];
                        for ($b = 0; $b < count($userPostFiles); $b++) {
                            if ($userPostFiles[$b]['file_path'] != '') {
                                $userPostFiles[$b]['file_path'] = asset($userPostFiles[$b]['file_path']);
                            }
                            $userPostFiles[$b]['share_url'] = asset($userPostFiles[$b]['share_url']);
                            if ($userPostFiles[$b]['type'] == "0") {
                                $getUserPosts[$pa]['images'][] = $userPostFiles[$b];
                            } elseif ($userPostFiles[$b]['type'] == "1") {
                                $getUserPosts[$pa]['videos'][] = $userPostFiles[$b];
                            }
                        }
                        //echo "<pre>";print_r($getUserPosts);die;
                    } else {
                        $getUserPosts[$pa]['videos'] = array();
                        $getUserPosts[$pa]['images'] = array();
                    }
                    $finalArr = $getUserPosts[$pa];
                }
                //echo "<pre>";print_r($finalArr);die;
                return response()->json(['success' => $finalArr], $this->successStatus);
            } else {
                return response()->json(['error' => 'Post data not found'], $this->successStatus);
            }
        } else {
            return response()->json(['error' => 'Pass type value 1-Post, 2-Story, 3-Reels'], $this->successStatus);
        }
        return response()->json(['error' => 'Unauthorised'], 401);
    }

    public function trandingList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $user = Auth::user();
        $input = $request->all();
        //echo "<pre>";print_r($input);die;
        if (isset($request->type) && ($request->type == 1 || $request->type == 2 || $request->type == 3)) {
            //echo "<pre>";print_r($userIdArr);die;
            $blockedUsers = UserBlock::where('user_id', $user->id)
                ->pluck('block_user_id')
                ->toArray();
            $getUserPosts = UserPosts::where('type', $request->type)
                ->where('is_deleted', '0')
                ->whereNotIn('user_id', $blockedUsers)
                ->orderBy('updated_at', 'desc')
                ->get()->toArray();
            if (count($getUserPosts) > 0) {
                $userPostIdArr = $userPostCommentsArr = $finalArr = $userPostFilesArr = $userNameArr = $userPhotoArr = $userEmailArr = $userMobileArr = $userGenderArr = $userBackArr = $userDobArr = $userBioArr = $userCountryArr =  $parentCommentIdArr = $childCommentArr = array();
                for ($p = 0; $p < count($getUserPosts); $p++) {
                    $userPostIdArr[] = $getUserPosts[$p]['id'];
                    $userIdArr[] = $getUserPosts[$p]['user_id'];
                }
                $getUserName = User::whereIn('id', $userIdArr)->get(['id', 'email', 'name', 'mobile', 'gender', 'photo', 'background', 'dob', 'bio', 'country'])->toArray();
                for ($g = 0; $g < count($getUserName); $g++) {
                    $userNameArr[$getUserName[$g]['id']] = $getUserName[$g]['name'];
                    $userPhotoArr[$getUserName[$g]['id']] = $getUserName[$g]['photo'];
                    $userEmailArr[$getUserName[$g]['id']] = $getUserName[$g]['email'];
                    $userMobileArr[$getUserName[$g]['id']] = $getUserName[$g]['mobile'];
                    $userGenderArr[$getUserName[$g]['id']] = $getUserName[$g]['gender'];
                    $userBackArr[$getUserName[$g]['id']] = $getUserName[$g]['background'];
                    $userDobArr[$getUserName[$g]['id']] = $getUserName[$g]['dob'];
                    $userBioArr[$getUserName[$g]['id']] = $getUserName[$g]['bio'];
                    $userCountryArr[$getUserName[$g]['id']] = $getUserName[$g]['country'];
                }
                //echo "<pre>";print_r($userNameArr);die;
                $getUserPostLikes = UserLikes::whereIn('content_id', $userPostIdArr)->where('type', 1)->get()->toArray();
                for ($l = 0; $l < count($getUserPostLikes); $l++) {
                    if (isset($userPostLikeArr[$getUserPostLikes[$l]['content_id']])) {
                        $userPostLikeArr[$getUserPostLikes[$l]['content_id']] += 1;
                    } else {
                        $userPostLikeArr[$getUserPostLikes[$l]['content_id']] = 1;
                    }
                }
                //echo "<pre>";print_r($userPostLikeArr);die;
                $getUserPostComments = UserComments::with('users')->whereIn('post_id', $userPostIdArr)->orderBy('created_at', 'desc')->get()->toArray();
                $getUserPostImage = UserPostImageVideo::whereIn('post_id', $userPostIdArr)->orderBy('created_at', 'desc')->get()->toArray();
                for ($v = 0; $v < count($getUserPostImage); $v++) {
                    $userPostFilesArr[$getUserPostImage[$v]['post_id']][] = $getUserPostImage[$v];
                }
                //echo "<pre>";print_r($userPostFilesArr);die;
                for ($c = 0; $c < count($getUserPostComments); $c++) {
                    if ($getUserPostComments[$c]['parent_comment_id'] > 0) {
                        $childCommentArr[$getUserPostComments[$c]['post_id']][$getUserPostComments[$c]['parent_comment_id']][] = $getUserPostComments[$c];
                    } else {
                        $userPostCommentsArr[$getUserPostComments[$c]['post_id']][] = $getUserPostComments[$c];
                    }
                }
                for ($pa = 0; $pa < count($getUserPosts); $pa++) {
                    $userName = "";
                    $likeCount = $commentCount = 0;
                    $photo = $mobile = $email = $bio = $dob = $country = $back = $gender = "";
                    if (isset($userNameArr[$getUserPosts[$pa]['user_id']])) {
                        $userName = $userNameArr[$getUserPosts[$pa]['user_id']];
                    }
                    $getUserPosts[$pa]['user_name'] = $userName;

                    if (isset($userMobileArr[$getUserPosts[$pa]['user_id']])) {
                        $mobile = $userMobileArr[$getUserPosts[$pa]['user_id']];
                    }
                    $getUserPosts[$pa]['mobile'] = $mobile;

                    if (isset($userEmailArr[$getUserPosts[$pa]['user_id']])) {
                        $email = $userEmailArr[$getUserPosts[$pa]['user_id']];
                    }
                    $getUserPosts[$pa]['email'] = $email;

                    if (isset($userPhotoArr[$getUserPosts[$pa]['user_id']])) {
                        $photo = $userPhotoArr[$getUserPosts[$pa]['user_id']];
                    }
                    $getUserPosts[$pa]['photo'] = $photo;

                    if (isset($userGenderArr[$getUserPosts[$pa]['user_id']])) {
                        $gender = $userGenderArr[$getUserPosts[$pa]['user_id']];
                    }
                    $getUserPosts[$pa]['gender'] = $gender;

                    if (isset($userBackArr[$getUserPosts[$pa]['user_id']])) {
                        $back = $userBackArr[$getUserPosts[$pa]['user_id']];
                    }
                    $getUserPosts[$pa]['background'] = $back;

                    if (isset($userCountryArr[$getUserPosts[$pa]['user_id']])) {
                        $country = $userCountryArr[$getUserPosts[$pa]['user_id']];
                    }
                    $getUserPosts[$pa]['country'] = $country;

                    if (isset($userBioArr[$getUserPosts[$pa]['user_id']])) {
                        $bio = $userBioArr[$getUserPosts[$pa]['user_id']];
                    }
                    $getUserPosts[$pa]['bio'] = $bio;


                    if (isset($userDobArr[$getUserPosts[$pa]['user_id']])) {
                        $dob = $userDobArr[$getUserPosts[$pa]['user_id']];
                    }
                    $getUserPosts[$pa]['dob'] = $dob;

                    /// save by me
                    $postSave = PostSave::where('post_id', $getUserPosts[$pa]['id'])->where(
                        'user_id',
                        $user->id
                    )->count();
                    $getUserPosts[$pa]['save_by_me'] = 0;
                    if ($postSave > 0) {
                        $getUserPosts[$pa]['save_by_me'] = 1;
                    }

                    /// like by me
                    $saveLikes = UserLikes::where('content_id', $getUserPosts[$pa]['id'])
                        ->where('type', 1)
                        ->where('user_id', $user->id)
                        ->count();
                    $getUserPosts[$pa]['like_by_me'] = 0;
                    if ($saveLikes > 0) {
                        $getUserPosts[$pa]['like_by_me'] = 1;
                    }

                    // is_user_follow
                    $userFollows = UserFollows::where('user_id', $user->id)
                        ->where('user_follow_id', $getUserPosts[$pa]['user_id'])
                        ->count();
                    $getUserPosts[$pa]['is_user_follow'] = 0;
                    if ($userFollows > 0) {
                        $getUserPosts[$pa]['is_user_follow'] = 1;
                    }

                    // follow by me
                    $userFollows1 = UserFollows::where('user_follow_id', $user->id)
                        ->where('user_id', $getUserPosts[$pa]['user_id'])
                        ->count();
                    $getUserPosts[$pa]['follow_by_me'] = 0;
                    if ($userFollows1 > 0) {
                        $getUserPosts[$pa]['follow_by_me'] = 1;
                    }



                    $getUserSport = UserSport::where('user_id', $getUserPosts[$pa]['user_id'])
                        ->orderBy('created_at', 'desc')->get('sport_id')->toArray();
                    if (count($getUserSport) > 0) {
                        $sportInfo = Sport::whereIn('id', $getUserSport)->get()->toArray();
                        for ($i = 0; $i < count($sportInfo); $i++) {
                            $getTeam = UserTeam::where('user_id', $getUserPosts[$pa]['user_id'])->where('sport_id', $sportInfo[$i]['id'])->orderBy('created_at', 'desc')->get('team_id')->toArray();
                            if (count($getTeam) > 0) {
                                $teamDetails = Team::whereIn('id', $getTeam)->where('status', '0')->get()->toArray();
                                if (!empty($teamDetails)) {
                                    foreach ($teamDetails as $tKey => $teamDetail) {
                                        $teamDetails[$tKey]['strTeamLogo'] = asset($teamDetail['strTeamLogo']);
                                    }
                                }
                                $sportInfo[$i]['team'] = $teamDetails;
                            } else {
                                $sportInfo[$i]['team'] = array();
                            }
                        }
                        $getUserPosts[$pa]['sport_info'] = $sportInfo;
                    } else {
                        $getUserPosts[$pa]['sport_info'] = array();
                    }


                    if (isset($userPostLikeArr[$getUserPosts[$pa]['id']])) {
                        $likeCount = $userPostLikeArr[$getUserPosts[$pa]['id']];
                    }
                    //echo "<pre>";print_r($childCommentArr);die;
                    $getUserPosts[$pa]['likes'] = $likeCount;
                    if (isset($userPostCommentsArr[$getUserPosts[$pa]['id']])) {
                        $userMainCommentArr = $userPostCommentsArr[$getUserPosts[$pa]['id']];
                        $commentCount += count($userMainCommentArr);
                        $getUserPosts[$pa]['comments'] = $userMainCommentArr;
                        for ($f = 0; $f < count($userMainCommentArr); $f++) {
                            if (isset($childCommentArr[$getUserPosts[$pa]['id']][$userMainCommentArr[$f]['id']])) {
                                $childCommentArr = $childCommentArr[$getUserPosts[$pa]['id']][$userMainCommentArr[$f]['id']];
                                $getUserPosts[$pa]['comments'][$f]['comments'] = $childCommentArr;
                                $commentCount += count($childCommentArr);
                            }
                        }
                    } else {
                        $getUserPosts[$pa]['comments'] = array();
                    }
                    $getUserPosts[$pa]['comments_count'] = $commentCount;
                    if (isset($userPostFilesArr[$getUserPosts[$pa]['id']])) {
                        $getUserPosts[$pa]['videos'] = array();
                        $getUserPosts[$pa]['images'] = array();
                        $userPostFiles = $userPostFilesArr[$getUserPosts[$pa]['id']];
                        for ($b = 0; $b < count($userPostFiles); $b++) {
                            if ($userPostFiles[$b]['file_path'] != '') {
                                $userPostFiles[$b]['file_path'] = asset($userPostFiles[$b]['file_path']);
                            }
                            $userPostFiles[$b]['share_url'] = asset($userPostFiles[$b]['share_url']);
                            if ($userPostFiles[$b]['type'] == "0") {
                                $getUserPosts[$pa]['images'][] = $userPostFiles[$b];
                            } elseif ($userPostFiles[$b]['type'] == "1") {
                                $getUserPosts[$pa]['videos'][] = $userPostFiles[$b];
                            }
                        }
                        //echo "<pre>";print_r($getUserPosts);die;
                    } else {
                        $getUserPosts[$pa]['videos'] = array();
                        $getUserPosts[$pa]['images'] = array();
                    }
                    $getUserPosts[$pa]['common_count'] = $likeCount;
                    if ($commentCount > $likeCount) {
                        $getUserPosts[$pa]['common_count'] = $commentCount;
                    }
                    $finalArr[] = $getUserPosts[$pa];
                }
                //echo "<pre>";print_r($finalArr);die;
                $keys = array_column($finalArr, 'common_count');
                array_multisort($keys, SORT_DESC, $finalArr);
                return response()->json(['success' => $finalArr], $this->successStatus);
            } else {
                return response()->json(['error' => 'Post data not found'], $this->successStatus);
            }
        } else {
            return response()->json(['error' => 'Pass type value 1-Post, 2-Story, 3-Reels'], $this->successStatus);
        }
        return response()->json(['error' => 'Unauthorised'], 401);
    }

    function aasort(&$array, $key)
    {
        $sorter = array();
        $ret = array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii] = $va[$key];
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii] = $array[$ii];
        }
        $array = $ret;
    }
}
