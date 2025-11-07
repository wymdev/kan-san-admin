@extends('layouts.vertical', ['title' => 'View Announcement'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Mobile App', 'title' => 'Announcement Details'])

    <div class="grid grid-cols-1 gap-5">
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <h4 class="card-title">{{ $announcement->title }}</h4>
                <div class="flex gap-2">
                    @if(!$announcement->is_sent)
                        <a href="{{ route('announcements.edit', $announcement) }}" class="btn btn-sm bg-yellow-500 text-white">
                            <i class="size-4 me-1" data-lucide="edit"></i>
                            Edit
                        </a>
                    @endif
                    <a href="{{ route('announcements.index') }}" class="btn btn-sm bg-default-200 text-default-600">
                        <i class="size-4 me-1" data-lucide="arrow-left"></i>
                        Back
                    </a>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Status Badge -->
                    <div>
                        <label class="text-sm font-medium text-default-500 mb-2 block">Status</label>
                        @if($announcement->is_sent)
                            <span class="inline-flex items-center gap-1 py-1.5 px-3 rounded text-sm font-medium bg-green-100 text-green-700">
                                <i class="size-4" data-lucide="check-circle"></i> Sent
                            </span>
                        @elseif($announcement->is_published)
                            <span class="inline-flex items-center gap-1 py-1.5 px-3 rounded text-sm font-medium bg-yellow-100 text-yellow-700">
                                <i class="size-4" data-lucide="clock"></i> Pending
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 py-1.5 px-3 rounded text-sm font-medium bg-default-200 text-default-700">
                                <i class="size-4" data-lucide="file-text"></i> Draft
                            </span>
                        @endif
                    </div>

                    <!-- Type Badge -->
                    <div>
                        <label class="text-sm font-medium text-default-500 mb-2 block">Type</label>
                        <span class="inline-flex py-1.5 px-3 rounded text-sm font-medium capitalize
                            @if($announcement->type === 'general') bg-blue-100 text-blue-700
                            @elseif($announcement->type === 'promotion') bg-green-100 text-green-700
                            @elseif($announcement->type === 'maintenance') bg-orange-100 text-orange-700
                            @else bg-purple-100 text-purple-700
                            @endif">
                            {{ ucfirst($announcement->type) }}
                        </span>
                    </div>
                </div>

                <!-- Message -->
                <div class="mb-6">
                    <label class="text-sm font-medium text-default-500 mb-2 block">Message</label>
                    <div class="bg-default-50 border border-default-200 rounded-lg p-4">
                        <p class="text-default-900 whitespace-pre-line">{{ $announcement->body }}</p>
                    </div>
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    @if($announcement->scheduled_at)
                        <div>
                            <label class="text-sm font-medium text-default-500 mb-1 block">Scheduled For</label>
                            <p class="text-default-900">{{ $announcement->scheduled_at->format('d M Y, H:i') }}</p>
                        </div>
                    @endif

                    @if($announcement->sent_at)
                        <div>
                            <label class="text-sm font-medium text-default-500 mb-1 block">Sent At</label>
                            <p class="text-default-900">{{ $announcement->sent_at->format('d M Y, H:i:s') }}</p>
                        </div>
                    @endif

                    @if($announcement->is_sent)
                        <div>
                            <label class="text-sm font-medium text-default-500 mb-1 block">Total Recipients</label>
                            <p class="text-default-900 font-semibold">{{ $announcement->recipients_count }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-default-500 mb-1 block">Delivery Status</label>
                            <div>
                                <span class="text-green-600 font-semibold">✓ {{ $announcement->success_count }}</span>
                                @if($announcement->failed_count > 0)
                                    <span class="text-red-600 font-semibold ml-3">✗ {{ $announcement->failed_count }}</span>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="text-sm font-medium text-default-500 mb-1 block">Created By</label>
                        <p class="text-default-900">{{ $announcement->creator->name ?? 'System' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-default-500 mb-1 block">Created At</label>
                        <p class="text-default-900">{{ $announcement->created_at->format('d M Y, H:i:s') }}</p>
                    </div>
                </div>

                <!-- Mobile Preview -->
                <div class="border-t border-default-200 pt-6">
                    <label class="text-sm font-medium text-default-900 mb-3 block">Mobile Notification Preview</label>
                    <div class="max-w-md">
                        <div class="bg-white border border-default-200 rounded-lg shadow-lg p-4">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
                                        <i class="size-6 text-white" data-lucide="bell"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-default-900">{{ $announcement->title }}</p>
                                    <p class="text-sm text-default-600 mt-1">
                                        {{ strlen($announcement->body) > 100 ? substr($announcement->body, 0, 97) . '...' : $announcement->body }}
                                    </p>
                                    <p class="text-xs text-default-400 mt-1">Just now</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
