<x-filament-panels::page>
    <!-- Custom CSS Stylesheet for SEO Audit Dashboard -->
    <style>
        .seo-audit-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            font-family: inherit;
        }

        /* Header Panel */
        .seo-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fff;
            padding: 1.25rem;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .dark .seo-header {
            background-color: #0c0a09; /* Matches zinc-950 in Filament v4 dark mode */
            border-color: #1c1917;
        }
        .seo-header-title h2 {
            font-size: 1rem;
            font-weight: 600;
            color: #111827;
        }
        .dark .seo-header-title h2 {
            color: #f9fafb;
        }
        .seo-header-title p {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        .dark .seo-header-title p {
            color: #a8a29e;
        }

        /* Widgets Grid */
        .seo-widgets-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        @media (min-width: 768px) {
            .seo-widgets-grid {
                grid-template-columns: 1fr 2fr;
            }
        }

        /* Score Card */
        .seo-score-card {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .dark .seo-score-card {
            background-color: #0c0a09;
            border-color: #1c1917;
        }
        .seo-score-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
        }
        .dark .seo-score-title {
            color: #a8a29e;
        }

        /* Circle Gauge */
        .seo-gauge-wrapper {
            position: relative;
            width: 8rem;
            height: 8rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .seo-gauge-svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }
        .seo-gauge-bg {
            stroke: #f3f4f6;
        }
        .dark .seo-gauge-bg {
            stroke: #1c1917;
        }
        .seo-gauge-fill-success {
            stroke: #22c55e;
        }
        .seo-gauge-fill-warning {
            stroke: #eab308;
        }
        .seo-gauge-fill-danger {
            stroke: #ef4444;
        }
        .seo-gauge-text {
            position: absolute;
            text-align: center;
        }
        .seo-gauge-percentage {
            font-size: 1.75rem;
            font-weight: 700;
            color: #111827;
            line-height: 1;
        }
        .dark .seo-gauge-percentage {
            color: #f9fafb;
        }
        .seo-gauge-status {
            font-size: 0.625rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-top: 0.25rem;
        }
        .seo-status-success { color: #16a34a; }
        .seo-status-warning { color: #d97706; }
        .seo-status-danger { color: #dc2626; }

        /* Stats Grid */
        .seo-stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        .seo-stat-card {
            background-color: #fff;
            padding: 1.25rem;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .seo-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .dark .seo-stat-card {
            background-color: #0c0a09;
            border-color: #1c1917;
        }
        .seo-stat-icon {
            padding: 0.75rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .seo-icon-bg-total { background-color: #f3f4f6; color: #4b5563; }
        .dark .seo-icon-bg-total { background-color: #1c1917; color: #d6d3d1; }
        .seo-icon-bg-good { background-color: #f0fdf4; color: #16a34a; }
        .dark .seo-icon-bg-good { background-color: #052e16; color: #4ade80; }
        .seo-icon-bg-warning { background-color: #fefbeb; color: #ca8a04; }
        .dark .seo-icon-bg-warning { background-color: #451a03; color: #facc15; }
        .seo-icon-bg-critical { background-color: #fef2f2; color: #dc2626; }
        .dark .seo-icon-bg-critical { background-color: #450a0a; color: #f87171; }

        .seo-stat-val {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            line-height: 1.2;
        }
        .dark .seo-stat-val {
            color: #f9fafb;
        }
        .seo-stat-lbl {
            font-size: 0.75rem;
            font-weight: 500;
            color: #6b7280;
        }
        .dark .seo-stat-lbl {
            color: #a8a29e;
        }

        /* Tabs Bar */
        .seo-tabs-bar {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
            gap: 1rem;
        }
        .dark .seo-tabs-bar {
            border-color: #1c1917;
        }
        .seo-tab-btn {
            background: none;
            border: none;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            transition: color 0.2s, border-color 0.2s;
            color: #6b7280;
        }
        .dark .seo-tab-btn {
            color: #a8a29e;
        }
        .seo-tab-btn.active {
            color: #d97706; /* Matches primary theme color warning amber */
            border-color: #d97706;
        }
        .dark .seo-tab-btn.active {
            color: #eab308;
            border-color: #eab308;
        }

        /* Empty State */
        .seo-empty-state {
            background-color: #fff;
            padding: 3rem 1.5rem;
            text-align: center;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .dark .seo-empty-state {
            background-color: #0c0a09;
            border-color: #1c1917;
        }
        .seo-empty-icon {
            width: 3rem;
            height: 3rem;
            background-color: #f0fdf4;
            color: #16a34a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .dark .seo-empty-icon {
            background-color: #052e16;
            color: #4ade80;
        }
        .seo-empty-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1f2937;
        }
        .dark .seo-empty-title {
            color: #f9fafb;
        }
        .seo-empty-desc {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        .dark .seo-empty-desc {
            color: #a8a29e;
        }

        /* Table container */
        .seo-table-container {
            background-color: #fff;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .dark .seo-table-container {
            background-color: #0c0a09;
            border-color: #1c1917;
        }
        .seo-table-scroll {
            overflow-x: auto;
        }
        .seo-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .seo-table th {
            background-color: #f9fafb;
            padding: 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }
        .dark .seo-table th {
            background-color: #1c1917;
            color: #a8a29e;
            border-color: #1c1917;
        }
        .seo-table td {
            padding: 1rem;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }
        .dark .seo-table td {
            border-color: #1c1917;
        }
        .seo-table tr:hover {
            background-color: rgba(243, 244, 246, 0.5);
        }
        .dark .seo-table tr:hover {
            background-color: rgba(28, 25, 23, 0.2);
        }

        /* Resource badge */
        .seo-badge {
            display: inline-block;
            padding: 0.125rem 0.5rem;
            font-size: 0.625rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-radius: 9999px;
            text-align: center;
        }
        .seo-badge-page { background-color: #eff6ff; color: #1d4ed8; }
        .dark .seo-badge-page { background-color: rgba(29, 78, 216, 0.2); color: #60a5fa; }
        .seo-badge-post { background-color: #faf5ff; color: #6b21a8; }
        .dark .seo-badge-post { background-color: rgba(107, 33, 168, 0.2); color: #c084fc; }
        .seo-badge-product { background-color: #ecfdf5; color: #047857; }
        .dark .seo-badge-product { background-color: rgba(4, 120, 87, 0.2); color: #34d399; }
        .seo-badge-category { background-color: #fffbeb; color: #b45309; }
        .dark .seo-badge-category { background-color: rgba(180, 83, 9, 0.2); color: #fbbf24; }

        /* Score Progress */
        .seo-progress-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .seo-progress-bar-bg {
            width: 100%;
            max-width: 6rem;
            background-color: #f3f4f6;
            border-radius: 9999px;
            height: 0.5rem;
            overflow: hidden;
            flex-shrink: 0;
        }
        .dark .seo-progress-bar-bg {
            background-color: #1c1917;
        }
        .seo-progress-bar-fill {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.5s ease;
        }
        .seo-bg-success { background-color: #22c55e; }
        .seo-bg-warning { background-color: #eab308; }
        .seo-bg-danger { background-color: #ef4444; }

        .seo-progress-text {
            font-size: 0.75rem;
            font-weight: 700;
            color: #4b5563;
            width: 2rem;
            flex-shrink: 0;
        }
        .dark .seo-progress-text {
            color: #d6d3d1;
        }

        /* Issues list */
        .seo-issues-list {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .seo-issue-item {
            display: flex;
            align-items: flex-start;
            gap: 0.375rem;
            line-height: 1.3;
        }
        .seo-issue-icon {
            flex-shrink: 0;
            font-size: 0.75rem;
        }
        .seo-issue-text-critical {
            color: #b91c1c;
            font-weight: 500;
        }
        .dark .seo-issue-text-critical {
            color: #f87171;
        }
        .seo-issue-text-warning {
            color: #4b5563;
        }
        .dark .seo-issue-text-warning {
            color: #d6d3d1;
        }
        .seo-issue-text-good {
            color: #15803d;
            font-weight: 500;
        }
        .dark .seo-issue-text-good {
            color: #4ade80;
        }

        /* Action Buttons */
        .seo-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 0.375rem;
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.2s, color 0.2s, transform 0.2s;
        }
        .seo-action-btn:hover {
            background-color: #e5e7eb;
            color: #111827;
            transform: scale(1.02);
        }
        .dark .seo-action-btn {
            background-color: #1c1917;
            color: #d6d3d1;
            border-color: #2e2a24;
        }
        .dark .seo-action-btn:hover {
            background-color: #2e2a24;
            color: #f9fafb;
        }
    </style>

    <div class="seo-audit-container">
        <!-- Dashboard Header Actions -->
        <div class="seo-header">
            <div class="seo-header-title">
                <h2>SEO Health Scanner</h2>
                <p>Last scan performed just now.</p>
            </div>
            <x-filament::button wire:click="refreshAudit" icon="heroicon-m-arrow-path" size="sm" class="transition-transform duration-300 hover:rotate-180">
                Rescan Database
            </x-filament::button>
        </div>

        <!-- Top Widgets Grid -->
        <div class="seo-widgets-grid">
            <!-- Overall Score Circle -->
            <div class="seo-score-card">
                <h3 class="seo-score-title">Overall Site Score</h3>
                <div class="seo-gauge-wrapper">
                    <svg class="seo-gauge-svg" viewBox="0 0 100 100">
                        <circle class="seo-gauge-bg" stroke-width="8" fill="transparent" r="40" cx="50" cy="50"/>
                        <circle class="transition-all duration-1000 ease-out {{ $overallScore >= 80 ? 'seo-gauge-fill-success' : ($overallScore >= 50 ? 'seo-gauge-fill-warning' : 'seo-gauge-fill-danger') }}"
                                stroke-width="8" stroke-dasharray="251.2" stroke-dashoffset="{{ 251.2 - (251.2 * $overallScore) / 100 }}" stroke-linecap="round" fill="transparent" r="40" cx="50" cy="50"/>
                    </svg>
                    <div class="seo-gauge-text">
                        <span class="seo-gauge-percentage">{{ $overallScore }}%</span>
                        <p class="seo-gauge-status {{ $overallScore >= 80 ? 'seo-status-success' : ($overallScore >= 50 ? 'seo-status-warning' : 'seo-status-danger') }}">{{ $healthStatus }}</p>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="seo-stats-grid">
                <!-- Total Scanned -->
                <div class="seo-stat-card">
                    <div class="seo-stat-icon seo-icon-bg-total">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="seo-stat-val">{{ $stats['total'] }}</p>
                        <p class="seo-stat-lbl">Scanned Resources</p>
                    </div>
                </div>

                <!-- Good SEO -->
                <div class="seo-stat-card">
                    <div class="seo-stat-icon seo-icon-bg-good">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="seo-stat-val">{{ $stats['good'] }}</p>
                        <p class="seo-stat-lbl">Healthy SEO</p>
                    </div>
                </div>

                <!-- Warnings -->
                <div class="seo-stat-card">
                    <div class="seo-stat-icon seo-icon-bg-warning">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <p class="seo-stat-val">{{ $stats['warning'] }}</p>
                        <p class="seo-stat-lbl">Warnings Raised</p>
                    </div>
                </div>

                <!-- Critical -->
                <div class="seo-stat-card">
                    <div class="seo-stat-icon seo-icon-bg-critical">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="seo-stat-val">{{ $stats['critical'] }}</p>
                        <p class="seo-stat-lbl">Critical Errors</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Tabs -->
        <div class="seo-tabs-bar">
            <button wire:click="setTab('all')" class="seo-tab-btn {{ $activeTab === 'all' ? 'active' : '' }}">
                All ({{ $stats['total'] }})
            </button>
            <button wire:click="setTab('critical')" class="seo-tab-btn {{ $activeTab === 'critical' ? 'active' : '' }}">
                Critical ({{ $stats['critical'] }})
            </button>
            <button wire:click="setTab('warning')" class="seo-tab-btn {{ $activeTab === 'warning' ? 'active' : '' }}">
                Warnings ({{ $stats['warning'] }})
            </button>
            <button wire:click="setTab('good')" class="seo-tab-btn {{ $activeTab === 'good' ? 'active' : '' }}">
                Healthy ({{ $stats['good'] }})
            </button>
        </div>

        <!-- Audited Resources List -->
        @if (count($auditItems) === 0)
            <div class="seo-empty-state">
                <div class="seo-empty-icon">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="seo-empty-title">No issues matching this filter</h3>
                <p class="seo-empty-desc">All scanned resources are running smoothly with healthy SEO parameters.</p>
            </div>
        @else
            <div class="seo-table-container">
                <div class="seo-table-scroll">
                    <table class="seo-table">
                        <thead>
                            <tr>
                                <th style="width: 120px;">Resource Type</th>
                                <th>Name / Title</th>
                                <th style="width: 160px;">SEO Score</th>
                                <th>Issues Identified</th>
                                <th style="width: 100px; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($auditItems as $item)
                                <tr>
                                    <!-- Resource Type Badge -->
                                    <td>
                                        <span class="seo-badge
                                            {{ $item['type'] === 'Page' ? 'seo-badge-page' : '' }}
                                            {{ $item['type'] === 'Post' ? 'seo-badge-post' : '' }}
                                            {{ $item['type'] === 'Product' ? 'seo-badge-product' : '' }}
                                            {{ $item['type'] === 'Category' ? 'seo-badge-category' : '' }}
                                        ">
                                            {{ $item['type'] }}
                                        </span>
                                    </td>

                                    <!-- Resource Title -->
                                    <td style="font-weight: 500; color: inherit;">
                                        {{ $item['title'] }}
                                    </td>

                                    <!-- SEO Score Progress Bar -->
                                    <td>
                                        <div class="seo-progress-wrapper">
                                            <div class="seo-progress-bar-bg">
                                                <div class="seo-progress-bar-fill
                                                    {{ $item['score'] >= 80 ? 'seo-bg-success' : ($item['score'] >= 50 ? 'seo-bg-warning' : 'seo-bg-danger') }}"
                                                     style="width: {{ $item['score'] }}%"></div>
                                            </div>
                                            <span class="seo-progress-text">{{ $item['score'] }}%</span>
                                        </div>
                                    </td>

                                    <!-- Issues Checklist -->
                                    <td>
                                        <div class="seo-issues-list">
                                            @if (count($item['issues']) === 0)
                                                <div class="seo-issue-item">
                                                    <span class="seo-issue-icon">✓</span>
                                                    <span class="seo-issue-text-good">All SEO elements look healthy!</span>
                                                </div>
                                            @else
                                                @foreach ($item['issues'] as $issue)
                                                    <div class="seo-issue-item">
                                                        @if ($issue['type'] === 'critical')
                                                            <span class="seo-issue-icon">❌</span>
                                                            <span class="seo-issue-text-critical">{{ $issue['message'] }}</span>
                                                        @else
                                                            <span class="seo-issue-icon">⚠️</span>
                                                            <span class="seo-issue-text-warning">{{ $issue['message'] }}</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Actions (Fix URL link) -->
                                    <td style="text-align: right;">
                                        <a href="{{ $item['edit_url'] }}" class="seo-action-btn">
                                            Fix SEO
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
