<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Team Members</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage the public-facing members of your organization displayed on the landing page.</div>
        </div>
    </x-slot>

    <div class="space-y-4" x-data="{
        addOpen: false,
        manageOpen: false,
        delOpen: false,
        delName: '',
        delAction: '',
        manageUpdateUrl: '',
        manageName: '',
        manageRole: '',
        manageDescription: '',
        photoPreview: null,
        addPhotoPreview: null,
        openManage(m) {
            this.manageUpdateUrl = @js(url('/admin/team-members')).replace(/\/$/, '') + '/' + m.id;
            this.manageName = m.name || '';
            this.manageRole = m.role || '';
            this.manageDescription = m.description || '';
            this.photoPreview = m.image_path ? '/storage/' + m.image_path : null;
            this.manageOpen = true;
        },
        openDelete(name, url) {
            this.delName = name;
            this.delAction = url;
            this.delOpen = true;
        },
        onAddPhotoChange(e) {
            if (this.addPhotoPreview) URL.revokeObjectURL(this.addPhotoPreview);
            const f = e.target.files && e.target.files[0];
            this.addPhotoPreview = f ? URL.createObjectURL(f) : null;
        },
        onManagePhotoChange(e) {
            const f = e.target.files && e.target.files[0];
            this.photoPreview = f ? URL.createObjectURL(f) : null;
        }
    }">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-100">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex items-center justify-end">
            <button type="button" @click="addOpen = true" class="inline-flex items-center rounded-2xl bg-gradient-to-r from-[#FF7A1A] to-[#FFB24D] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-95">
                Add Member
            </button>
        </div>

        {{-- Add member modal --}}
        <div x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" @click="addOpen = false"></div>
            <div class="relative w-full max-w-lg rounded-2xl border border-white/10 bg-white dark:bg-gray-900 shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200/60 dark:border-white/10 flex justify-between shrink-0">
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Add Team Member</div>
                    <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="addOpen = false">Close</button>
                </div>
                <form method="POST" action="{{ route('admin.team_members.store') }}" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto">
                    @csrf
                    <div>
                        <x-input-label for="add_name" value="Name" />
                        <x-text-input id="add_name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required maxlength="150" />
                    </div>
                    <div>
                        <x-input-label for="add_role" value="Role" />
                        <x-text-input id="add_role" name="role" type="text" class="mt-1 block w-full" :value="old('role')" required maxlength="100" />
                    </div>
                    <div>
                        <x-input-label for="add_description" value="Description" />
                        <textarea id="add_description" name="description" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" rows="3" maxlength="1000">{{ old('description') }}</textarea>
                    </div>
                    <div>
                        <x-input-label for="add_photo" value="Photo (optional)" />
                        <input id="add_photo" name="photo" type="file" accept="image/*" class="mt-1 block w-full text-sm" @change="onAddPhotoChange($event)" />
                        <div class="mt-2 relative aspect-video w-full max-w-xs rounded-2xl border border-dashed border-gray-300 dark:border-white/15 bg-gray-50 dark:bg-white/5 overflow-hidden">
                            <span x-show="!addPhotoPreview" class="absolute inset-0 flex items-center justify-center text-xs text-gray-500 dark:text-gray-400">No preview</span>
                            <img x-show="addPhotoPreview" x-bind:src="addPhotoPreview" alt="" class="h-full w-full object-cover" />
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="addOpen = false">Cancel</button>
                        <x-primary-button>Create</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Manage member modal --}}
        <div x-show="manageOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" @click="manageOpen = false"></div>
            <div class="relative w-full max-w-lg rounded-2xl border border-white/10 bg-white dark:bg-gray-900 shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200/60 dark:border-white/10 flex justify-between shrink-0">
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Edit Team Member</div>
                    <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="manageOpen = false">Close</button>
                </div>
                <form method="POST" :action="manageUpdateUrl" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto">
                    @csrf
                    @method('PATCH')
                    <div>
                        <x-input-label value="Name" />
                        <x-text-input name="name" type="text" x-model="manageName" class="mt-1 block w-full" required maxlength="150" />
                    </div>
                    <div>
                        <x-input-label value="Role" />
                        <x-text-input name="role" type="text" x-model="manageRole" class="mt-1 block w-full" required maxlength="100" />
                    </div>
                    <div>
                        <x-input-label value="Description" />
                        <textarea name="description" x-model="manageDescription" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" rows="3" maxlength="1000"></textarea>
                    </div>
                    <div>
                        <x-input-label value="Replace Photo (optional)" />
                        <input name="photo" type="file" accept="image/*" class="mt-1 block w-full text-sm" @change="onManagePhotoChange($event)" />
                        <div class="mt-2 relative aspect-video w-full max-w-xs rounded-2xl border border-dashed border-gray-300 dark:border-white/15 bg-gray-50 dark:bg-white/5 overflow-hidden">
                            <span x-show="!photoPreview" class="absolute inset-0 flex items-center justify-center text-xs text-gray-500 dark:text-gray-400">No preview</span>
                            <img x-show="photoPreview" x-bind:src="photoPreview" alt="" class="h-full w-full object-cover" />
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="manageOpen = false">Cancel</button>
                        <x-primary-button>Save</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Delete modal --}}
        <div x-show="delOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" @click="delOpen = false"></div>
            <div class="relative w-full max-w-md rounded-2xl border border-white/10 bg-white dark:bg-gray-900 shadow-xl p-6">
                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Delete team member?</div>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Remove <span class="font-semibold" x-text="delName"></span> from the team display. This cannot be undone.</p>
                <form :action="delAction" method="POST" class="mt-6 flex justify-end gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="delOpen = false">Cancel</button>
                    <button type="submit" class="inline-flex items-center rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Delete</button>
                </form>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                    <thead class="bg-gray-50/70 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Member</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Description</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($members as $m)
                            @php
                                $photo = $m->image_path ? '/storage/'.$m->image_path : null;
                                $payload = [
                                    'id' => $m->id,
                                    'name' => $m->name,
                                    'role' => $m->role,
                                    'description' => $m->description,
                                    'image_path' => $m->image_path,
                                ];
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 shrink-0 rounded-full bg-gray-100 dark:bg-white/10 bg-cover bg-center ring-1 ring-black/5 dark:ring-white/10" @if($photo) style="background-image: url('{{ $photo }}')" @endif></div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $m->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $m->role }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate" title="{{ $m->description }}">
                                    {{ $m->description ?: '—' }}
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <button type="button" class="text-sm font-semibold text-[#FF7A1A] hover:underline" @click="openManage(@js($payload))">Edit</button>
                                    <button type="button" class="ml-3 text-red-600 dark:text-red-400 hover:underline" title="Delete" @click="openDelete(@js($m->name), @js(route('admin.team_members.destroy', $m)))">
                                        <svg class="inline h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14zM10 11v6M14 11v6"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-600 dark:text-gray-400">No team members added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200/60 dark:border-white/10">
                {{ $members->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
