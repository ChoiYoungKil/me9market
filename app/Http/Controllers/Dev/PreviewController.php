<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PreviewController extends Controller
{
    public function recoveryMail(Request $request, string $id)
    {
        abort_unless(app()->environment('local') && in_array($request->ip(), ['127.0.0.1', '::1'], true), 404);
        $expires = $request->session()->get('account_recovery_previews.'.$id);
        abort_unless(is_numeric($expires) && now()->timestamp < (int) $expires, 404);
        $path = 'account-recovery-mail/'.$id.'.enc';
        abort_unless(\Illuminate\Support\Facades\Storage::disk('local')->exists($path), 404);
        $html = \Illuminate\Support\Facades\Crypt::decryptString(\Illuminate\Support\Facades\Storage::disk('local')->get($path));
        $notice = '<div style="padding:14px 20px;background:#ede4fa;color:#75518d;text-align:center;font:13px/1.7 sans-serif;">로컬 검수 메일함 · 외부 이메일로 발송하지 않은 검수용 메일입니다.</div>';
        return response(preg_replace('/(<body[^>]*>)/', '$1'.$notice, $html, 1))
            ->header('Content-Type', 'text/html; charset=UTF-8')->header('Cache-Control', 'no-store, private')
            ->header('Referrer-Policy', 'no-referrer')->header('X-Frame-Options', 'DENY');
    }

    public function version(Request $request)
    {
        abort_unless(app()->environment('local') && in_array($request->ip(), ['127.0.0.1', '::1'], true), 404);

        $files = [];
        $styles = [];
        foreach (['resources/views', 'routes', 'app', 'config', 'public/me9market', 'public/mypage_assets', 'public/shop', 'public/common'] as $directory) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(base_path($directory), \FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if ($file->isFile() && ! $file->isLink() && in_array(strtolower($file->getExtension()), ['php', 'css', 'js', 'webp', 'png'], true)) {
                    if (strtolower($file->getExtension()) === 'css') {
                        $styles[] = $file->getPathname().':'.hash_file('sha256', $file->getPathname());
                    } else {
                        $files[] = $file->getPathname().':'.$file->getMTime().':'.$file->getSize();
                    }
                }
            }
        }
        sort($files);
        sort($styles);

        return response()->json([
            'version' => hash('sha256', implode("\n", array_merge($files, $styles))),
            'documentVersion' => hash('sha256', implode("\n", $files)),
            'styleVersion' => hash('sha256', implode("\n", $styles)),
        ])
            ->header('Cache-Control', 'no-store, private');
    }
}
