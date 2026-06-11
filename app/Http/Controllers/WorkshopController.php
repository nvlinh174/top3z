<?php

namespace App\Http\Controllers;

use App\Enums\ArticleType;
use App\Enums\GeneralStatus;
use App\Models\Article;
use App\Support\GuestEngagement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class WorkshopController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'upcoming') === 'past' ? 'past' : 'upcoming';

        $query = Article::query()
            ->workshops()
            ->published()
            ->whereNotNull('starts_at')
            ->with(['category', 'author', 'media']);

        $workshops = $tab === 'past'
            ? $query->pastWorkshops()->paginate(12)->withQueryString()
            : $query->upcomingWorkshops()->paginate(12)->withQueryString();

        return view('workshops.index', [
            'workshops' => $workshops,
            'tab' => $tab,
        ]);
    }

    public function show(Article $article): View
    {
        abort_unless(
            $article->type === ArticleType::Announcement
            && $article->status === GeneralStatus::ACTIVE
            && ($article->published_at === null || $article->published_at <= now()),
            404
        );

        $article->loadCount('interests');
        $article->load([
            'category',
            'author',
            'media',
            'rootComments.visibleReplies.replyTo',
        ]);

        $sessionToken = GuestEngagement::sessionToken();

        return view('workshops.show', [
            'workshop' => $article,
            'hasInterest' => $article->hasViewerInterest(auth()->id(), $sessionToken),
        ]);
    }
}
