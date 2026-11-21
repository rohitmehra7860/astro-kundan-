<footer style="margin-top: 40px; border-top: 1px solid #333; padding-top: 20px; text-align: center; font-size: 12px; color: #888;">
    <p>{{ \App\Models\Setting::get('footer_text', '© ' . date('Y') . ' ' . \App\Models\Setting::get('site_name', config('app.name', 'Corporate Dashboard')) . '. All rights reserved.') }}</p>
</footer>
