<?php
namespace App;

/**
 * 파일 저장 추상화.
 * 지금은 로컬 디스크(public/uploads) 드라이버.
 * 나중에 Cloudflare R2 / S3로 갈 때 이 클래스의 put()/delete()만 교체하면
 * 브랜드·제품 등 호출부 코드는 그대로 둘 수 있다.
 *
 * 반환/저장 형식: 웹에서 접근 가능한 상대경로 (예: /uploads/2026/06/ab12cd.jpg)
 */
class Storage
{
    private const ALLOWED = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private const PUBLIC_DIR = __DIR__ . '/../public';

    /** 업로드 파일 1건 저장 → 웹 상대경로 또는 null */
    public static function put(array $file, string $subdir = 'uploads'): ?string
    {
        if (empty($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED, true)) {
            return null;
        }
        // 실제 이미지인지 검증 (확장자 위조 방지)
        if (@getimagesize($file['tmp_name']) === false) {
            return null;
        }

        $rel = '/' . trim($subdir, '/') . '/' . date('Y/m') . '/' . bin2hex(random_bytes(8)) . '.' . $ext;
        $abs = self::PUBLIC_DIR . $rel;
        if (!is_dir(dirname($abs))) {
            @mkdir(dirname($abs), 0775, true);
        }
        if (!@move_uploaded_file($file['tmp_name'], $abs)) {
            // CLI/테스트 환경 대비 fallback
            if (!@rename($file['tmp_name'], $abs)) return null;
        }
        return $rel;
    }

    /** 여러 파일($_FILES['images'] 형태) → 저장된 상대경로 배열 */
    public static function putMany(array $files, string $subdir = 'uploads'): array
    {
        $out = [];
        if (empty($files['name']) || !is_array($files['name'])) return $out;
        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            $one = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i] ?? '',
                'error'    => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size'     => $files['size'][$i] ?? 0,
            ];
            $rel = self::put($one, $subdir);
            if ($rel) $out[] = $rel;
        }
        return $out;
    }

    public static function delete(?string $rel): void
    {
        if (!$rel) return;
        $abs = self::PUBLIC_DIR . $rel;
        if (is_file($abs)) @unlink($abs);
    }
}
