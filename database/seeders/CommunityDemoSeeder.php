<?php

namespace Database\Seeders;

use App\Enums\ArticleModerationStatus;
use App\Enums\ArticleType;
use App\Enums\GeneralStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class CommunityDemoSeeder extends Seeder
{
    private const ASSETS_PATH = 'database/seeders/assets/community';

    public function run(): void
    {
        $category = $this->ensureCommunityCategory();
        $author = $this->ensureAuthor();

        $this->seedPost(
            slug: 'buoi-workshop-dau-tien-ke-mini',
            category: $category,
            author: $author,
            title: 'Buổi workshop đầu tiên — em làm chiếc kệ mini',
            excerpt: 'Lần đầu cầm máy cắt và lắp ráp — chia sẻ vài ảnh và bài học nhỏ sau buổi tối makerspace.',
            body: $this->firstPostBody(),
            publishedAt: now()->subDays(5),
        );

        $this->seedPost(
            slug: 'ba-dieu-hoc-duoc-khi-build-lan-dau',
            category: $category,
            author: $author,
            title: '3 điều mình học được khi build lần đầu',
            excerpt: 'Đo đạc kỹ, chọn vật liệu phù hợp và đừng ngại hỏi — tóm tắt sau vài tuần ghé Top3z.',
            body: $this->secondPostBody(),
            publishedAt: now()->subDays(12),
        );

        $this->seedPost(
            slug: 'anh-san-pham-tu-buoi-in-3d',
            category: $category,
            author: $author,
            title: 'Ảnh sản phẩm từ buổi in 3D',
            excerpt: 'Vài món đồ nhỏ in thử trên máy FDM — không hoàn hảo nhưng rất vui.',
            body: $this->thirdPostBody(),
            publishedAt: now()->subDays(20),
        );
    }

    private function ensureCommunityCategory(): Category
    {
        $root = Category::query()->find(Category::SYSTEM_ROOT_ID);

        if ($root === null) {
            $root = new Category([
                'name' => 'System',
                'slug' => 'system',
                'status' => GeneralStatus::ACTIVE,
            ]);
            $root->saveAsRoot();
        }

        return Category::query()->firstOrCreate(
            ['slug' => 'chia-se-trai-nghiem'],
            [
                'name' => 'Chia sẻ trải nghiệm',
                'parent_id' => $root->getKey(),
                'status' => GeneralStatus::ACTIVE,
            ],
        );
    }

    private function ensureAuthor(): User
    {
        $user = User::query()->first();

        if ($user !== null) {
            return $user;
        }

        return User::factory()->create([
            'name' => 'Top3z Team',
            'email' => 'team@top3z.test',
        ]);
    }

    private function seedPost(
        string $slug,
        Category $category,
        User $author,
        string $title,
        string $excerpt,
        string $body,
        Carbon $publishedAt,
    ): void {
        $article = Article::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'type' => ArticleType::Article,
                'category_id' => $category->getKey(),
                'author_id' => $author->getKey(),
                'title' => $title,
                'excerpt' => $excerpt,
                'body' => $body,
                'status' => GeneralStatus::ACTIVE,
                'moderation_status' => ArticleModerationStatus::Approved,
                'published_at' => $publishedAt,
            ],
        );

        $this->attachMediaFromAssets($article, $slug);
    }

    private function attachMediaFromAssets(Article $article, string $slug): void
    {
        $directory = base_path(self::ASSETS_PATH.'/'.$slug);

        if (! File::isDirectory($directory)) {
            return;
        }

        $article->clearMediaCollection('thumbnail');
        $article->clearMediaCollection('gallery');

        $thumbnail = $this->firstExistingFile($directory, [
            'thumbnail.jpg',
            'thumbnail.jpeg',
            'thumbnail.png',
            'thumbnail.webp',
        ]);

        if ($thumbnail !== null) {
            $article
                ->addMedia($thumbnail)
                ->preservingOriginal()
                ->toMediaCollection('thumbnail');
        }

        foreach (['gallery-1', 'gallery-2', 'gallery-3'] as $basename) {
            $galleryFile = $this->firstExistingFile($directory, [
                "{$basename}.jpg",
                "{$basename}.jpeg",
                "{$basename}.png",
                "{$basename}.webp",
            ]);

            if ($galleryFile !== null) {
                $article
                    ->addMedia($galleryFile)
                    ->preservingOriginal()
                    ->toMediaCollection('gallery');
            }
        }
    }

    /**
     * @param  list<string>  $candidates
     */
    private function firstExistingFile(string $directory, array $candidates): ?string
    {
        foreach ($candidates as $filename) {
            $path = $directory.'/'.$filename;

            if (File::isFile($path)) {
                return $path;
            }
        }

        return null;
    }

    private function firstPostBody(): string
    {
        return <<<'HTML'
<h2>Bắt đầu từ con số không</h2>
<p>Trước buổi workshop mình chưa từng đụng tới máy cắt. Có người hướng dẫn giải thích từng bước — từ file thiết kế đến lắp ráp chiếc kệ mini mang về.</p>

<h3>Điều mình thích</h3>
<ul>
<li>Không gian thoải mái, được hỏi thoải mái</li>
<li>Làm xong có sản phẩm cụ thể</li>
<li>Gặp vài bạn cùng chí hướng</li>
</ul>
HTML;
    }

    private function secondPostBody(): string
    {
        return <<<'HTML'
<h2>Đo hai lần, cắt một lần</h2>
<p>Nghe quen nhưng đúng là mình học được sau lần đo sai đầu tiên. Thêm nữa là chọn gỗ/vật liệu — nhẹ hay chắc tùy mục đích.</p>

<h3>Đừng ngại hỏi</h3>
<p>Makerspace có người túc cả hơn bạn nghĩ. Hỏi sớm tiết kiệm thời gian và vật liệu.</p>
HTML;
    }

    private function thirdPostBody(): string
    {
        return <<<'HTML'
<h2>In thử, sửa, in lại</h2>
<p>Buổi in 3D cho mình thấy quy trình prototype nhanh thế nào — layer đầu hơi lệch nhưng chỉnh lại file là xong.</p>
HTML;
    }
}
