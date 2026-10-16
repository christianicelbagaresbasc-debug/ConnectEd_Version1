@props([
    'category' => 'student_resume',
    'documentableType' => null,
    'documentableId' => null,
    'ownerUserId' => null,
    'maxSizeMb' => 10,
    'accept' => '.pdf,.docx,.png,.jpg,.jpeg',
    'multiple' => false,
    'label' => 'Drag and drop files here, or click to browse',
    'hint' => 'Allowed formats: PDF, DOCX, PNG, JPG (max 10MB)',
])

<div 
    x-data="dropzoneComponent({
        category: '{{ $category }}',
        documentableType: {{ $documentableType ? "'{$documentableType}'" : 'null' }},
        documentableId: {{ $documentableId ? (int) $documentableId : 'null' }},
        ownerUserId: {{ $ownerUserId ? (int) $ownerUserId : 'null' }},
        maxSizeMb: {{ (int) $maxSizeMb }},
        slotsUrl: '{{ route('documents.slots') }}',
        confirmUrlTemplate: '{{ url('/documents/uploads') }}/BATCH_ID/confirm',
        csrfToken: '{{ csrf_token() }}'
    })"
    class="w-full"
>
    <!-- Dropzone Area -->
    <div 
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="handleDrop($event)"
        @click="$refs.fileInput.click()"
        :class="isDragging ? 'border-primary bg-primary/5 ring-2 ring-primary/20' : 'border-border bg-surface hover:border-primary/50 hover:bg-surface-2'"
        class="relative border-2 border-dashed rounded-card p-6 text-center cursor-pointer transition-all duration-200 group"
    >
        <input 
            type="file" 
            x-ref="fileInput" 
            @change="handleFiles($event.target.files)" 
            accept="{{ $accept }}" 
            {{ $multiple ? 'multiple' : '' }} 
            class="hidden" 
        />

        <div class="flex flex-col items-center justify-center space-y-3">
            <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                </svg>
            </div>

            <div class="text-sm font-medium text-text">
                <span class="text-primary font-semibold underline underline-offset-2">{{ $label }}</span>
            </div>

            <p class="text-xs text-text-muted">
                {{ $hint }}
            </p>
        </div>

        <!-- Upload Progress Overlay -->
        <div x-show="isUploading" class="mt-4 pt-4 border-t border-border" style="display: none;">
            <div class="flex items-center justify-between text-xs font-semibold mb-1">
                <span x-text="statusMessage" class="text-text"></span>
                <span x-text="progress + '%'" class="text-primary font-bold"></span>
            </div>
            <div class="w-full bg-surface-2 rounded-full h-2 overflow-hidden border border-border">
                <div 
                    class="bg-primary h-2 transition-all duration-300 rounded-full" 
                    :style="'width: ' + progress + '%'"
                ></div>
            </div>
        </div>
    </div>

    <!-- Error Message Alert -->
    <div x-show="errorMessage" x-transition class="mt-3 p-3 bg-danger/10 border border-danger/30 rounded-lg flex items-start gap-2 text-xs text-danger" style="display: none;">
        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
        </svg>
        <span x-text="errorMessage"></span>
    </div>

    <!-- Uploaded Files List -->
    <div x-show="uploadedFiles.length > 0" class="mt-4 space-y-2" style="display: none;">
        <div class="text-xs font-semibold uppercase tracking-wider text-text-muted">Uploaded Files</div>
        <template x-for="(file, index) in uploadedFiles" :key="index">
            <div class="flex items-center justify-between p-3 bg-surface border border-border rounded-lg text-xs">
                <div class="flex items-center gap-2 truncate">
                    <svg class="w-4 h-4 text-primary flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <span class="font-medium text-text truncate" x-text="file.name"></span>
                    <span class="text-text-muted" x-text="'(' + file.sizeFormatted + ')'"></span>
                </div>
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 px-2 py-0.5 rounded">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    Verified
                </span>
            </div>
        </template>
    </div>
</div>

<script>
    function dropzoneComponent(config) {
        return {
            isDragging: false,
            isUploading: false,
            progress: 0,
            statusMessage: '',
            errorMessage: null,
            uploadedFiles: [],

            handleDrop(event) {
                this.isDragging = false;
                if (event.dataTransfer && event.dataTransfer.files) {
                    this.handleFiles(event.dataTransfer.files);
                }
            },

            async handleFiles(fileList) {
                this.errorMessage = null;
                const files = Array.from(fileList);
                if (files.length === 0) return;

                // Validate size
                for (const file of files) {
                    if (file.size > config.maxSizeMb * 1024 * 1024) {
                        this.errorMessage = `File "${file.name}" exceeds the maximum limit of ${config.maxSizeMb}MB.`;
                        return;
                    }
                }

                try {
                    this.isUploading = true;
                    this.progress = 10;
                    this.statusMessage = 'Requesting secure upload slot...';

                    // 1. Request Slots
                    const slotPayload = {
                        files: files.map(f => ({
                            name: f.name,
                            size: f.size,
                            mime: f.type || 'application/octet-stream',
                            category: config.category,
                            documentable_type: config.documentableType,
                            documentable_id: config.documentableId,
                            owner_user_id: config.ownerUserId
                        }))
                    };

                    const slotRes = await fetch(config.slotsUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': config.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(slotPayload)
                    });

                    if (!slotRes.ok) {
                        const err = await slotRes.json();
                        throw new Error(err.message || 'Failed to obtain upload authorization.');
                    }

                    const { batch_id, slots } = await slotRes.json();

                    // 2. Upload each file directly to presigned URL
                    for (let i = 0; i < slots.length; i++) {
                        const slot = slots[i];
                        const file = files[i];

                        this.progress = 30 + Math.round((i / slots.length) * 40);
                        this.statusMessage = `Uploading ${file.name}...`;

                        const uploadRes = await fetch(slot.upload_url, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': file.type || 'application/octet-stream'
                            },
                            body: file
                        });

                        if (!uploadRes.ok) {
                            throw new Error(`Direct upload failed for ${file.name}.`);
                        }
                    }

                    // 3. Confirm Batch
                    this.progress = 85;
                    this.statusMessage = 'Verifying file integrity & magic bytes...';

                    const confirmUrl = config.confirmUrlTemplate.replace('BATCH_ID', batch_id);
                    const confirmRes = await fetch(confirmUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': config.csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    if (!confirmRes.ok) {
                        const err = await confirmRes.json();
                        throw new Error(err.message || 'File integrity check failed.');
                    }

                    const confirmData = await confirmRes.json();

                    this.progress = 100;
                    this.statusMessage = 'Completed!';

                    for (const f of files) {
                        this.uploadedFiles.push({
                            name: f.name,
                            sizeFormatted: (f.size / 1024 / 1024).toFixed(2) + ' MB'
                        });
                    }

                    this.$dispatch('documents-uploaded', { batch_id, files: confirmData.files });

                    setTimeout(() => {
                        this.isUploading = false;
                        this.progress = 0;
                    }, 1200);

                } catch (err) {
                    this.isUploading = false;
                    this.errorMessage = err.message || 'Upload error encountered.';
                }
            }
        };
    }
</script>
