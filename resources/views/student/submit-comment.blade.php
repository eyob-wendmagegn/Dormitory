<x-app-layout title="Submit Feedback">
    <div class="space-y-5 max-w-5xl w-full">
        <!-- Header -->
        <div class="p-6 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/25">
            <span class="text-xs uppercase font-semibold tracking-wider text-slate-400 mb-1 block">Help Desk & Maintenance</span>
            <h2 class="text-2xl font-bold tracking-tight text-white">Submit Feedback or Maintenance Request</h2>
            <p class="text-sm text-slate-400 mt-1 font-normal leading-relaxed">Send requests, inquiries, or maintenance notices directly to dormitory management and proctors.</p>
        </div>

        <!-- Form Card -->
        <div class="p-6 sm:p-8 rounded-3xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-xl shadow-black/20">
            <form action="{{ route('student.comments.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Category -->
                <div class="space-y-2">
                    <label for="category" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                        Request Category <span class="text-emerald-400">*</span>
                    </label>
                    <select name="category" id="category" required
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#0F1420] border border-white/[0.08] text-white text-xs focus:outline-none focus:border-emerald-500/50 transition-all">
                        <option value="">-- Select Category --</option>
                        <option value="maintenance" {{ old('category') === 'maintenance' ? 'selected' : '' }}>Room Maintenance (Plumbing, Electrical, Bed fixture)</option>
                        <option value="complaint" {{ old('category') === 'complaint' ? 'selected' : '' }}>Living Condition Complaint / Noise Disturbance</option>
                        <option value="inquiry" {{ old('category') === 'inquiry' ? 'selected' : '' }}>Housing Inquiry / General Question</option>
                        <option value="suggestion" {{ old('category') === 'suggestion' ? 'selected' : '' }}>Dormitory Suggestion / Improvement</option>
                        <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>Other General Request</option>
                    </select>
                    @error('category')
                        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Title -->
                <div class="space-y-2">
                    <label for="title" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                        Subject / Brief Title <span class="text-emerald-400">*</span>
                    </label>
                    <input type="text" name="title" id="title" required value="{{ old('title') }}"
                           placeholder="e.g. Leaking faucet in bathroom, Light fixture replacement"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-[#0F1420] border border-white/[0.08] text-white text-xs placeholder-slate-500 focus:outline-none focus:border-emerald-500/50 transition-all" />
                    @error('title')
                        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Message -->
                <div class="space-y-2">
                    <label for="message" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                        Detailed Description <span class="text-emerald-400">*</span>
                    </label>
                    <textarea name="message" id="message" rows="5" required
                              placeholder="Please describe the issue in detail, including room number, specific location, and how long the problem has persisted..."
                              class="w-full px-3.5 py-2.5 rounded-xl bg-[#0F1420] border border-white/[0.08] text-white text-xs placeholder-slate-500 focus:outline-none focus:border-emerald-500/50 transition-all">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between pt-5 border-t border-white/[0.06]">
                    <a href="{{ route('student.comments.index') }}" class="text-xs font-medium text-slate-400 hover:text-white transition-colors">
                        &larr; View submitted requests
                    </a>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('student.dashboard') }}" class="px-4 py-2.5 rounded-xl text-xs font-medium bg-white/[0.05] hover:bg-white/[0.09] text-slate-300 hover:text-white border border-white/10 transition-all">
                            Cancel
                        </a>
                        <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-5 py-2.5 rounded-xl text-xs shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            <span>Submit Feedback</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
