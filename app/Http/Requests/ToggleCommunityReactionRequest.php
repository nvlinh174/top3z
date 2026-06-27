<?php

namespace App\Http\Requests;

use App\Enums\ArticleReactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleCommunityReactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required_without:comment_id', Rule::enum(ArticleReactionType::class)],
            'comment_id' => ['sometimes', 'integer', 'exists:comments,id'],
        ];
    }
}
