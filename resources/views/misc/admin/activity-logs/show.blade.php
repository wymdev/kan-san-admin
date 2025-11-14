@extends('layouts.vertical', ['title' => 'Log Details'])

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Admin', 'title' => 'Activity Log Details'])

    <div class="card">
        <div class="card-header"><h6 class="card-title">Activity Log Detail</h6></div>
        <div class="p-6">
            <dl>
                <dt class="font-medium">Action</dt>
                <dd class="mb-2">{{ $activityLog->action }}</dd>

                <dt class="font-medium">Actor</dt>
                <dd class="mb-2">
                    @if($activityLog->actor)
                        {{ class_basename($activityLog->actor_type) }}: {{ $activityLog->actor->name ?? $activityLog->actor->email ?? $activityLog->actor_id }}
                    @else
                        System
                    @endif
                </dd>

                <dt class="font-medium">Subject</dt>
                <dd class="mb-2">
                    @if($activityLog->loggable)
                        {{ class_basename($activityLog->loggable_type) }}: {{ $activityLog->loggable->getLogIdentifier() ?? $activityLog->loggable_id }}
                    @else
                        -
                    @endif
                </dd>

                <dt class="font-medium">Description</dt>
                <dd class="mb-2">{{ $activityLog->description }}</dd>

                <dt class="font-medium">Context / Guard</dt>
                <dd class="mb-2">{{ $activityLog->context }} / {{ $activityLog->guard }}</dd>

                <dt class="font-medium">Route</dt>
                <dd class="mb-2">{{ $activityLog->route }}</dd>

                <dt class="font-medium">IP / User Agent</dt>
                <dd class="mb-2">{{ $activityLog->ip_address }}<br><small>{{ $activityLog->user_agent }}</small></dd>

                <dt class="font-medium">Metadata</dt>
                <dd class="mb-2"><pre>{{ json_encode($activityLog->metadata, JSON_PRETTY_PRINT) }}</pre></dd>

                <dt class="font-medium">Old Values</dt>
                <dd class="mb-2"><pre>{{ json_encode($activityLog->old_values, JSON_PRETTY_PRINT) }}</pre></dd>

                <dt class="font-medium">New Values</dt>
                <dd class="mb-2"><pre>{{ json_encode($activityLog->new_values, JSON_PRETTY_PRINT) }}</pre></dd>

                <dt class="font-medium">Created At</dt>
                <dd class="mb-2">{{ $activityLog->created_at }}</dd>
            </dl>
            <a href="{{ route('activity-logs.index') }}" class="btn mt-4 bg-default-200">Back to Activity Logs</a>
        </div>
    </div>
@endsection
