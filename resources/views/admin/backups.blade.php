<x-app-layout title="Database Backup & Restore">
    <div class="space-y-5" x-data="{
        restoreModalOpen: false,
        restoreMode: 'history',
        targetName: '',
        targetAction: '',
        modalPassword: '',
        isSubmitting: false,
        selectedUploadName: '',
        selectedUploadError: '',
        openHistoryRestore(actionUrl, fileName) {
            this.restoreMode = 'history';
            this.targetAction = actionUrl;
            this.targetName = fileName;
            this.modalPassword = '';
            this.selectedUploadError = '';
            this.restoreModalOpen = true;
            this.$nextTick(() => {
                if (this.$refs.modalPasswordInput) {
                    this.$refs.modalPasswordInput.focus();
                }
            });
        },
        openUploadRestore() {
            const fileInput = this.$refs.uploadBackupInput;
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                this.selectedUploadError = 'Please select a .sql.gz or .sql backup file first.';
                return;
            }
            const name = fileInput.files[0].name;
            if (!name.toLowerCase().endsWith('.sql.gz') && !name.toLowerCase().endsWith('.sql')) {
                this.selectedUploadError = 'Invalid file format. Only .sql.gz or .sql backup files are allowed.';
                return;
            }
            this.selectedUploadError = '';
            this.restoreMode = 'upload';
            this.targetName = name;
            this.modalPassword = '';
            this.restoreModalOpen = true;
            this.$nextTick(() => {
                if (this.$refs.modalPasswordInput) {
                    this.$refs.modalPasswordInput.focus();
                }
            });
        },
        submitConfirmedRestore() {
            if (!this.modalPassword) {
                return;
            }
            this.isSubmitting = true;
            if (this.restoreMode === 'history') {
                this.$refs.historyRestoreForm.submit();
            } else {
                this.$refs.uploadRestoreForm.submit();
            }
        }
    }">

        <!-- 1. TOP CONSOLE BANNER WITH "BACKUP NOW" ACTION -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl p-6 sm:p-7 shadow-xl shadow-black/25 flex flex-col lg:flex-row lg:items-center justify-between gap-5 relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-52 h-52 bg-emerald-500/[0.04] rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">System Maintenance &bull; Disaster Recovery</span>
                </div>
                <h2 class="text-2xl font-bold text-white tracking-tight">PostgreSQL Database Backup & Restore</h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-2xl leading-relaxed">
                    Compressed <code class="px-1.5 py-0.5 rounded bg-white/[0.06] text-emerald-400 font-mono text-[11px]">.sql.gz</code> snapshots synced to Google Drive folder
                    <span class="text-white font-semibold">{{ $diagnostics['drive']['folder_name'] }}</span>. Nightly schedule runs at <span class="text-cyan-400 font-semibold font-mono">02:00 AM</span> with a <span class="text-white font-semibold">{{ $diagnostics['retention_days'] }}-day</span> cloud retention policy.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 relative z-10">
                <form method="POST" action="{{ route('admin.backups.store') }}" onsubmit="this.querySelector('button').disabled = true;">
                    @csrf
                    <button type="submit" class="px-5 py-3 text-xs sm:text-sm font-semibold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-lg shadow-emerald-500/20 transition-all active:scale-[0.98] flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>Backup Now</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- 2. WARNING / CONFIGURATION & ERROR BANNERS -->
        @if ($errors->any())
            <div class="p-4 sm:p-5 rounded-2xl bg-rose-500/10 border border-rose-500/25 text-xs text-rose-300 shadow-lg backdrop-blur-md">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div class="space-y-1">
                        <p class="font-semibold text-rose-200">Action Required</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @if ($latestFailure)
            <div class="p-4 sm:p-5 rounded-2xl bg-rose-500/10 border border-rose-500/25 text-xs text-rose-300 shadow-lg backdrop-blur-md">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-rose-500/15 border border-rose-500/30 flex items-center justify-center text-rose-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="text-sm font-semibold text-rose-200">Latest Backup / Restore Operation Failed</h3>
                            <span class="text-[11px] font-mono text-rose-300/80">{{ $latestFailure->created_at->format('M d, Y H:i:s') }}</span>
                        </div>
                        <p class="mt-1 text-rose-300 leading-relaxed break-words">
                            <span class="font-semibold">[{{ $latestFailure->trigger_type }}]</span> {{ $latestFailure->file_name }}: {{ $latestFailure->error_message }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @php
            $driveAuthReady = ($diagnostics['drive']['oauth_configured'] ?? false) || ($diagnostics['drive']['credentials_valid'] ?? false);
        @endphp

        @if (! $driveAuthReady || ! $diagnostics['pg_dump_ready'] || ! $diagnostics['psql_ready'] || ! empty($diagnostics['drive']['issues']))
            <div class="p-4 sm:p-5 rounded-2xl bg-amber-500/10 border border-amber-500/25 text-xs text-amber-300 shadow-lg backdrop-blur-md">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0 space-y-1.5">
                        <h3 class="text-sm font-semibold text-amber-200">Environment & Google Drive Configuration Checklist</h3>
                        <ul class="list-disc list-inside space-y-1 text-amber-300/90">
                            @if (! $diagnostics['pg_dump_ready'])
                                <li><span class="font-semibold">pg_dump</span> binary not found at <code class="font-mono">{{ $diagnostics['pg_dump_path'] }}</code>. Verify <code class="font-mono">PG_DUMP_PATH</code> in <code class="font-mono">.env</code>.</li>
                            @endif
                            @if (! $diagnostics['psql_ready'])
                                <li><span class="font-semibold">psql</span> binary not found at <code class="font-mono">{{ $diagnostics['psql_path'] }}</code>. Verify <code class="font-mono">PSQL_PATH</code> in <code class="font-mono">.env</code>.</li>
                            @endif
                            @foreach ($diagnostics['drive']['issues'] as $issue)
                                <li>{{ $issue }}</li>
                            @endforeach
                        </ul>
                        @if ($diagnostics['drive']['service_account_email'])
                            <p class="text-[11px] text-amber-200/90 pt-1">
                                Service Account Email: <code class="px-1.5 py-0.5 rounded bg-black/20 font-mono select-all">{{ $diagnostics['drive']['service_account_email'] }}</code> — ensure your Google Drive folder <span class="font-semibold">{{ $diagnostics['drive']['folder_name'] }}</span> is shared with this email (Editor permission).
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- 3. KPI SUMMARY STRIP -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-lg">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Last Successful Backup</span>
                @if ($latestSuccess)
                    <div class="text-sm font-bold text-white truncate" title="{{ $latestSuccess->file_name }}">{{ $latestSuccess->file_name }}</div>
                    <div class="text-[11px] text-emerald-400 font-medium mt-1 font-mono">{{ $latestSuccess->formatted_size }} &bull; {{ $latestSuccess->created_at->diffForHumans() }}</div>
                @else
                    <div class="text-sm font-bold text-slate-300">No backups yet</div>
                    <div class="text-[11px] text-slate-400 mt-1">Click "Backup Now" to create first snapshot</div>
                @endif
            </div>

            <div class="p-5 rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-lg">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Google Drive Target</span>
                <div class="text-sm font-bold text-white flex items-center gap-2">
                    <span>{{ $diagnostics['drive']['folder_name'] }}</span>
                    @if ($diagnostics['drive']['oauth_configured'] ?? false)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">OAuth2 Connected</span>
                    @elseif ($diagnostics['drive']['credentials_valid'] ?? false)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Key Loaded</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">Setup Needed</span>
                    @endif
                </div>
                <div class="text-[11px] text-slate-400 font-mono mt-1 truncate">
                    Folder ID: {{ $diagnostics['drive']['folder_id'] ?: 'Auto-lookup by folder name' }}
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-lg">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Scheduler & Retention</span>
                <div class="text-sm font-bold text-cyan-400 font-mono">Daily @ 02:00 AM</div>
                <div class="text-[11px] text-slate-400 mt-1">Auto-prunes Drive archives &gt; {{ $diagnostics['retention_days'] }} days</div>
            </div>

            <div class="p-5 rounded-2xl bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] shadow-lg">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Backup Audit Log</span>
                <div class="text-sm font-bold text-white font-mono">
                    <span class="text-emerald-400">{{ $stats['success'] }} Success</span>
                    <span class="text-slate-500 mx-1">/</span>
                    <span class="text-rose-400">{{ $stats['failed'] }} Failed</span>
                </div>
                <div class="text-[11px] text-slate-400 mt-1">Target DB: <span class="font-mono text-slate-300">{{ $diagnostics['database'] ?: $diagnostics['connection'] }}</span></div>
            </div>
        </div>

        <!-- 4. UPLOAD & RESTORE CARD -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl p-6 shadow-xl shadow-black/25">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-white/[0.06]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4-4m0 0L8 8m4-4v12"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-white tracking-tight">Manual Upload & Restore</h3>
                        <p class="text-xs text-slate-400">Upload a local <code class="font-mono text-emerald-400">.sql.gz</code> or <code class="font-mono text-emerald-400">.sql</code> PostgreSQL dump. A Pre-Restore Safety backup is automatically taken before restoring.</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-amber-500/10 text-amber-400 border border-amber-500/20 self-start md:self-center">
                    Admin Password Required
                </span>
            </div>

            <form x-ref="uploadRestoreForm" method="POST" action="{{ route('admin.backups.upload_restore') }}" enctype="multipart/form-data" class="mt-5 grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
                @csrf
                <input type="hidden" name="password" x-model="modalPassword" />

                <div class="lg:col-span-8">
                    <label for="backup_file" class="block text-[11px] uppercase font-semibold text-slate-400 tracking-wider mb-1.5">
                        Select Backup Archive (.sql.gz or .sql)
                    </label>
                    <input
                        x-ref="uploadBackupInput"
                        id="backup_file"
                        type="file"
                        name="backup_file"
                        accept=".sql,.sql.gz,.gz"
                        @change="selectedUploadName = $event.target.files[0]?.name || ''; selectedUploadError = '';"
                        class="w-full bg-[#0F1420] border border-white/[0.08] focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-white/[0.06] file:text-white hover:file:bg-white/[0.1] transition-all cursor-pointer"
                    />
                    <p x-show="selectedUploadError" x-text="selectedUploadError" x-cloak class="text-[11px] text-rose-400 mt-1.5"></p>
                    @error('backup_file')
                        <p class="text-[11px] text-rose-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="lg:col-span-4 flex justify-end">
                    <button
                        type="button"
                        @click="openUploadRestore()"
                        class="w-full lg:w-auto px-5 py-3 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-500 rounded-xl shadow-lg shadow-rose-500/20 transition-all active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Upload & Restore Database</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- 5. BACKUP HISTORY TABLE -->
        <div class="bg-[#121927]/80 backdrop-blur-md border border-white/[0.07] rounded-3xl overflow-hidden shadow-xl shadow-black/25">
            <div class="p-5 sm:p-6 border-b border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-white tracking-tight">Backup History & Cloud Archives</h3>
                    <p class="text-xs text-slate-400 mt-0.5">All automated, manual, and pre-restore safety backups stored in Google Drive (<span class="font-mono">{{ $diagnostics['drive']['folder_name'] }}</span>).</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[11px] font-medium bg-white/[0.05] text-slate-300 border border-white/10 self-start sm:self-center font-mono">
                    {{ $backups->total() }} {{ \Illuminate\Support\Str::plural('Record', $backups->total()) }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-[#0F1420]/80 text-slate-400 uppercase text-[10px] font-semibold tracking-wider border-b border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5">Timestamp</th>
                            <th class="px-5 py-3.5">File Name & Size</th>
                            <th class="px-5 py-3.5">Trigger Type</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse ($backups as $backup)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <!-- Timestamp -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-white font-mono">{{ $backup->created_at->format('M d, Y H:i:s') }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $backup->created_at->diffForHumans() }}
                                        @if ($backup->creator)
                                            &bull; by {{ $backup->creator->name }}
                                        @endif
                                    </div>
                                </td>

                                <!-- File Name & Size -->
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-white font-mono">{{ $backup->file_name }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5 flex flex-wrap items-center gap-2">
                                        <span class="font-mono">{{ $backup->formatted_size }}</span>
                                        @if ($backup->restored_at)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                                                Restored {{ $backup->restored_at->diffForHumans() }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Trigger Type Badge -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @if ($backup->trigger_type === \App\Models\BackupLog::TRIGGER_AUTOMATIC)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                                            Automatic
                                        </span>
                                    @elseif ($backup->trigger_type === \App\Models\BackupLog::TRIGGER_PRE_RESTORE)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                            Pre-Restore Safety
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                            Manual
                                        </span>
                                    @endif
                                </td>

                                <!-- Status Badge -->
                                <td class="px-5 py-4 max-w-xs">
                                    @if ($backup->status === \App\Models\BackupLog::STATUS_SUCCESS)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            Success
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                            Failed
                                        </span>
                                        @if ($backup->error_message)
                                            <p class="text-[11px] text-rose-300/90 mt-1 line-clamp-2" title="{{ $backup->error_message }}">{{ $backup->error_message }}</p>
                                        @endif
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    @if ($backup->isAvailableOnDrive())
                                        <div class="inline-flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.backups.download', $backup) }}"
                                               class="px-3 py-1.5 text-[11px] font-semibold text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.1] border border-white/10 rounded-lg transition-all inline-flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                <span>Download</span>
                                            </a>
                                            <button type="button"
                                                    @click="openHistoryRestore('{{ route('admin.backups.restore', $backup) }}', @js($backup->file_name))"
                                                    class="px-3 py-1.5 text-[11px] font-semibold text-rose-300 hover:text-white bg-rose-500/10 hover:bg-rose-600 border border-rose-500/25 rounded-lg transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                <span>Restore</span>
                                            </button>
                                        </div>
                                    @elseif ($backup->isSuccessful())
                                        <span class="text-[11px] text-slate-500 italic">Pruned (&gt;{{ $diagnostics['retention_days'] }}d)</span>
                                    @else
                                        <span class="text-[11px] text-slate-500 italic">Unavailable</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                                        <p class="text-xs font-medium text-slate-300">No PostgreSQL backups have been logged yet.</p>
                                        <p class="text-[11px] text-slate-400">Click <span class="text-emerald-400 font-semibold">Backup Now</span> above or run <code class="font-mono text-slate-300">php artisan db:backup</code>.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($backups->hasPages())
                <div class="p-4 sm:p-5 border-t border-white/[0.06]">
                    {{ $backups->links() }}
                </div>
            @endif
        </div>

        <!-- Hidden Form for One-Click History Restore -->
        <form x-ref="historyRestoreForm" method="POST" :action="targetAction" class="hidden">
            @csrf
            <input type="hidden" name="password" x-model="modalPassword" />
        </form>

        <!-- 6. RESTORE CONFIRMATION + ADMIN PASSWORD MODAL -->
        <div x-show="restoreModalOpen"
             x-cloak
             @keydown.escape.window="if (!isSubmitting) restoreModalOpen = false"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4"
             style="display: none;">
            <div class="bg-[#121927] border border-white/[0.08] w-full max-w-lg rounded-3xl p-6 sm:p-7 shadow-2xl relative"
                 @click.away="if (!isSubmitting) restoreModalOpen = false">

                <div class="flex items-start justify-between pb-4 border-b border-white/[0.06]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-500/15 border border-rose-500/30 flex items-center justify-center text-rose-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white tracking-tight">Confirm PostgreSQL Database Restore</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Target Archive: <span class="font-mono text-rose-300 font-semibold" x-text="targetName"></span></p>
                        </div>
                    </div>
                    <button type="button" @click="if (!isSubmitting) restoreModalOpen = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>

                <div class="mt-4 space-y-4">
                    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-200 space-y-2 leading-relaxed">
                        <p class="font-semibold text-amber-300 uppercase text-[10px] tracking-wider">Safety Protections Engaged</p>
                        <ul class="list-disc list-inside space-y-1 text-amber-200/90">
                            <li>A <span class="font-semibold">Pre-Restore Safety</span> backup of the live database will be created and uploaded to Google Drive before overwriting.</li>
                            <li>The dormitory application will briefly enter <span class="font-semibold">Maintenance Mode</span> during <code class="font-mono">psql</code> execution and come back online automatically.</li>
                            <li>All tables (<code class="font-mono">users</code>, allocations, attendance, leaves, assets) will be restored to this archive state.</li>
                        </ul>
                    </div>

                    <div>
                        <label for="restore_admin_password" class="block text-[11px] uppercase font-semibold text-slate-300 tracking-wider mb-1.5">
                            Re-enter System Admin Password <span class="text-rose-400">*</span>
                        </label>
                        <input
                            x-ref="modalPasswordInput"
                            id="restore_admin_password"
                            type="password"
                            x-model="modalPassword"
                            @keydown.enter.prevent="submitConfirmedRestore()"
                            required
                            autocomplete="current-password"
                            placeholder="Enter your Administrator password to authorize"
                            class="w-full bg-[#0F1420] border border-white/[0.1] focus:border-rose-500 focus:ring-1 focus:ring-rose-500 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 transition-all"
                        />
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-white/[0.06] flex items-center justify-end gap-3">
                    <button type="button"
                            :disabled="isSubmitting"
                            @click="restoreModalOpen = false"
                            class="px-4 py-2.5 text-xs font-medium text-slate-300 hover:text-white bg-white/[0.05] hover:bg-white/[0.09] border border-white/10 rounded-xl transition-all disabled:opacity-50">
                        Cancel
                    </button>
                    <button type="button"
                            :disabled="!modalPassword || isSubmitting"
                            @click="submitConfirmedRestore()"
                            class="px-5 py-2.5 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-500 rounded-xl shadow-lg shadow-rose-500/20 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 cursor-pointer">
                        <span x-show="!isSubmitting">Confirm &amp; Restore Database</span>
                        <span x-show="isSubmitting" x-cloak>Backing Up &amp; Restoring...</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
