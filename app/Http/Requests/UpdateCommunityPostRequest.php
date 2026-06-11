<?php

namespace App\Http\Requests;

use App\Support\CommunityPostBody;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class UpdateCommunityPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => [
                'required',
                'string',
                'max:50000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || CommunityPostBody::isEmpty($value)) {
                        $fail('Vui lòng nhập nội dung bài viết.');
                    }
                },
            ],
            'thumbnail' => ['nullable', File::image()->max(5120)],
            'gallery' => ['nullable', 'array', 'max:10'],
            'gallery.*' => [File::image()->max(5120)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Vui lòng nhập tiêu đề.',
            'body.required' => 'Vui lòng nhập nội dung bài viết.',
            'thumbnail.image' => 'Ảnh đại diện phải là file ảnh.',
            'gallery.*.image' => 'Mỗi ảnh trong thư viện phải là file ảnh.',
        ];
    }
}
