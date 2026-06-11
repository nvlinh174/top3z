<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RelocateMediaToPublicDisk extends Command
{
    protected $signature = 'media:relocate-to-public-disk {--dry-run : List media that would be moved without changing files}';

    protected $description = 'Move Spatie media files from private disks (e.g. local) to the configured public media disk';

    public function handle(): int
    {
        $targetDisk = (string) config('media-library.disk_name');

        if (! config("filesystems.disks.{$targetDisk}")) {
            $this->error("Disk [{$targetDisk}] is not configured.");

            return self::FAILURE;
        }

        $query = Media::query()->where(function ($builder) use ($targetDisk): void {
            $builder
                ->where('disk', '!=', $targetDisk)
                ->orWhere('conversions_disk', '!=', $targetDisk);
        });

        $count = $query->count();

        if ($count === 0) {
            $this->info("All media already uses disk [{$targetDisk}].");

            return self::SUCCESS;
        }

        $this->info("Found {$count} media record(s) to relocate to [{$targetDisk}].");

        if ($this->option('dry-run')) {
            $query->get(['id', 'disk', 'conversions_disk', 'file_name', 'collection_name'])->each(
                fn (Media $media): int => $this->line("  #{$media->id} {$media->collection_name}/{$media->file_name} (disk: {$media->disk})") ?? 0
            );

            return self::SUCCESS;
        }

        $relocated = 0;

        $query->each(function (Media $media) use ($targetDisk, &$relocated): void {
            $sourceDisk = $media->disk;

            if ($sourceDisk === $targetDisk) {
                $media->update(['conversions_disk' => $targetDisk]);

                return;
            }

            $directory = (string) $media->getKey();

            if (! Storage::disk($sourceDisk)->directoryExists($directory)) {
                $this->warn("Skipping media #{$media->id}: directory missing on disk [{$sourceDisk}].");

                return;
            }

            foreach (Storage::disk($sourceDisk)->allFiles($directory) as $path) {
                Storage::disk($targetDisk)->put($path, Storage::disk($sourceDisk)->get($path));
            }

            Storage::disk($sourceDisk)->deleteDirectory($directory);

            $media->update([
                'disk' => $targetDisk,
                'conversions_disk' => $targetDisk,
            ]);

            $relocated++;
            $this->line("Relocated media #{$media->id} → {$targetDisk}");
        });

        $this->info("Done. Relocated {$relocated} media record(s).");

        return self::SUCCESS;
    }
}
