<?php

namespace App\Filament\Widgets;

use App\Models\MediaUpload;
use App\Models\Post;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalPosts = Post::count();
        $publishedPosts = Post::where('status', 'published')->count();

        $totalUsers = User::count();
        $totalMedia = MediaUpload::count();

        return [
            Stat::make('Total Posts', $totalPosts)
                ->description("{$publishedPosts} published")
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success')
                ->chart($this->getWeeklyTrend(Post::class)),

            Stat::make('Total Users', $totalUsers)
                ->description('Registered accounts')
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->chart($this->getWeeklyTrend(User::class)),

            Stat::make('Media Library', $totalMedia)
                ->description('Uploaded files')
                ->descriptionIcon('heroicon-m-photo')
                ->color('danger')
                ->chart($this->getWeeklyTrend(MediaUpload::class)),
        ];
    }

    private function getWeeklyTrend(string $model): array
    {
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $data[] = $model::whereDate('created_at', now()->subDays($i))->count();
        }

        return $data;
    }
}
