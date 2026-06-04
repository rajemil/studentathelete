@props(['prefix' => '', 'profile' => null, 'required' => true])

@php $p = $profile; @endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label :for="$prefix.'birthdate'" value="Birthday" />
        <x-text-input :id="$prefix.'birthdate'" name="birthdate" type="date" class="mt-1 block w-full" :value="old('birthdate', optional($p?->birthdate)->format('Y-m-d'))" @required($required) />
        <x-input-error :messages="$errors->get('birthdate')" class="mt-2" />
    </div>
    <div>
        <x-input-label value="Age (auto)" />
        <div class="mt-1 rounded-md border border-gray-200 dark:border-white/10 px-3 py-2 text-sm text-gray-700 dark:text-gray-200">
            @if($p?->age){{ $p->age }}@else—@endif
        </div>
    </div>
    <div>
        <x-input-label :for="$prefix.'gender'" value="Gender" />
        <select :id="$prefix.'gender'" name="gender" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" @required($required)>
            <option value="">Select...</option>
            @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'prefer_not_to_say' => 'Prefer not to say'] as $val => $label)
                <option value="{{ $val }}" @selected(old('gender', $p?->gender) === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('gender')" class="mt-2" />
    </div>
    <div>
        <x-input-label :for="$prefix.'course'" value="Course / Program" />
        <x-text-input :id="$prefix.'course'" name="course" type="text" class="mt-1 block w-full" :value="old('course', $p?->course)" @required($required) />
        <x-input-error :messages="$errors->get('course')" class="mt-2" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label :for="$prefix.'address'" value="Address" />
        <x-text-input :id="$prefix.'address'" name="address" type="text" class="mt-1 block w-full" :value="old('address', $p?->address)" @required($required) />
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>
    <div>
        <x-input-label :for="$prefix.'height_cm'" value="Height (cm)" />
        <x-text-input :id="$prefix.'height_cm'" name="height_cm" type="number" step="0.01" class="mt-1 block w-full" :value="old('height_cm', $p?->height_cm)" @required($required) />
        <x-input-error :messages="$errors->get('height_cm')" class="mt-2" />
    </div>
    <div>
        <x-input-label :for="$prefix.'weight_kg'" value="Weight (kg)" />
        <x-text-input :id="$prefix.'weight_kg'" name="weight_kg" type="number" step="0.01" class="mt-1 block w-full" :value="old('weight_kg', $p?->weight_kg)" @required($required) />
        <x-input-error :messages="$errors->get('weight_kg')" class="mt-2" />
    </div>
    <div>
        <x-input-label value="BMI (auto)" />
        <div class="mt-1 rounded-md border border-gray-200 dark:border-white/10 px-3 py-2 text-sm text-gray-700 dark:text-gray-200">
            @if($p?->bmi){{ number_format((float) $p->bmi, 2) }}@else—@endif
        </div>
    </div>
</div>
