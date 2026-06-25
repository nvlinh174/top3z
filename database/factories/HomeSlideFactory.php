<?php

namespace Database\Factories;

use App\Models\HomeSlide;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;

/**
 * @extends Factory<HomeSlide>
 */
class HomeSlideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'sort_order' => HomeSlide::nextSortOrder(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }

    public function withImage(): static
    {
        return $this->afterCreating(function (HomeSlide $slide): void {
            $slide->addMedia(UploadedFile::fake()->image('home-slide.jpg', 1920, 600))
                ->toMediaCollection('image');
        });
    }
}
