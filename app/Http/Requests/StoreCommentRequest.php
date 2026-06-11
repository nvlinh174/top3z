<?php

namespace App\Http\Requests;

use App\Enums\CommentStatus;
use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Article $article */
        $article = $this->route('article');

        return [
            'guest_name' => ['nullable', 'string', 'max:100'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
            'reply_to_id' => [
                'nullable',
                'integer',
                Rule::exists('comments', 'id')->where(function ($query) use ($article): void {
                    $query
                        ->where('article_id', $article->getKey())
                        ->where('status', CommentStatus::Active->value);
                }),
            ],
            'website' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'body.required' => 'Vui lòng nhập ý kiến của bạn.',
            'body.max' => 'Ý kiến không được dài quá :max ký tự.',
            'reply_to_id.exists' => 'Bình luận bạn trả lời không còn tồn tại.',
            'website.prohibited' => 'Không thể gửi biểu mẫu này.',
        ];
    }
}
