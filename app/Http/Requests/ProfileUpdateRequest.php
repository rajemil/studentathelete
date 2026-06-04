<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\PersonName;
use App\Support\RegistrationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();

        $rules = array_merge(
            RegistrationRules::nameFields(),
            [
                'email' => [
                    'required',
                    'string',
                    'lowercase',
                    'email',
                    'max:255',
                    Rule::unique(User::class)->ignore($user->id),
                ],
            ],
        );

        if ($user->role === 'student') {
            $rules = array_merge($rules, RegistrationRules::studentProfileFields(true));
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        if (is_array($validated) && isset($validated['first_name'], $validated['last_name'])) {
            $validated['name'] = PersonName::combine($validated['first_name'], $validated['last_name']);
        }

        return $validated;
    }
}
