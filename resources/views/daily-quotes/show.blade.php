@extends('layouts.vertical', ['title' => 'View Quote'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Mobile App', 'title' => 'Quote Details'])

    <div class="grid grid-cols-1 gap-5">
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <h4 class="card-title">Daily Quote</h4>
                <div class="flex gap-2">
                    @if(!$quote->is_sent)
                        <form action="{{ route('daily-quotes.send-now', $quote) }}" method="POST" class="inline">
                            @csrf
                            <button 
                                type="submit" 
                                class="btn btn-sm bg-green-600 text-white"
                                onclick="return confirm('Send this quote now?')"
                            >
                                <i class="size-4 me-1" data-lucide="send"></i>
                                Send Now
                            </button>
                        </form>
                        <a href="{{ route('daily-quotes.edit', $quote) }}" class="btn btn-sm bg-yellow-500 text-white">
                            <i class="size-4 me-1" data-lucide="edit"></i>
                            Edit
                        </a>
                    @endif
                    <a href="{{ route('daily-quotes.index') }}" class="btn btn-sm bg-default-200 text-default-600">
                        <i class="size-4 me-1" data-lucide="arrow-left"></i>
                        Back
                    </a>
                </div>
            </div>
            <div class="p-6">
                <!-- Quote Content -->
                <div class="mb-6">
                    <div class="bg-gradient-to-br from-primary/5 to-primary/10 border-l-4 border-primary rounded-r-lg p-6">
                        <div class="flex items-start gap-3">
                            <i class="size-8 text-primary flex-shrink-0" data-lucide="quote"></i>
                            <div class="flex-1">
                                <p class="text-lg text-default-900 italic leading-relaxed">{{ $quote->quote }}</p>
                                @if($quote->author)
                                    <p class="text-sm text-default-600 mt-3 font-medium">— {{ $quote->author }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="text-sm font-medium text-default-500 mb-2 block">Status</label>
                        @if($quote->is_sent)
                            <span class="inline-flex items-center gap-1 py-1.5 px-3 rounded text-sm font-medium bg-green-100 text-green-700">
                                <i class="size-4" data-lucide="check-circle"></i> Sent
                            </span>
                        @elseif($quote->is_active)
                            <span class="inline-flex items-center gap-1 py-1.5 px-3 rounded text-sm font-medium bg-yellow-100 text-yellow-700">
                                <i class="size-4" data-lucide="clock"></i> Pending
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 py-1.5 px-3 rounded text-sm font-medium bg-default-200 text-default-700">
                                Inactive
                            </span>
                        @endif
                    </div>

                    <div>
                        <label class="text-sm font-medium text-default-500 mb-2 block">Category</label>
                        <span class="inline-flex py-1.5 px-3 rounded text-sm font-medium capitalize bg-default-100 text-default-700">
                            {{ $quote->category }}
                        </span>
                    </div>

                    @if($quote->scheduled_for)
                        <div>
                            <label class="text-sm font-medium text-default-500 mb-1 block">Scheduled For</label>
                            <p class="text-default-900">{{ $quote->scheduled_for->format('d M Y') }}</p>
                        </div>
                    @endif

                    @if($quote->sent_at)
                        <div>
                            <label class="text-sm font-medium text-default-500 mb-1 block">Sent At</label>
                            <p class="text-default-900">{{ $quote->sent_at->format('d M Y, H:i:s') }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-default-500 mb-1 block">Recipients</label>
                            <p class="text-default-900 font-semibold">{{ $quote->recipients_count }}</p>
                        </div>
                    @endif

                    <div>
                        <label class="text-sm font-medium text-default-500 mb-1 block">Created At</label>
                        <p class="text-default-900">{{ $quote->created_at->format('d M Y, H:i:s') }}</p>
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
                                        <i class="size-6 text-white" data-lucide="quote"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-default-900">Daily Quote</p>
                                    <p class="text-sm text-default-600 mt-1">
                                        {{ strlen($quote->quote) > 100 ? substr($quote->quote, 0, 97) . '...' : $quote->quote }}
                                    </p>
                                    @if($quote->author)
                                        <p class="text-xs text-default-500 mt-1">— {{ $quote->author }}</p>
                                    @endif
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
