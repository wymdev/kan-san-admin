@extends('layouts.vertical', ['title' => 'Add Secondary Ticket'])

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<style>
    .ocr-zone {
        border: 3px dashed rgba(var(--primary-rgb), 0.4);
        border-radius: 1rem;
        padding: 2.5rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        background: rgba(var(--primary-rgb), 0.02);
    }
    .ocr-zone:hover, .ocr-zone.dragover {
        border-color: rgba(var(--primary-rgb), 0.8);
        background: rgba(var(--primary-rgb), 0.08);
    }
    .ocr-zone .icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1rem;
        background: rgba(var(--primary-rgb), 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ocr-preview-img {
        max-height: 200px;
        border-radius: 0.5rem;
        object-fit: contain;
        margin: 1rem auto;
        display: block;
    }
    .extracted-num {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-family: 'Courier New', monospace;
        font-size: 1.25rem;
        font-weight: 700;
        letter-spacing: 0.15em;
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.1) 0%, rgba(var(--primary-rgb), 0.05) 100%);
        border: 2px solid rgba(var(--primary-rgb), 0.3);
        border-radius: 0.5rem;
        margin: 0.25rem;
        cursor: pointer;
        transition: all 0.25s ease;
        user-select: none;
    }
    .extracted-num:hover {
        background: rgba(var(--primary-rgb), 0.15);
        border-color: rgba(var(--primary-rgb), 0.6);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.2);
    }
    /* Selected state - Green background */
    .extracted-num.selected {
        background: rgba(34, 197, 94, 0.15);
        border-color: rgba(34, 197, 94, 0.6);
        color: rgb(22, 163, 74);
    }
    .extracted-num.selected:hover {
        background: rgba(239, 68, 68, 0.15);
        border-color: rgba(239, 68, 68, 0.6);
    }
    .extracted-num .status-icon {
        transition: all 0.2s;
    }
    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid rgba(var(--primary-rgb), 0.2);
        border-top-color: rgb(var(--primary-rgb));
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 1rem auto;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Secondary Sales', 'title' => 'Add New Ticket'])

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- OCR Scanner Section --}}
        <div class="card">
            <div class="card-header">
                <h6 class="card-title flex items-center gap-2">
                    <i class="size-4" data-lucide="camera"></i> Scan Lottery Ticket
                </h6>
            </div>
            <div class="card-body">
                <div class="ocr-zone" id="ocrZone">
                    <input type="file" id="fileInput" accept="image/*" class="hidden">
                    
                    <div id="uploadPrompt">
                        <div class="icon">
                            <i class="size-8 text-primary" data-lucide="camera"></i>
                        </div>
                        <p class="text-lg font-medium text-default-700">Click to upload or drag & drop</p>
                        <p class="text-sm text-default-500 mt-1">Supports JPG, PNG, JPEG • Max 5MB</p>
                        <button type="button" class="btn bg-primary text-white mt-4" onclick="document.getElementById('fileInput').click()">
                            <i class="size-4 me-2" data-lucide="upload"></i> Choose Image
                        </button>
                    </div>
                    
                    <div id="previewArea" class="hidden">
                        <img id="previewImg" class="ocr-preview-img">
                        <button type="button" class="btn btn-sm bg-danger/10 text-danger" onclick="resetScanner()">
                            <i class="size-4 me-1" data-lucide="x"></i> Remove
                        </button>
                    </div>
                </div>

                <div id="loadingArea" class="hidden text-center py-4">
                    <div class="spinner"></div>
                    <p class="text-sm text-default-500">Extracting lottery numbers...</p>
                </div>

                <div id="resultsArea" class="hidden mt-4">
                    <h6 class="text-sm font-semibold mb-3 text-success flex items-center gap-2">
                        <i class="size-4" data-lucide="check-circle"></i> Numbers Found (click to add):
                    </h6>
                    <div id="numbersContainer" class="flex flex-wrap"></div>
                </div>

                <div id="errorArea" class="hidden mt-4 p-3 bg-danger/10 text-danger rounded-lg text-sm"></div>
            </div>
        </div>

        {{-- Manual Entry Form --}}
        <div class="card">
            <div class="card-header">
                <h6 class="card-title flex items-center gap-2">
                    <i class="size-4" data-lucide="edit-3"></i> Ticket Details
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('secondary-tickets.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Batch Number --}}
                    <div class="mb-4 p-4 bg-primary/5 border border-primary/20 rounded-lg">
                        <label class="form-label font-semibold flex items-center gap-2">
                        <i class="size-4" data-lucide="package"></i> Batch Number <span class="text-xs text-default-400 font-normal">(group tickets)</span>
                        </label>
                        <input type="text" name="batch_number" value="{{ old('batch_number') }}" 
                               class="form-input @error('batch_number') border-danger @enderror" 
                               placeholder="e.g., 45, 46, BATCH-A">
                        <p class="text-xs text-default-400 mt-1">Tickets with same batch will be grouped for customer's public link</p>
                        @error('batch_number')
                            <span class="text-danger text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Ticket Numbers --}}
                    <div class="mb-4">
                        <label class="form-label">Ticket Number(s) <span class="text-danger">*</span></label>
                        <input type="text" name="numbers" id="numbersInput" value="{{ old('numbers') }}" 
                               class="form-input font-mono text-lg @error('numbers') border-danger @enderror" 
                               placeholder="123456, 654321, 111222" required>
                        <p class="text-xs text-default-400 mt-1">Enter 6-digit numbers separated by commas</p>
                        @error('numbers')
                            <span class="text-danger text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="form-label">Draw Date</label>
                            <input type="date" name="withdraw_date" value="{{ old('withdraw_date') }}" class="form-input">
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Price (฿)</label>
                            <input type="number" name="price" value="{{ old('price', 80) }}" step="0.01" min="0" class="form-input">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Source Seller</label>
                        <input type="text" name="source_seller" value="{{ old('source_seller') }}" 
                               class="form-input" placeholder="Where did you buy this ticket?">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" rows="2" class="form-input" placeholder="Optional notes...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Attach Image <span class="text-xs text-default-400">(optional)</span></label>
                        <input type="file" name="source_image" id="attachImage" accept="image/*" class="form-input">
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="submit" class="btn bg-primary text-white">
                            <i class="size-4 me-1" data-lucide="save"></i> Save Ticket
                        </button>
                        <a href="{{ route('secondary-tickets.index') }}" class="btn bg-default-200">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ocrZone = document.getElementById('ocrZone');
    const fileInput = document.getElementById('fileInput');
    const uploadPrompt = document.getElementById('uploadPrompt');
    const previewArea = document.getElementById('previewArea');
    const previewImg = document.getElementById('previewImg');
    const loadingArea = document.getElementById('loadingArea');
    const resultsArea = document.getElementById('resultsArea');
    const numbersContainer = document.getElementById('numbersContainer');
    const errorArea = document.getElementById('errorArea');
    const numbersInput = document.getElementById('numbersInput');

    // Click zone to trigger file input
    ocrZone.addEventListener('click', function(e) {
        if (e.target !== fileInput && !e.target.closest('button')) {
            fileInput.click();
        }
    });

    // Drag and drop
    ocrZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        ocrZone.classList.add('dragover');
    });

    ocrZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        ocrZone.classList.remove('dragover');
    });

    ocrZone.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        ocrZone.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    });

    // File input change
    fileInput.addEventListener('change', function() {
        if (fileInput.files.length > 0) {
            handleFile(fileInput.files[0]);
        }
    });

    function handleFile(file) {
        if (!file.type.startsWith('image/')) {
            showError('Please select a valid image file (JPG, PNG, JPEG)');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            showError('File size must be less than 5MB');
            return;
        }

        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            uploadPrompt.classList.add('hidden');
            previewArea.classList.remove('hidden');
        };
        reader.readAsDataURL(file);

        // Process OCR
        processOCR(file);
    }

    function processOCR(file) {
        loadingArea.classList.remove('hidden');
        resultsArea.classList.add('hidden');
        errorArea.classList.add('hidden');

        const formData = new FormData();
        formData.append('image', file);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("secondary-tickets.extract-ocr") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            loadingArea.classList.add('hidden');
            
            if (data.success && data.numbers && data.numbers.length > 0) {
                resultsArea.classList.remove('hidden');
                numbersContainer.innerHTML = '';
                
                data.numbers.forEach(function(num) {
                    const span = document.createElement('span');
                    span.className = 'extracted-num';
                    span.dataset.number = num;
                    span.innerHTML = num + ' <i class="size-4 status-icon text-success" data-lucide="plus-circle"></i>';
                    span.addEventListener('click', function() {
                        toggleNumber(span, num);
                    });
                    numbersContainer.appendChild(span);
                });
                
                // Re-initialize Lucide icons
                if (window.lucide) {
                    lucide.createIcons();
                }
            } else if (!data.success) {
                showError(data.error || 'Failed to extract numbers from image');
            } else {
                showError('No 6-digit lottery numbers found in the image. Try a clearer photo.');
            }
        })
        .catch(function(err) {
            loadingArea.classList.add('hidden');
            showError('OCR processing failed. Please try again.');
            console.error(err);
        });
    }

    function toggleNumber(span, num) {
        const current = numbersInput.value.trim();
        let existingNums = current ? current.split(',').map(n => n.trim()).filter(n => n) : [];
        
        if (span.classList.contains('selected')) {
            // Remove number
            existingNums = existingNums.filter(n => n !== num);
            span.classList.remove('selected');
            span.innerHTML = num + ' <i class="size-4 status-icon text-success" data-lucide="plus-circle"></i>';
        } else {
            // Add number
            if (!existingNums.includes(num)) {
                existingNums.push(num);
            }
            span.classList.add('selected');
            span.innerHTML = num + ' <i class="size-4 status-icon text-green-600" data-lucide="check-circle"></i>';
        }
        
        numbersInput.value = existingNums.join(', ');
        
        // Re-initialize icons
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function addNumber(num) {
        const current = numbersInput.value.trim();
        const existingNums = current ? current.split(',').map(n => n.trim()) : [];
        
        if (!existingNums.includes(num)) {
            numbersInput.value = current ? current + ', ' + num : num;
        }
    }

    function showError(msg) {
        errorArea.textContent = msg;
        errorArea.classList.remove('hidden');
    }

    // Global reset function
    window.resetScanner = function() {
        fileInput.value = '';
        uploadPrompt.classList.remove('hidden');
        previewArea.classList.add('hidden');
        resultsArea.classList.add('hidden');
        errorArea.classList.add('hidden');
        loadingArea.classList.add('hidden');
    };
});
</script>
@endsection
