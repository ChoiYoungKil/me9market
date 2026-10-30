<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Distributor;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ChannelOrderManagers
{
    public function normalizeEmail(Request $request): string
    {
        if ($request->has('email_local') || $request->has('email_domain')) {
            $parts = $request->validate(['email_local' => 'required|string|max:64|not_regex:/[@\s]/', 'email_domain' => 'required|string|max:253|not_regex:/[@\s]/']);
            $email = trim($parts['email_local']).'@'.trim($parts['email_domain']);
        } else {
            $email = $request->validate(['email' => 'required|string|max:255'])['email'];
        }
        $email = Str::lower(trim($email));
        $request->merge(['email' => $email]);
        $request->validate(['email' => 'required|email:rfc|max:255']);

        return $email;
    }

    public function emailProof(Admin $owner, string $email, ?Distributor $manager): string
    {
        return Crypt::encryptString(json_encode(['owner' => $owner->id, 'vendor' => $owner->vendor_id, 'email' => $email, 'manager' => $manager?->id, 'expires' => now()->addMinutes(30)->timestamp]));
    }

    private function verifiedEmail(Admin $owner, string $email, ?Distributor $manager, string $proof): bool
    {
        try {
            $data = json_decode(Crypt::decryptString($proof), true, 512, JSON_THROW_ON_ERROR);

            return ($data['owner'] ?? null) === $owner->id && (int) ($data['vendor'] ?? 0) === (int) $owner->vendor_id
                && ($data['email'] ?? null) === $email && ($data['manager'] ?? null) === $manager?->id && ($data['expires'] ?? 0) >= now()->timestamp;
        } catch (\Throwable $exception) {
            return false;
        }
    }

    public function save(Request $request, Admin $owner, ?Distributor $manager = null): Distributor
    {
        $email = $this->normalizeEmail($request);
        $data = $request->validate([
            'status' => 'required|in:0,1', 'name' => 'required|string|max:100',
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('distributors', 'email')->ignore($manager?->id)],
            'email_check_token' => 'nullable|string', 'phone' => 'nullable|string|max:50',
            'dispatch_postcode' => ['nullable', 'regex:/^[0-9]{5}$/'], 'dispatch_address' => 'nullable|string|max:255',
            'dispatch_address_detail' => 'nullable|string|max:255', 'memo' => 'nullable|string|max:5000',
            'return_postcode' => 'nullable|string|max:20', 'return_address' => 'nullable|string|max:255',
            'access_started_at' => 'bail|nullable|string|date', 'access_ended_at' => 'bail|nullable|string|date',
            'password' => ['nullable', 'confirmed', Password::min(12)->mixedCase()->letters()->numbers()],
        ], ['email.unique' => '중복된 이메일을 가진 발주 담당이 있습니다.', 'name.required' => '발주담당자를 입력해 주세요.']);
        if ($request->filled('access_started_at') && $request->filled('access_ended_at')) {
            $request->validate(['access_ended_at' => 'after_or_equal:access_started_at'], ['access_ended_at.after_or_equal' => '접속 종료일시는 시작일시 이후로 입력해 주세요.']);
        }
        $unchanged = $manager && $email === Str::lower($manager->email);
        if (! $unchanged && ! $this->verifiedEmail($owner, $email, $manager, $data['email_check_token'] ?? '')) {
            throw ValidationException::withMessages(['email_check_token' => '이메일 중복확인을 완료해 주세요. 이메일을 변경하면 다시 확인해야 합니다.']);
        }
        $manager ??= new Distributor;
        $values = collect($data)->except(['password', 'email_check_token'])->all();
        $values['vendor_id'] = $owner->vendor_id;
        // The PPT contact form does not require separate login credentials.
        if ($request->filled('password')) {
            $values['password'] = Hash::make($data['password']);
        } elseif (! $manager->exists) {
            $values['password'] = Hash::make(Str::random(64));
        }
        $manager->fill($values);
        try {
            $manager->save();
        } catch (QueryException $exception) {
            if (($exception->errorInfo[1] ?? null) === 1062) {
                throw ValidationException::withMessages(['email' => '중복된 이메일을 가진 발주 담당이 있습니다.']);
            }
            throw $exception;
        }

        return $manager;
    }
}
