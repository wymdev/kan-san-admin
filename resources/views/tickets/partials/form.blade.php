{{-- $ticket is optional for edit, $barcode passed for create --}}
<div class="grid grid-cols-2 gap-4">
    <div class="form-group">
        <label class="form-label" for="ticket_name">Name*</label>
        <input class="form-input @error('ticket_name') border-red-500 @enderror"
               id="ticket_name" name="ticket_name" type="text"
               value="{{ old('ticket_name', $ticket->ticket_name ?? '') }}" required>
        @error('ticket_name')<div class="text-red-500 text-xs">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="signature">Signature</label>
        <input class="form-input" id="signature" name="signature" type="text"
               value="{{ old('signature', $ticket->signature ?? '') }}">
    </div>
    <div class="form-group">
        <label class="form-label" for="withdraw_date">Withdraw Date*</label>
        <input class="form-input" id="withdraw_date" name="withdraw_date" type="date"
               value="{{ old('withdraw_date', isset($ticket) ? $ticket->withdraw_date?->format('Y-m-d') : '') }}" required>
        @error('withdraw_date')<div class="text-red-500 text-xs">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="ticket_type">Type*</label>
        <select class="form-input" id="ticket_type" name="ticket_type" required>
            <option value="normal" @selected(old('ticket_type', $ticket->ticket_type ?? '') == 'normal')>Normal</option>
            <option value="special" @selected(old('ticket_type', $ticket->ticket_type ?? '') == 'special')>Special</option>
            <option value="lucky" @selected(old('ticket_type', $ticket->ticket_type ?? '') == 'lucky')>Lucky</option>
        </select>
    </div>
    <div class="form-group col-span-2">
        <label class="form-label" for="numbers">Numbers*</label>
        <div class="flex gap-2">
            @for($i = 0; $i < 6; $i++)
                <input name="numbers[]"
                    class="form-input w-12 text-center @error('numbers.'.$i) border-red-500 @enderror"
                    maxlength="1"
                    type="text"
                    value="{{ old('numbers.'.$i, isset($ticket) && is_array($ticket->numbers) ? $ticket->numbers[$i] ?? '' : '') }}"
                    required>
            @endfor
        </div>
        @for($i = 0; $i < 6; $i++)
            @error('numbers.'.$i)
                <div class="text-red-500 text-xs">{{ $message }}</div>
            @enderror
        @endfor
    </div>
    <div class="form-group">
        <label class="form-label" for="bar_code">Barcode*</label>
        <input class="form-input" id="bar_code" name="bar_code"
               value="{{ old('bar_code', $barcode ?? ($ticket->bar_code ?? '')) }}" readonly required>
        @error('bar_code')<div class="text-red-500 text-xs">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="period">Period*</label>
        <input class="form-input" id="period" name="period" type="number"
               value="{{ old('period', $ticket->period ?? '') }}" required>
    </div>
    <div class="form-group">
        <label class="form-label" for="big_num">Big Number</label>
        <input class="form-input" id="big_num" name="big_num" type="number"
               value="{{ old('big_num', $ticket->big_num ?? '') }}">
    </div>
    <div class="form-group">
        <label class="form-label" for="set_no">Set No</label>
        <input class="form-input" id="set_no" name="set_no" type="number"
               value="{{ old('set_no', $ticket->set_no ?? '') }}">
    </div>
    <div class="form-group">
        <label class="form-label" for="price">Price*</label>
        <input class="form-input" id="price" name="price" type="number" step="0.01"
               value="{{ old('price', $ticket->price ?? '') }}" required>
        @error('price')<div class="text-red-500 text-xs">{{ $message }}</div>@enderror
    </div>
    <div class="form-group col-span-2">
        <label class="form-label" for="left_icon">Left Icon (Image, .jpg/.png)</label>
        <input class="form-input" id="left_icon" name="left_icon" type="file" accept="image/*">
        {{-- Preview on edit --}}
        @if(isset($ticket) && $ticket->left_icon)
            <div class="mt-2">
                <img src="{{ asset('storage/'.$ticket->left_icon) }}" alt="icon" class="h-16 w-auto rounded border">
                <div class="text-xs text-default-400 mt-1">Current: {{ $ticket->left_icon }}</div>
            </div>
        @endif
        @error('left_icon')<div class="text-red-500 text-xs">{{ $message }}</div>@enderror
    </div>
</div>
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select your 6 OTP inputs by name attribute
    const inputs = document.querySelectorAll('input[name="numbers[]"]');
    inputs.forEach((input, idx) => {
        input.addEventListener('input', function(e) {
            if (this.value.length === 1 && idx < inputs.length - 1) {
                inputs[idx+1].focus();
            }
        });
        input.addEventListener('keydown', function(e) {
            if ((e.key === "Backspace" || e.key === "Delete") && this.value === "" && idx > 0) {
                inputs[idx-1].focus();
            }
        });
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            let paste = (e.clipboardData || window.clipboardData).getData('text');
            paste = paste.replace(/\D/g, '');
            for(let i = 0; i < paste.length && idx+i < inputs.length; i++) {
                inputs[idx+i].value = paste[i];
            }
            if(idx + paste.length - 1 < inputs.length) {
                inputs[idx + paste.length - 1].focus();
            }
        });
    });
});
</script>
@endsection
