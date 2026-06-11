<?php

namespace Database\Seeders;

use App\Enums\ArticleType;
use App\Enums\GeneralStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class WorkshopDemoSeeder extends Seeder
{
    private const ASSETS_PATH = 'database/seeders/assets/workshops';

    /**
     * Demo workshop articles for UI preview.
     *
     * Optional images — place files then re-run:
     *   database/seeders/assets/workshops/{slug}/thumbnail.jpg
     *   database/seeders/assets/workshops/{slug}/gallery-1.jpg … gallery-3.jpg
     */
    public function run(): void
    {
        $category = $this->ensureWorkshopCategory();
        $author = $this->ensureAuthor();

        $this->seedWorkshop(
            slug: 'buoi-toi-laser-cut-mica',
            category: $category,
            author: $author,
            title: 'Buổi tối laser cut mica — làm keychain mang về',
            excerpt: 'Thứ Bảy tối mở cửa tự do tại Top3z. Có hướng dẫn cắt laser mica làm móc khóa — vào cửa miễn phí, chỉ trả phí vật liệu nếu bạn mang sản phẩm về.',
            body: $this->upcomingBody(),
            startsAt: $this->nextSaturdayAt(19, 0),
            endsAt: $this->nextSaturdayAt(21, 0),
        );

        $this->seedWorkshop(
            slug: 'mo-cua-thu-7-in-3d',
            category: $category,
            author: $author,
            title: 'Buổi mở cửa thứ Bảy — trải nghiệm in 3D',
            excerpt: 'Đã diễn ra: một tối thứ Bảy mở cửa tự do, tham quan xưởng và thử in 3D với máy FDM. Nhiều bạn lần đầu ghé makerspace.',
            body: $this->pastBody(),
            startsAt: $this->previousSaturdayAt(19, 0),
            endsAt: $this->previousSaturdayAt(21, 0),
        );
    }

    private function ensureWorkshopCategory(): Category
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
            ['slug' => 'buoi-toi-makerspace'],
            [
                'name' => 'Buổi tối makerspace',
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

    private function seedWorkshop(
        string $slug,
        Category $category,
        User $author,
        string $title,
        string $excerpt,
        string $body,
        Carbon $startsAt,
        Carbon $endsAt,
    ): void {
        $article = Article::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'type' => ArticleType::Announcement,
                'category_id' => $category->getKey(),
                'author_id' => $author->getKey(),
                'title' => $title,
                'excerpt' => $excerpt,
                'body' => $body,
                'status' => GeneralStatus::ACTIVE,
                'published_at' => $startsAt->copy()->subWeek(),
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
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

    private function nextSaturdayAt(int $hour, int $minute): Carbon
    {
        $date = now()->copy()->next(Carbon::SATURDAY)->setTime($hour, $minute, 0);

        if ($date->isPast()) {
            $date->addWeek();
        }

        return $date;
    }

    private function previousSaturdayAt(int $hour, int $minute): Carbon
    {
        return now()->copy()->previous(Carbon::SATURDAY)->setTime($hour, $minute, 0);
    }

    private function upcomingBody(): string
    {
        return <<<'HTML'
<h2>Chào mừng bạn đến buổi tối makerspace</h2>
<p>Top3z mở cửa tự do vào tối thứ Bảy — bạn có thể ghé tham quan không gian, xem máy laser, in 3D và các góc làm việc thủ công. Buổi này có thêm <strong>người hướng dẫn</strong> giúp bạn thiết kế và cắt mica làm móc khóa cá nhân.</p>

<h3>Ai nên đến?</h3>
<ul>
    <li>Bạn tò mò makerspace là gì, muốn ghé lần đầu</li>
    <li>Bạn thích DIY, muốn thử laser cut mà chưa có máy ở nhà</li>
    <li>Gia đình, nhóm bạn muốn có một tối cuối tuần khác lạ</li>
</ul>

<h3>Địa điểm</h3>
<p><strong>Top3z Makerspace</strong> — địa chỉ chi tiết và chỉ đường xem trên Google Maps (gắn link trong bài khi có). Có chỗ để xe máy; đến sớm 10–15 phút nếu bạn muốn làm sản phẩm ngay từ đầu buổi.</p>

<h3>Chi phí</h3>
<ul>
    <li><strong>Vào cửa &amp; tham quan:</strong> miễn phí</li>
    <li><strong>Làm móc khóa mica mang về:</strong> trả phí vật liệu + khấu hao máy (ước tính 30.000–50.000đ/tấm tùy kích thước)</li>
    <li>Không bắt buộc đăng ký trước — đến trực tiếp trong khung giờ mở cửa</li>
</ul>

<h3>Quy trình buổi tối</h3>
<ol>
    <li>19:00 — Mở cửa, tham quan tự do</li>
    <li>19:15 — Giới thiệu nhanh về laser &amp; an toàn</li>
    <li>19:30–20:45 — Hướng dẫn thiết kế &amp; cắt mica (làm theo nhóm nhỏ)</li>
    <li>20:45–21:00 — Hoàn thiện sản phẩm, chụp ảnh, giao lưu</li>
</ol>

<p><em>Lưu ý: Trẻ em dưới 12 tuổi nên có người lớn đi cùng. Mang laptop nếu muốn tự chỉnh file vector; không có máy vẫn dùng được mẫu có sẵn.</em></p>
HTML;
    }

    private function pastBody(): string
    {
        return <<<'HTML'
<h2>Cảm ơn mọi người đã ghé!</h2>
<p>Buổi mở cửa thứ Bảy vừa qua đã có khoảng <strong>15–20 lượt khách</strong> ghé tham quan — nhiều bạn lần đầu biết đến makerspace và thử in 3D một mẫu nhỏ trên máy FDM.</p>

<h3>Điều gì đã diễn ra?</h3>
<ul>
    <li>Tham quan tự do khu vực in 3D, bàn làm việc và kho vật liệu</li>
    <li>Demo in mẫu keycap / đế điện thoại mini — ai muốn có thể đặt in (tính phí filament)</li>
    <li>Hỏi đáp về membership, giờ mở cửa và các buổi tối cố định trong tuần</li>
</ul>

<h3>Địa điểm</h3>
<p><strong>Top3z Makerspace</strong> — cùng địa chỉ chi nhánh hiện tại. Buổi sau vẫn mô hình cũ: vào cửa miễn phí, chỉ trả khi dùng vật liệu hoặc máy để làm sản phẩm mang về.</p>

<h3>Góp ý từ khách</h3>
<p>Nhiều bạn hỏi thêm về buổi <strong>laser cut mica</strong> — buổi tiếp theo sẽ có hướng dẫn chi tiết hơn. Theo dõi lịch workshop trên trang này để không bỏ lỡ.</p>

<p><em>Ảnh recap buổi sẽ được cập nhật khi có. Cảm ơn bạn đã đồng hành cùng Top3z!</em></p>
HTML;
    }
}
