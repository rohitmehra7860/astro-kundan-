<?php

namespace App\Filament\Pages;

use App\Models\Page;
use App\Models\Post;
use App\Models\Category;
use Filament\Pages\Page as FilamentPage;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use Filament\Notifications\Notification;

class SeoAudit extends FilamentPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'SEO Audit';

    protected static ?string $title = 'SEO Audit Dashboard';

    protected string $view = 'filament.pages.seo-audit';

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view seo audit') ?? false;
    }

    public array $stats = [];
    public array $auditItems = [];
    public int $overallScore = 0;
    public string $healthStatus = 'Good';
    public string $activeTab = 'all';

    public function mount(): void
    {
        $this->runAudit();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->runAudit();
    }

    public function refreshAudit(): void
    {
        $this->runAudit();
        Notification::make()
            ->title('SEO audit report refreshed successfully.')
            ->success()
            ->send();
    }

    protected function runAudit(): void
    {
        // Fetch resources
        $pages = Page::with('seoMetadata')->get();
        $posts = Post::with('seoMetadata')->get();
        $categories = Category::with('seoMetadata')->get();

        $items = [];
        $totalScore = 0;
        $scoredItemsCount = 0;

        $counts = [
            'total' => 0,
            'good' => 0,
            'warning' => 0,
            'critical' => 0,
        ];

        // Audit Pages
        foreach ($pages as $page) {
            $audit = $this->auditResource($page, 'Page', $page->title, \App\Filament\Resources\Pages\PageResource::getUrl('edit', ['record' => $page->id]));
            $items[] = $audit;
        }

        // Audit Posts
        foreach ($posts as $post) {
            $audit = $this->auditResource($post, 'Post', $post->title, \App\Filament\Resources\Posts\PostResource::getUrl('edit', ['record' => $post->id]));
            $items[] = $audit;
        }

        // Audit Categories
        foreach ($categories as $category) {
            $editUrl = \App\Filament\Resources\Categories\BlogCategoryResource::getUrl('edit', ['record' => $category->id]);
            $audit = $this->auditResource($category, 'Category', $category->name, $editUrl);
            $items[] = $audit;
        }

        // Process overall statistics
        foreach ($items as $item) {
            $counts['total']++;
            $totalScore += $item['score'];
            $scoredItemsCount++;

            if ($item['status'] === 'critical') {
                $counts['critical']++;
            } elseif ($item['status'] === 'warning') {
                $counts['warning']++;
            } else {
                $counts['good']++;
            }
        }

        $this->overallScore = $scoredItemsCount > 0 ? (int) round($totalScore / $scoredItemsCount) : 100;

        if ($this->overallScore >= 80) {
            $this->healthStatus = 'Excellent';
        } elseif ($this->overallScore >= 50) {
            $this->healthStatus = 'Needs Attention';
        } else {
            $this->healthStatus = 'Critical';
        }

        // Filter items by active tab
        if ($this->activeTab !== 'all') {
            $items = array_filter($items, fn($item) => $item['status'] === $this->activeTab);
        }

        // Sort items: critical first, then warnings, then good
        usort($items, function($a, $b) {
            $statusWeight = ['critical' => 3, 'warning' => 2, 'good' => 1];
            $weightA = $statusWeight[$a['status']] ?? 0;
            $weightB = $statusWeight[$b['status']] ?? 0;
            if ($weightA !== $weightB) {
                return $weightB <=> $weightA; // descending weight
            }
            return $a['score'] <=> $b['score']; // ascending score (worse first)
        });

        $this->auditItems = array_values($items); // re-index after filtering
        $this->stats = $counts;
    }

    protected function auditResource($model, string $type, string $title, string $editUrl): array
    {
        $seo = $model->seoMetadata;
        $score = 0;
        $issues = [];

        if (!$seo) {
            return [
                'id' => $model->id,
                'type' => $type,
                'title' => $title,
                'edit_url' => $editUrl,
                'score' => 0,
                'status' => 'critical',
                'issues' => [
                    ['type' => 'critical', 'message' => 'Missing SEO metadata configuration entirely.'],
                ],
            ];
        }

        // 1. Has SEO Record
        $score += 40;

        // 2. Meta Title
        if (!empty($seo->meta_title)) {
            $score += 20;
            $titleLen = mb_strlen($seo->meta_title);
            if ($titleLen < 30) {
                $issues[] = ['type' => 'warning', 'message' => "Meta Title is too short ($titleLen chars, recommended: 30-60)."];
            } elseif ($titleLen > 60) {
                $issues[] = ['type' => 'warning', 'message' => "Meta Title is too long ($titleLen chars, recommended: 30-60)."];
            } else {
                $score += 10;
            }
        } else {
            $issues[] = ['type' => 'critical', 'message' => 'Meta Title is empty.'];
        }

        // 3. Meta Description
        if (!empty($seo->meta_description)) {
            $score += 20;
            $descLen = mb_strlen($seo->meta_description);
            if ($descLen < 120) {
                $issues[] = ['type' => 'warning', 'message' => "Meta Description is too short ($descLen chars, recommended: 120-160)."];
            } elseif ($descLen > 160) {
                $issues[] = ['type' => 'warning', 'message' => "Meta Description is too long ($descLen chars, recommended: 120-160)."];
            } else {
                $score += 5;
            }
        } else {
            $issues[] = ['type' => 'critical', 'message' => 'Meta Description is empty.'];
        }

        // 4. Open Graph Image
        if (!empty($seo->og_image_id)) {
            $score += 5;
        } else {
            $issues[] = ['type' => 'warning', 'message' => 'Missing social sharing (Open Graph) image.'];
        }

        // Determine Status
        $hasCritical = false;
        $hasWarning = false;
        foreach ($issues as $issue) {
            if ($issue['type'] === 'critical') {
                $hasCritical = true;
            } elseif ($issue['type'] === 'warning') {
                $hasWarning = true;
            }
        }

        $status = 'good';
        if ($hasCritical) {
            $status = 'critical';
        } elseif ($hasWarning) {
            $status = 'warning';
        }

        return [
            'id' => $model->id,
            'type' => $type,
            'title' => $title,
            'edit_url' => $editUrl,
            'score' => $score,
            'status' => $status,
            'issues' => $issues,
        ];
    }
}
