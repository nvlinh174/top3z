<?php

namespace App\Http\Controllers;

use App\Enums\ArticleType;
use App\Enums\GeneralStatus;
use App\Http\Requests\StoreArticleInterestRequest;
use App\Models\Article;
use App\Models\ArticleInterest;
use App\Support\GuestEngagement;
use Illuminate\Http\RedirectResponse;

class WorkshopInterestController extends Controller
{
    public function store(StoreArticleInterestRequest $request, Article $article): RedirectResponse
    {
        abort_unless($this->isPublicWorkshop($article), 404);
        abort_unless($article->isUpcomingWorkshop(), 403);

        $sessionToken = GuestEngagement::sessionToken();

        if ($article->hasGuestInterest($sessionToken)) {
            return redirect()
                ->route('workshops.show', $article)
                ->with('info', 'Bạn đã bày tỏ quan tâm buổi workshop này rồi.');
        }

        ArticleInterest::query()->create([
            'article_id' => $article->getKey(),
            'session_token' => $sessionToken,
            'ip_hash' => GuestEngagement::ipHash(),
        ]);

        return redirect()
            ->route('workshops.show', $article)
            ->with('success', 'Cảm ơn bạn! Chúng tôi ghi nhận bạn quan tâm buổi workshop này.');
    }

    private function isPublicWorkshop(Article $article): bool
    {
        return $article->type === ArticleType::Announcement
            && $article->status === GeneralStatus::ACTIVE
            && ($article->published_at === null || $article->published_at <= now());
    }
}
