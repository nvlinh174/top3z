<?php

namespace App\Http\Requests;

use App\Models\Article;
use App\Support\CommunityPostDraft;
use Illuminate\Foundation\Http\FormRequest;

class AutosaveCommunityDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $article = $this->route('article');

        if ($article instanceof Article) {
            return $user->can('autosaveDraft', $article);
        }

        return $user->can('create', Article::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string', 'max:50000'],
        ];
    }

    /**
     * @return array{title?: string|null, excerpt?: string|null, body?: string|null}
     */
    public function draftPayload(): array
    {
        return [
            'title' => $this->input('title'),
            'excerpt' => $this->input('excerpt'),
            'body' => $this->input('body'),
        ];
    }

    public function hasSavableContent(): bool
    {
        return CommunityPostDraft::hasSavableContent($this->draftPayload());
    }
}
