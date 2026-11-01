<?php

namespace App\Http\Middleware;

use App\Models\AdminActivity;
use App\Services\AdminAuditRecorder;
use App\Support\OrderClaimActions;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class RecordAdminActivity
{
    public function handle(Request $request, Closure $next)
    {
        $actor = Auth::guard('admin')->user();
        $eligible = $actor && in_array($actor->type, ['admin', 'superadmin'], true) && $request->isMethod('post') && $request->is('admin/*') && ! $request->is('admin/login', 'admin/logout', 'admin/*password*');
        $recorder = app(AdminAuditRecorder::class);
        if ($eligible) {
            $recorder->start($request);
        }
        try {
            $response = $next($request);
            $changes = $eligible ? $recorder->finish() : [];
        } finally {
            $recorder->reset();
        }
        if (! $actor || ! in_array($actor->type, ['admin', 'superadmin'], true) || ! $request->isMethod('post') || ! $request->is('admin/*') || $request->is('admin/login', 'admin/logout', 'admin/*password*') || $response->getStatusCode() >= 400 || $request->session()->has('errors') || $request->session()->has('error_message')) {
            return $response;
        }
        if ($response instanceof JsonResponse && ($response->getData(true)['status'] ?? null) === false) {
            return $response;
        }
        if (Schema::hasTable('admin_activities')) {
            $ids = [];
            foreach (['ids', 'item_ids'] as $key) {
                foreach (array_slice((array) $request->input($key, []), 0, 500) as $id) {
                    if (is_scalar($id) && ctype_digit((string) $id)) {
                        $ids[] = (int) $id;
                    }
                }
            }
            foreach (['id', 'order_id', 'order_item_id', 'product_id', 'user_id'] as $key) {
                if (ctype_digit((string) $request->input($key, ''))) {
                    $ids[] = (int) $request->input($key);
                }
            }
            foreach (array_merge(array_values($request->route()?->parameters() ?? []), (array) $request->attributes->get('admin_activity_ids', [])) as $id) {
                if (is_scalar($id) && ctype_digit((string) $id)) {
                    $ids[] = (int) $id;
                }
            }
            $fields = array_values(array_diff(array_keys($request->except(['_token'])), ['password', 'new_password', 'current_password', 'password_confirmation', 'email', 'phone', 'mobile', 'content', 'message', 'admin_reply', 'attachment']));
            $operation = $request->input('action');
            if (! is_string($operation) || ! array_key_exists($operation, OrderClaimActions::definitions())) {
                $operation = null;
            }
            AdminActivity::create(['admin_id' => $actor->id, 'actor' => mb_substr($actor->name, 0, 100), 'action' => mb_substr($request->route()?->getName() ?: $request->path(), 0, 180), 'method' => 'POST', 'path' => mb_substr($request->path(), 0, 500), 'metadata' => ['ids' => array_values(array_unique($ids)), 'fields' => $fields, 'operation' => $operation, 'changes' => $changes, 'result' => 'success', 'summary' => mb_substr(collect($changes)->map(fn ($change) => $change['resource'].' '.$change['name'].' '.collect($change['fields'])->map(fn ($field) => $field['label'].' '.$field['before'].' '.$field['after'])->implode(' '))->implode(' / '), 0, 5000), 'mode' => $request->routeIs('admin.lists.bulk') ? '일괄 처리' : '개별 처리', 'reason' => $request->routeIs('admin.channel_points.reject') ? mb_substr((string)$request->input('memo',''),0,255) : ($operation && is_string($request->input('reason')) ? mb_substr($request->input('reason'), 0, 1000) : null)]]);
        }

        return $response;
    }
}
