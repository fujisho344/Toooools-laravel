<?php

namespace App\Http\Controllers;

use App\Post;
use App\User;
use App\Profile;
use App\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->id ?? Auth::user()->id;
        $user = User::find($userId);
        if (empty($user)) {
            return redirect('/');
        }

        $posts = Post::postItem(Auth::user()->id ?? 0);

        if (!empty($request->isLikeShow)) {
            $posts->leftJoin('likes AS likes_w', function ($join) use ($userId) {
                    $join->on('likes_w.post_id', '=', 'posts.id')
                        ->where('likes_w.user_id', '=', $userId);
                })
                ->where('likes_w.user_id', '=', $userId);
        } else {
            $posts->where('posts.user_id', '=', $userId);
        }

        $posts->orderBy('posts.created_at', 'DESC');


        $posts = $posts->get();

        $param = [
            'user' => $user,
            'posts' => $posts,
        ];
        return view('profile.index', $param);
    }

    public function edit(Request $request)
    {
        $user = Auth::user();
        $profile = Profile::where('user_id', $user->id)->first();
        $form = [
            'email' => $user->email,
            'name' => $user->name,
            'bio' => !empty($profile->bio) ? $profile->bio : '',
            'like_tool' => !empty($profile->like_tool) ? $profile->like_tool : '',
            'img' => !empty($profile->img_filename) ? $profile->img_filename : '/img/avatar/default.png',
        ];

        return view('profile.edit', ['form' => $form]);
    }

    public function update(Request $request)
    {
        $user = User::find(Auth::user()->id);
        $this->validate($request,[
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id . ',id,deleted_at,NULL'],
            'bio' => ['max:500'],
            'like_tool' => ['max:30'],
            'img' => ['image','max:3000'],
        ]);

        if (isset($request->img)) {
            $filename = Storage::disk('s3')->putFile('img', $request->img, 'public');
            $path = Storage::disk('s3')->url($filename);
        }


        $user->email = $request->email;
        $user->name = $request->name;
        $user->save();

        $profiles = Profile::where('user_id', $user->id)->get();
        if ($profiles->isEmpty()) {

            $profile = new Profile;
            $profile->user_id = Auth::user()->id;
            $profile->bio = $request->bio ?? '';
            $profile->like_tool = $request->like_tool ?? '';
            $profile->img_filename = $path ?? '';
            $profile->save();
        } else {
            $profile = $profiles[0];
            $profile->bio = $request->bio ?? '';
            $profile->like_tool = $request->like_tool ?? '';
            $profile->img_filename = $path ?? $profile->img_filename;
            $profile->save();
        }

        return redirect('/profile')->with('flash_msg', 'プロフィールを変更しました');
    }
}
