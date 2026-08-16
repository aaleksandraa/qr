<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Services\Security\RegistrationGuard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
            'website' => ['nullable', 'string', 'max:200'],
            'form_started_at' => ['required', 'integer'],
            'cf-turnstile-response' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function (): void {
            app(RegistrationGuard::class)->assertSafe($this);
        });
    }
}
