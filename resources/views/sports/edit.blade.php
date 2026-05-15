<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Edit Sport</h2>
                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $sport->name }}</div>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dash-card rounded-3xl p-8">
                <form method="POST" action="{{ route('sports.update', $sport) }}{{ request()->boolean('modal') ? '?modal=1' : '' }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" value="Sport name" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required :value="old('name', $sport->name)" />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="slug" value="Slug" />
                        <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug', $sport->slug)" />
                        <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                    </div>

                    <div>
                        <x-input-label for="description" value="Description (optional)" />
                        <textarea id="description" name="description" rows="5" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-gray-100 focus:border-[#FF7A1A] focus:ring-[#FF7A1A] transition">{{ old('description', $sport->description) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 p-5 space-y-4">
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Student application rules</div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Used when students apply from their dashboard. Leave blank to skip a rule.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="qual_min_age" value="Min age" />
                                <x-text-input id="qual_min_age" name="qual_min_age" type="number" class="mt-1 block w-full" :value="old('qual_min_age', $sport->qual_min_age)" />
                                <x-input-error class="mt-2" :messages="$errors->get('qual_min_age')" />
                            </div>
                            <div>
                                <x-input-label for="qual_max_age" value="Max age" />
                                <x-text-input id="qual_max_age" name="qual_max_age" type="number" class="mt-1 block w-full" :value="old('qual_max_age', $sport->qual_max_age)" />
                                <x-input-error class="mt-2" :messages="$errors->get('qual_max_age')" />
                            </div>
                            <div>
                                <x-input-label for="qual_min_height_cm" value="Min height (cm)" />
                                <x-text-input id="qual_min_height_cm" name="qual_min_height_cm" type="number" class="mt-1 block w-full" :value="old('qual_min_height_cm', $sport->qual_min_height_cm)" />
                                <x-input-error class="mt-2" :messages="$errors->get('qual_min_height_cm')" />
                            </div>
                        </div>
                        <div>
                            <x-input-label value="Allowed genders (optional)" />
                            @php $qg = old('qual_genders', $sport->qual_allowed_genders ?? []); @endphp
                            <div class="mt-2 flex flex-wrap gap-4 text-sm text-gray-800 dark:text-gray-200">
                                @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'prefer_not_to_say' => 'Prefer not to say'] as $val => $label)
                                    <label class="inline-flex items-center gap-2">
                                        <input type="checkbox" name="qual_genders[]" value="{{ $val }}" class="rounded border-gray-300 dark:border-gray-600" @checked(in_array($val, (array) $qg, true)) />
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('qual_genders')" />
                        </div>
                    </div>

                    <!-- Additional requirement toggles -->
                    <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 p-5 space-y-4">
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Additional Application Requirements</div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="require_report_card" value="1" class="rounded border-gray-300 dark:border-gray-600" @checked(old('require_report_card', $sport->require_report_card))>
                                Require Report Card
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="require_medical_form" value="1" class="rounded border-gray-300 dark:border-gray-600" @checked(old('require_medical_form', $sport->require_medical_form))>
                                Require Medical Form
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="require_bp" value="1" class="rounded border-gray-300 dark:border-gray-600" @checked(old('require_bp', $sport->require_bp))>
                                Require Blood Pressure
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="require_heart_rate" value="1" class="rounded border-gray-300 dark:border-gray-600" @checked(old('require_heart_rate', $sport->require_heart_rate))>
                                Require Heart Rate
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="require_allergies" value="1" class="rounded border-gray-300 dark:border-gray-600" @checked(old('require_allergies', $sport->require_allergies))>
                                Require Allergies / Notes
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-2">
                        @unless(request()->boolean('modal'))
                            <a href="{{ route('sports.show', $sport) }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition">Back</a>
                        @endunless
                        <button type="submit" class="inline-flex items-center rounded-xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] px-5 py-2.5 text-sm font-semibold text-white shadow-sm glow-border-orange hover:shadow-md transition">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
