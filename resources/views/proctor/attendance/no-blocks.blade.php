<x-app-layout title="Curfew Attendance">
    <div class="max-w-2xl mx-auto py-8 px-4 text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 shadow-xl">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold tracking-tight text-white">No Block Assigned Yet</h2>
        <p class="text-sm text-slate-400 mt-2 max-w-md mx-auto leading-relaxed">
            You are currently not assigned to any dormitory hall. Please contact the Proctor Manager or Administrator to assign you a dormitory block so you can perform nightly curfew rounds.
        </p>
        <div class="mt-6">
            <a href="{{ route('proctor.dashboard') }}" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold px-5 py-2.5 rounded-xl text-sm shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all inline-flex items-center gap-2">
                <span>Return to Dashboard</span>
            </a>
        </div>
    </div>
</x-app-layout>
