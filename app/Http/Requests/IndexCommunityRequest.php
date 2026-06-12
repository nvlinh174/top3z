<?php

namespace App\Http\Requests;

use App\Enums\CommunityFeedSort;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCommunityRequest extends FormRequest
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
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'string', Rule::enum(CommunityFeedSort::class)],
            'category' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function searchQuery(): ?string
    {
        $query = trim((string) $this->validated('q', ''));

        return $query === '' ? null : $query;
    }

    public function sort(): CommunityFeedSort
    {
        $sort = $this->validated('sort');

        if ($sort === null) {
            return CommunityFeedSort::Latest;
        }

        return CommunityFeedSort::from($sort);
    }

    public function categorySlug(): ?string
    {
        $category = trim((string) $this->validated('category', ''));

        return $category === '' ? null : $category;
    }

    /**
     * @return array<string, string>
     */
    public function feedQuery(array $overrides = []): array
    {
        $query = array_filter([
            'q' => $this->searchQuery(),
            'sort' => $this->sort() !== CommunityFeedSort::Latest ? $this->sort()->value : null,
            'category' => $this->categorySlug(),
        ], fn (?string $value): bool => filled($value));

        return array_filter(
            array_merge($query, $overrides),
            fn (?string $value): bool => filled($value),
        );
    }
}
