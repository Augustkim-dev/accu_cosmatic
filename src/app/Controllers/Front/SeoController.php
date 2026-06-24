<?php
namespace App\Controllers\Front;

use App\Database;
use App\Settings;

class SeoController
{
    private function baseUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host;
    }

    public function sitemap(array $params = []): void
    {
        $base = $this->baseUrl();
        $urls = ['/', '/products', '/brands', '/news', '/contact'];

        try {
            foreach (Database::all('SELECT id FROM products WHERE is_active=1') as $p) $urls[] = '/products/' . $p['id'];
            foreach (Database::all("SELECT id FROM posts WHERE type='news' AND is_active=1") as $p) $urls[] = '/news/' . $p['id'];
            foreach (Database::all('SELECT slug FROM pages WHERE is_active=1') as $p) $urls[] = '/page/' . $p['slug'];
        } catch (\Throwable $e) {}

        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            echo '  <url><loc>' . htmlspecialchars($base . $u, ENT_XML1) . '</loc></url>' . "\n";
        }
        echo '</urlset>';
        exit;
    }

    public function robots(array $params = []): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Disallow: /admin\n";
        echo "Disallow: /cart\n";
        echo "Disallow: /checkout\n";
        echo "Disallow: /mypage\n";
        echo "Sitemap: " . $this->baseUrl() . "/sitemap.xml\n";
        exit;
    }
}
