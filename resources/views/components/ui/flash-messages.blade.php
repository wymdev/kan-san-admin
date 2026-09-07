<div class="ui-messages">
    @foreach(['success', 'error', 'warning', 'info'] as $type)
        @if(session()->has($type))
            <x-ui.alert :type="$type">{{ session($type) }}</x-ui.alert>
        @endif
    @endforeach
    @if($errors->any())
        <x-ui.alert type="error" title="Please check the following fields:">
            <ul class="list-disc ps-5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </x-ui.alert>
    @endif
</div>
