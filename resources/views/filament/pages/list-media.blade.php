<x-filament-panels::page>
    <div class="media-gallery-container">
        {{ $this->table }}
    </div>

    <style>
        /* Scoped to media gallery page */
        .media-gallery-container .fi-ta-content-grid > * {
            position: relative !important;
            border-radius: 16px !important;
            border: 1px solid rgba(156, 163, 175, 0.15) !important;
            background-color: rgba(156, 163, 175, 0.02) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            padding: 0px !important; /* Flush image to edges */
            overflow: hidden !important; /* Clip corners */
        }
        
        .dark .media-gallery-container .fi-ta-content-grid > * {
            border-color: rgba(255, 255, 255, 0.06) !important;
            background-color: rgba(255, 255, 255, 0.02) !important;
        }
        
        /* Elevate card and add shadow on hover */
        .media-gallery-container .fi-ta-content-grid > *:hover {
            background-color: rgba(156, 163, 175, 0.04) !important;
            border-color: rgba(156, 163, 175, 0.25) !important;
            transform: translateY(-4px) !important;
            box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.15) !important;
        }
        
        .dark .media-gallery-container .fi-ta-content-grid > *:hover {
            background-color: rgba(255, 255, 255, 0.04) !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 12px 24px -10px rgba(0, 0, 0, 0.6) !important;
        }

        .media-gallery-container .fi-ta-content-grid > * > div {
            display: block !important;
            position: static !important;
        }

        /* Make image and all its wrappers take full card width and height */
        .media-gallery-container .fi-ta-content-grid img {
            width: 100% !important;
            height: 180px !important;
            object-fit: cover !important;
            border-radius: 0px !important;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        
        .media-gallery-container .fi-ta-content-grid .fi-ta-image,
        .media-gallery-container .fi-ta-content-grid .fi-ta-image > div,
        .media-gallery-container .fi-ta-content-grid .fi-ta-image > div > div {
            width: 100% !important;
            height: 180px !important;
            overflow: hidden !important;
        }
        
        /* Subtle image zoom on card hover */
        .media-gallery-container .fi-ta-content-grid > *:hover img {
            transform: scale(1.04) !important;
        }

        /* Pad details and actions under the image */
        .media-gallery-container .fi-ta-content-grid .flex-1 {
            width: 100% !important;
            display: block !important;
            padding: 16px !important;
        }

        /* Checkbox overlay positioned at top-left, preserving native orange branding */
        .media-gallery-container .fi-ta-content-grid .fi-ta-record-checkbox {
            position: absolute !important;
            top: 16px !important;
            left: 16px !important;
            z-index: 20 !important;
            margin: 0 !important;
        }

        /* Center and add divider to action buttons */
        .media-gallery-container .fi-ta-actions {
            margin-top: 12px !important;
            display: flex !important;
            justify-content: center !important;
            gap: 16px !important;
            border-top: 1px solid rgba(156, 163, 175, 0.1) !important;
            padding-top: 12px !important;
        }
        
        .dark .media-gallery-container .fi-ta-actions {
            border-top-color: rgba(255, 255, 255, 0.06) !important;
        }

        .media-gallery-container .fi-ta-actions button, 
        .media-gallery-container .fi-ta-actions a {
            opacity: 0.7 !important;
            transition: all 0.2s ease !important;
        }
        
        .media-gallery-container .fi-ta-actions button:hover, 
        .media-gallery-container .fi-ta-actions a:hover {
            opacity: 1 !important;
            transform: scale(1.1) !important;
        }
    </style>
</x-filament-panels::page>
