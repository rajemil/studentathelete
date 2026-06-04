@props(['profile' => null, 'required' => true])

@php $p = $profile; @endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="birthdate" value="Birthday" />
        <x-text-input id="birthdate" name="birthdate" type="date" class="mt-1 block w-full" :value="old('birthdate', optional($p?->birthdate)->format('Y-m-d'))" {{ $required ? 'required' : '' }} />
        <x-input-error :messages="$errors->get('birthdate')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="gender" value="Gender" />
        <select id="gender" name="gender" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" {{ $required ? 'required' : '' }}>
            <option value="">Select...</option>
            @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'prefer_not_to_say' => 'Prefer not to say'] as $val => $label)
                <option value="{{ $val }}" @selected(old('gender', $p?->gender) === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('gender')" class="mt-2" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="address" value="Address" />
        <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $p?->address)" {{ $required ? 'required' : '' }} />
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="profession" value="Profession" />
        <x-text-input id="profession" name="profession" type="text" class="mt-1 block w-full" :value="old('profession', $p?->profession)" {{ $required ? 'required' : '' }} />
        <x-input-error :messages="$errors->get('profession')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="field_expertise" value="Field of Expertise" />
        <x-text-input id="field_expertise" name="field_expertise" type="text" class="mt-1 block w-full" :value="old('field_expertise', $p?->field_expertise)" {{ $required ? 'required' : '' }} />
        <x-input-error :messages="$errors->get('field_expertise')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="coaching_experience_years" value="Experience (years)" />
        <x-text-input id="coaching_experience_years" name="coaching_experience_years" type="number" min="0" max="80" class="mt-1 block w-full" :value="old('coaching_experience_years', $p?->coaching_experience_years)" {{ $required ? 'required' : '' }} />
        <x-input-error :messages="$errors->get('coaching_experience_years')" class="mt-2" />
    </div>
</div>
