<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;

class PolicyController extends Controller
{
    public function show(string $policy)
    {
        abort_unless(in_array($policy, ['service','seller','shop','guest-consent','guest-notice','seller-provision','privacy','member-marketing'], true), 404);
        $document = json_decode(file_get_contents(resource_path('policies/'.$policy.'.json')), true, 512, JSON_THROW_ON_ERROR);
        return view('front.policies.show', compact('document'));
    }
}
