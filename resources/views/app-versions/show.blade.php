@extends('layouts.vertical', ['title' => 'View App Version'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Mobile App', 'title' => 'Version Details'])

    <div class="grid grid-cols-1 gap-5">
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <h4 class="card-title">Version {{ $version->version }} (Code: {{ $version->version_code }})</h4>
                <div class="flex gap-2">
                    <a href="{{ route('app-versions.edit', $version) }}" class="btn btn-sm bg-yellow-500 text-white">
                        <i class="size-4 me-1" data-lucide="edit"></i>Edit
                    </a>
                    <a href="{{ route('app-versions.index') }}" class="btn btn-sm bg-default-200 text-default-600">
                        <i class="size-4 me-1" data-lucide="arrow-left"></i>Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h5 class="text-sm font-semibold text-default-700 mb-2">Basic Information</h5>
                        <table class="w-full text-sm">
                            <tr class="border-b border-default-200">
                                <td class="py-2 text-default-600">Version:</td>
                                <td class="py-2 font-medium">{{ $version->version }}</td>
                            </tr>
                            <tr class="border-b border-default-200">
                                <td class="py-2 text-default-600">Version Code:</td>
                                <td class="py-2 font-medium">{{ $version->version_code }}</td>
                            </tr>
                            <tr class="border-b border-default-200">
                                <td class="py-2 text-default-600">Platform:</td>
                                <td class="py-2">
                                    <span class="inline-flex py-0.5 px-2.5 rounded text-xs font-normal bg-blue-100 border border-blue-200 text-blue-600 capitalize">
                                        {{ $version->platform }}
                                    </span>
                                </td>
                            </tr>
                            <tr class="border-b border-default-200">
                                <td class="py-2 text-default-600">Status:</td>
                                <td class="py-2">
                                    @if($version->is_active)
                                        <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-green-100 border border-green-200 text-green-600">
                                            <i class="size-3" data-lucide="check-circle"></i> Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-default-100 border border-default-200 text-default-500">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr class="border-b border-default-200">
                                <td class="py-2 text-default-600">Latest Version:</td>
                                <td class="py-2">
                                    @if($version->is_latest)
                                        <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-purple-100 border border-purple-200 text-purple-600">
                                            <i class="size-3" data-lucide="star"></i> Yes
                                        </span>
                                    @else
                                        <span class="text-default-400">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2 text-default-600">Release Date:</td>
                                <td class="py-2 font-medium">{{ $version->release_date ? $version->release_date->format('d M Y, H:i') : '-' }}</td>
                            </tr>
                        </table>
                    </div>

                    <div>
                        <h5 class="text-sm font-semibold text-default-700 mb-2">Update Settings</h5>
                        <table class="w-full text-sm">
                            <tr class="border-b border-default-200">
                                <td class="py-2 text-default-600">Force Update:</td>
                                <td class="py-2">
                                    @if($version->force_update)
                                        <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-red-100 border border-red-200 text-red-600">
                                            <i class="size-3" data-lucide="alert-circle"></i> Yes
                                        </span>
                                    @else
                                        <span class="text-default-400">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr class="border-b border-default-200">
                                <td class="py-2 text-default-600">Minimum Version:</td>
                                <td class="py-2 font-medium">{{ $version->minimum_version ?? '-' }}</td>
                            </tr>
                            <tr class="border-b border-default-200">
                                <td class="py-2 text-default-600">Min Version Code:</td>
                                <td class="py-2 font-medium">{{ $version->minimum_version_code ?? '-' }}</td>
                            </tr>
                            <tr class="border-b border-default-200">
                                <td class="py-2 text-default-600">Download URL:</td>
                                <td class="py-2">
                                    @if($version->download_url)
                                        <a href="{{ $version->download_url }}" target="_blank" class="text-blue-500 hover:underline text-xs">
                                            {{ Str::limit($version->download_url, 40) }}
                                        </a>
                                    @else
                                        <span class="text-default-400">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2 text-default-600">Display Order:</td>
                                <td class="py-2 font-medium">{{ $version->display_order }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($version->release_notes)
                    <div class="mt-6">
                        <h5 class="text-sm font-semibold text-default-700 mb-2">Release Notes</h5>
                        <div class="bg-default-50 p-4 rounded border border-default-200">
                            <p class="text-sm text-default-700 whitespace-pre-line">{{ $version->release_notes }}</p>
                        </div>
                    </div>
                @endif

                @if($version->features && count($version->features) > 0)
                    <div class="mt-6">
                        <h5 class="text-sm font-semibold text-default-700 mb-2">New Features</h5>
                        <ul class="list-disc list-inside bg-green-50 p-4 rounded border border-green-200">
                            @foreach($version->features as $feature)
                                @if($feature)
                                    <li class="text-sm text-default-700">{{ $feature }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($version->bug_fixes && count($version->bug_fixes) > 0)
                    <div class="mt-6">
                        <h5 class="text-sm font-semibold text-default-700 mb-2">Bug Fixes</h5>
                        <ul class="list-disc list-inside bg-blue-50 p-4 rounded border border-blue-200">
                            @foreach($version->bug_fixes as $bugFix)
                                @if($bugFix)
                                    <li class="text-sm text-default-700">{{ $bugFix }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-6 pt-4 border-t border-default-200">
                    <p class="text-xs text-default-500">Created: {{ $version->created_at->format('d M Y, H:i') }}</p>
                    <p class="text-xs text-default-500">Last Updated: {{ $version->updated_at->format('d M Y, H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
