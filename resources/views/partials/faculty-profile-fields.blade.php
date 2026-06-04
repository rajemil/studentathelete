@props(['profile' => null, 'required' => true])

@php
    $p = $profile;
    $inputClass = 'mt-1 block w-full rounded-md shadow-sm transition border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-white/35 dark:focus:border-indigo-600 dark:focus:ring-indigo-600';
    $selectClass = $inputClass;
    $req = $required ? 'required' : '';
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="birthdate" value="Birthday" />
        <input
            id="birthdate"
            name="birthdate"
            type="date"
            class="{{ $inputClass }}"
            value="{{ old('birthdate', optional($p?->birthdate)->format('Y-m-d')) }}"
            {{ $req }}
        />
        <x-input-error :messages="$errors->get('birthdate')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="gender" value="Gender" />
        <select id="gender" name="gender" class="{{ $selectClass }}" {{ $req }}>
            <option value="">Select...</option>
            @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'prefer_not_to_say' => 'Prefer not to say'] as $val => $label)
                <option value="{{ $val }}" @selected(old('gender', $p?->gender) === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('gender')" class="mt-2" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="address" value="Address" />
        <input
            id="address"
            name="address"
            type="text"
            class="{{ $inputClass }}"
            value="{{ old('address', $p?->address) }}"
            {{ $req }}
        />
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="profession" value="Profession" />
        <input
            id="profession"
            name="profession"
            type="text"
            class="{{ $inputClass }}"
            value="{{ old('profession', $p?->profession) }}"
            {{ $req }}
        />
        <x-input-error :messages="$errors->get('profession')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="field_expertise" value="Field of expertise" />
        <input
            id="field_expertise"
            name="field_expertise"
            type="text"
            class="{{ $inputClass }}"
            value="{{ old('field_expertise', $p?->field_expertise) }}"
            {{ $req }}
        />
        <x-input-error :messages="$errors->get('field_expertise')" class="mt-2" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="coaching_experience_years" value="Coaching experience (years)" />
        <input
            id="coaching_experience_years"
            name="coaching_experience_years"
            type="number"
            min="0"
            max="80"
            class="{{ $inputClass }}"
            value="{{ old('coaching_experience_years', $p?->coaching_experience_years) }}"
            {{ $req }}
        />
        <x-input-error :messages="$errors->get('coaching_experience_years')" class="mt-2" />
    </div>
</div>
