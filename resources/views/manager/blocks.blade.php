<x-app-layout title="Dormitory Blocks Management">
    <div class="space-y-6" x-data="{
        editModalOpen: false,
        editBlock: {
            id: null,
            name: '',
            block_code: '',
            gender_category: 'male',
            location: '',
            description: ''
        },
        openEdit(block) {
            this.editBlock = {
                id: block.id,
                name: block.name,
                block_code: block.block_code,
                gender_category: block.gender_category,
                location: block.location || '',
                description: block.description || ''
            };
            this.editModalOpen = true;
        }
    }">
        <!-- Register Block Form Console -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl p-6 shadow-xl shadow-black/25">
            <div class="flex items-center gap-2 mb-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Campus Infrastructure</span>
            </div>
            <h2 class="text-xl font-bold text-white tracking-tight mb-1">Create Dormitory Block & Assign Category</h2>
            <p class="text-xs text-slate-400 mb-5">Each block is designed for 50 rooms (up to 300 resident students, max 6 per room). Set category to Male or Female.</p>

            <form method="POST" action="{{ route('manager.blocks.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                @csrf
                <div>
                    <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Hall / Block Name</label>
                    <input type="text" name="name" required placeholder="e.g. Tewodros Hall" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 transition-all" />
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Block Code</label>
                    <input type="text" name="block_code" required placeholder="e.g. BLK-49" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 transition-all font-mono" />
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Accommodation Category</label>
                    <select name="gender_category" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white transition-all">
                        <option value="male">Male Students (Male Block)</option>
                        <option value="female">Female Students (Female Block)</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full py-2.5 px-4 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Register Block</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Search and Filter Bar -->
        <div class="relative z-20 bg-[#111726]/70 border border-white/[0.06] rounded-2xl p-3 flex flex-col sm:flex-row items-center gap-3">
            <form method="GET" action="{{ route('manager.blocks.index') }}" class="flex flex-col sm:flex-row items-center gap-3 w-full">
                <div class="relative flex-1 w-full">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by block code or name..."
                           class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl pl-9 pr-3.5 py-2 text-xs text-white placeholder-slate-500 transition-all" />
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <select name="gender" class="bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3 py-2 text-xs text-white transition-all">
                        <option value="">All Categories</option>
                        <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male Blocks Only</option>
                        <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female Blocks Only</option>
                    </select>

                    <button type="submit" class="px-4 py-2 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                        Filter
                    </button>

                    <x-block-sort-dropdown />

                    @if(request()->hasAny(['search', 'gender', 'sort']))
                        <a href="{{ route('manager.blocks.index') }}" class="px-3.5 py-2 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.03] hover:bg-white/[0.06] border border-white/10 rounded-xl transition-all">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Managed Blocks Table with Edit and Delete Buttons -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl overflow-hidden shadow-xl shadow-black/25">
            <div class="p-5 border-b border-white/[0.06] flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-white tracking-tight">Active Blocks & Accommodation Capacity</h3>
                    <p class="text-xs text-slate-400">Standard: 50 rooms &bull; 300 students design capacity per block</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-[#0F1420]/80 text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5">
                                <div class="inline-flex items-center">
                                    <span>Block Code</span>
                                    <x-sort-dropdown column="block_code" label="Block Code" asc-label="Ascending (BLK-01 → BLK-50)" desc-label="Descending (BLK-50 → BLK-01)" />
                                </div>
                            </th>
                            <th class="px-5 py-3.5">
                                <div class="inline-flex items-center">
                                    <span>Name</span>
                                    <x-sort-dropdown column="name" label="Block Name" asc-label="Ascending (A → Z)" desc-label="Descending (Z → A)" />
                                </div>
                            </th>
                            <th class="px-5 py-3.5">Category</th>
                            <th class="px-5 py-3.5">Rooms Configured</th>
                            <th class="px-5 py-3.5">Occupancy / 300 Cap</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($blocks as $b)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $b->image_url }}" alt="{{ $b->block_code }}" class="w-12 h-10 rounded-lg object-cover border border-white/10" />
                                        <span class="font-bold text-emerald-400 font-mono">{{ $b->block_code }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="font-semibold text-white block">{{ $b->name }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $b->location ?? 'Campus Zone' }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase tracking-wider {{ $b->gender_category === 'male' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                        {{ $b->gender_category }} Block
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 font-medium text-slate-300">
                                    <span>{{ $b->rooms_count }} / 50 Rooms</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-white">{{ $b->active_allocations_count }} / 300</span>
                                        <div class="w-16 bg-white/[0.06] rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-emerald-400 h-1.5 rounded-full" style="width: {{ min(100, $b->standard_occupancy_rate) }}%"></div>
                                        </div>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full font-medium text-emerald-400 bg-emerald-500/10 border border-emerald-500/20">
                                            {{ $b->standard_occupancy_rate }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Edit Block Button -->
                                        <button type="button" @click="openEdit({{ Js::from($b) }})" title="Edit Block" class="p-1.5 text-slate-400 hover:text-white hover:bg-white/[0.06] rounded-lg border border-white/10 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        <!-- Delete Block Button -->
                                        <form method="POST" action="{{ route('manager.blocks.destroy', $b) }}" onsubmit="return confirm('Permanently delete {{ $b->block_code }} ({{ addslashes($b->name) }})?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete Block" class="p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-lg border border-rose-500/20 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-400">No dormitory blocks registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: Edit Block (Proctor Manager)        -->
        <!-- ========================================== -->
        <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-sm" @click="editModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-[#121927] border border-white/10 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-white/[0.06]">
                        <div>
                            <h3 class="text-base font-semibold text-white tracking-tight">Edit Dormitory Block</h3>
                            <p class="text-xs text-slate-400" x-text="'Updating ' + editBlock.block_code + ' - ' + editBlock.name"></p>
                        </div>
                        <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form :action="'/proctor-manager/blocks/' + editBlock.id" method="POST" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Block Name</label>
                                <input type="text" name="name" x-model="editBlock.name" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Block Code</label>
                                <input type="text" name="block_code" x-model="editBlock.block_code" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Accommodation Category</label>
                                <select name="gender_category" x-model="editBlock.gender_category" required class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white">
                                    <option value="male">Male Students Only</option>
                                    <option value="female">Female Students Only</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Campus Location</label>
                                <input type="text" name="location" x-model="editBlock.location" placeholder="e.g. North Wing" class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-medium text-slate-300 mb-1.5">Description / Notes</label>
                            <textarea name="description" x-model="editBlock.description" rows="2" placeholder="Block description..." class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/[0.06]">
                            <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
