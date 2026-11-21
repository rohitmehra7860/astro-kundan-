<div x-data="{ search: '' }" class="p-6">
    <!-- Search Bar & Direct Image Uploader -->
    <div class="mb-5 flex flex-col sm:flex-row gap-3 items-stretch justify-between" x-data="{ 
        uploading: false, 
        progress: 0,
        errorMessage: '',
        triggerUpload() {
            this.$refs.fileInput.click();
        },
        handleUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.uploading = true;
            this.progress = 0;
            this.errorMessage = '';

            const formData = new FormData();
            formData.append('file', file);
            
            // Retrieve CSRF token
            const csrfToken = document.querySelector('meta[name=&quot;csrf-token&quot;]')?.getAttribute('content') 
                || document.querySelector('input[name=&quot;_token&quot;]')?.value;

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route('admin.media.upload-ajax') }}', true);
            if (csrfToken) {
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            }

            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable) {
                    this.progress = Math.round((e.loaded / e.total) * 100);
                }
            };

            xhr.onload = () => {
                this.uploading = false;
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        // Dynamically update Livewire state path on parent form
                        $wire.set('{{ $statePath }}', response.id);
                        
                        // Close the modal picker
                        close();
                    } else {
                        this.errorMessage = response.error || 'Upload failed';
                    }
                } else {
                    this.errorMessage = 'Upload failed with status ' + xhr.status;
                }
            };

            xhr.onerror = () => {
                this.uploading = false;
                this.errorMessage = 'Network error occurred';
            };

            xhr.send(formData);
        }
    }">
        <!-- Search Input -->
        <div class="relative flex-grow">
            <input 
                x-model="search" 
                type="text" 
                placeholder="Search images by title..." 
                class="gallery-search-input"
            />
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400 search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        <!-- Upload Trigger Button -->
        <div class="flex flex-col justify-center">
            <input type="file" x-ref="fileInput" class="hidden" accept="image/*" @change="handleUpload" />
            <button 
                type="button" 
                @click="triggerUpload"
                class="gallery-upload-btn"
                :disabled="uploading"
            >
                <template x-if="!uploading">
                    <span class="flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Upload Image
                    </span>
                </template>
                <template x-if="uploading">
                    <span class="flex items-center justify-center gap-1.5">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" style="width: 16px; height: 16px; animation: spin 1s linear infinite;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Uploading (<span x-text="progress + '%'"></span>)
                    </span>
                </template>
            </button>
        </div>

        <!-- Live Upload Error Notification -->
        <template x-if="errorMessage">
            <div class="w-full text-xs text-red-500 mt-2 px-1 font-semibold" x-text="errorMessage"></div>
        </template>
    </div>

    <!-- Media Library Grid -->
    @php
        $mediaItems = \App\Models\MediaUpload::with('media')->latest()->get();
    @endphp
    
    @if($mediaItems->isEmpty())
        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
            <svg class="mx-auto h-12 w-12 text-gray-455 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-sm">No media uploads found. Please upload images in the Media Library.</p>
        </div>
    @else
        <div class="gallery-grid overflow-y-auto max-h-[450px] pr-2">
            @foreach($mediaItems as $media)
                @php
                    $imgUrl = $media->getFirstMediaUrl('gallery', 'thumb');
                    $titleLower = strtolower($media->title);
                @endphp
                @if($imgUrl)
                    <div 
                        x-show="search === '' || {{ json_encode($titleLower) }}.includes(search.toLowerCase())"
                        class="gallery-card group"
                        x-on:click="$wire.set('{{ $statePath }}', {{ $media->id }}); close();"
                    >
                        <div class="gallery-image-wrapper">
                            <img src="{{ $imgUrl }}" />
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity duration-300">
                                <span class="text-white text-[10px] font-semibold bg-primary-600 px-2 py-1 rounded shadow-sm" style="background-color: rgb(234, 88, 12);">Select</span>
                            </div>
                        </div>
                        <div class="gallery-card-title" title="{{ $media->title }}">
                            {{ $media->title }}
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    <style>
        .gallery-search-input {
            width: 100% !important;
            padding: 10px 16px 10px 38px !important;
            border-radius: 8px !important;
            border: 1px solid rgba(156, 163, 175, 0.25) !important;
            background-color: rgba(156, 163, 175, 0.05) !important;
            font-size: 14px !important;
            line-height: 20px !important;
            color: inherit !important;
            outline: none !important;
            transition: all 0.2s ease !important;
        }
        .dark .gallery-search-input {
            border-color: rgba(255, 255, 255, 0.1) !important;
            background-color: rgba(0, 0, 0, 0.2) !important;
        }
        .gallery-search-input:focus {
            border-color: rgb(234, 88, 12) !important; /* Orange/Primary Focus border */
            box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.2) !important;
        }
        .search-icon-svg {
            position: absolute !important;
            top: 50% !important;
            left: 12px !important;
            transform: translateY(-50%) !important;
            color: #9ca3af !important;
        }
        
        /* Direct Uploader Button */
        .gallery-upload-btn {
            background-color: rgb(234, 88, 12) !important;
            color: #ffffff !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            padding: 10px 18px !important;
            border-radius: 8px !important;
            border: none !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            white-space: nowrap !important;
            height: 42px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .gallery-upload-btn:hover {
            background-color: rgb(249, 115, 22) !important;
            box-shadow: 0 4px 6px -1px rgba(234, 88, 12, 0.2) !important;
        }
        .gallery-upload-btn:disabled {
            opacity: 0.6 !important;
            cursor: not-allowed !important;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Grid & Card responsive overrides */
        .gallery-grid {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important; /* 3 columns on mobile */
            gap: 12px !important;
        }
        @media (min-width: 640px) {
            .gallery-grid {
                grid-template-columns: repeat(4, 1fr) !important; /* 4 columns on tablets */
            }
        }
        @media (min-width: 1024px) {
            .gallery-grid {
                grid-template-columns: repeat(6, 1fr) !important; /* 6 columns on desktops */
            }
        }

        .gallery-card {
            cursor: pointer !important;
            border: 1px solid rgba(156, 163, 175, 0.15) !important;
            border-radius: 12px !important;
            overflow: hidden !important;
            background-color: #ffffff !important;
            padding: 8px !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            transition: all 0.3s ease !important;
            height: 160px !important; /* Optimized card height for 6 columns grid */
        }
        .dark .gallery-card {
            border-color: rgba(255, 255, 255, 0.08) !important;
            background-color: rgba(255, 255, 255, 0.03) !important;
        }
        .gallery-card:hover {
            border-color: rgb(234, 88, 12) !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            transform: translateY(-2px) !important;
        }
        .gallery-image-wrapper {
            position: relative !important;
            width: 100% !important;
            height: 105px !important; /* Height adjusted for 6 columns grid */
            border-radius: 8px !important;
            overflow: hidden !important;
            background-color: rgba(156, 163, 175, 0.1) !important;
        }
        .gallery-image-wrapper img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            transition: transform 0.3s ease !important;
        }
        .gallery-card:hover .gallery-image-wrapper img {
            transform: scale(1.05) !important;
        }
        .gallery-card-title {
            font-size: 11px !important;
            font-weight: 550 !important;
            color: #4b5563 !important;
            margin-top: 6px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            padding: 0 4px !important;
            text-align: center !important;
        }
        .dark .gallery-card-title {
            color: #d1d5db !important;
        }
    </style>
</div>
