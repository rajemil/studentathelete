@props(['existingUrl' => null])

<div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-4 space-y-4">
    <div class="flex items-center gap-4">
        <div class="h-20 w-20 shrink-0 rounded-2xl bg-gray-100 dark:bg-white/5 overflow-hidden flex items-center justify-center">
            <template x-if="photoUrl">
                <img :src="photoUrl" alt="" class="h-full w-full object-cover" />
            </template>
            <template x-if="!photoUrl">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">PHOTO</span>
            </template>
        </div>
        <div class="min-w-0">
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Profile photo</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">PNG or JPG, up to 5MB. Optional.</div>
        </div>
    </div>
    <input
        name="photo"
        type="file"
        accept="image/*"
        @change="const f = $event.target.files?.[0]; photoUrl = f ? URL.createObjectURL(f) : null"
        class="block w-full text-sm text-gray-700 dark:text-gray-200 file:mr-3 file:rounded-xl file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:opacity-90 dark:file:bg-white/10 dark:file:text-gray-100"
    />
    <x-input-error :messages="$errors->get('photo')" class="mt-1" />
</div>
