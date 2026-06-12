<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;

class MemberProfileController extends Controller
{
    public function show(User $user): View
    {
        $user->loadCount('publicCommunityPosts');

        $posts = $user->publicCommunityPosts()
            ->with(['category', 'author.media', 'media'])
            ->paginate(12);

        return view('members.show', [
            'member' => $user,
            'posts' => $posts,
        ]);
    }
}
