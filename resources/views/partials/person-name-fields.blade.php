@props(['user' => null])

@php
    $split = \App\Support\PersonName::split($user?->name ?? '');
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="first_name" value="First Name" />
        <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $split['first_name'])" required autofocus autocomplete="given-name" style="text-transform: uppercase;" />
        <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="last_name" value="Last Name" />
        <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $split['last_name'])" required autocomplete="family-name" style="text-transform: uppercase;" />
        <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
    </div>
</div>
