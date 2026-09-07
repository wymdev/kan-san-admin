# Shared admin UI

Edit color tokens in `resources/css/themes.css` and shared admin styling in `resources/css/custom/_admin.css`. The `admin-theme` class on `layouts.vertical` scopes the design, including light/dark colors, control heights, spacing, borders and focus styles. New admin screens should extend that layout.

## Components

| Component | Purpose |
| --- | --- |
| `x-ui.page-header` | Page title, optional subtitle and `actions` slot |
| `x-ui.card` | Card title, body, optional `actions` and `footer` slots |
| `x-ui.filter` | Native GET form with a filter landmark; preserve `action`, `id` and query field names |
| `x-ui.table` | Native table, optional accessible caption; keep an `overflow-x-auto` wrapper on the page |
| `x-ui.field` | Label, required marker, hint and field error; `name` must match the control ID |
| `x-ui.input`, `x-ui.select`, `x-ui.textarea` | Native form controls; forward IDs, names, values, validation and widget attributes |
| `x-ui.button` | Button or link; primary/secondary/success/danger variants; buttons default to `type="button"` |
| `x-ui.alert` | Info/success/warning/error message with an appropriate status/alert role |
| `x-ui.flash-messages` | Layout-owned escaped session messages and validation summary |
| `x-ui.badge`, `x-ui.empty-state` | Compact status and empty result presentation |

```blade
<x-ui.card title="Customers">
    <x-slot:actions>
        <x-ui.button :href="route('customers.create')">Add customer</x-ui.button>
    </x-slot:actions>
    <x-ui.filter :action="route('customers.index')" class="flex gap-3">
        <x-ui.input name="search" :value="request('search')" aria-label="Search customers" />
        <x-ui.button type="submit">Search</x-ui.button>
        <x-ui.button :href="route('customers.index')" variant="secondary">Clear</x-ui.button>
    </x-ui.filter>
</x-ui.card>

<x-ui.field name="email" label="Email" required>
    <x-ui.input id="email" name="email" type="email" :value="old('email')"
        required :aria-describedby="$errors->has('email') ? 'email-error' : null" />
</x-ui.field>
```

Values and `old()` defaults remain explicit so password/file controls are never unintentionally repopulated. A required field marker does not replace the native control's `required` attribute or server validation. Use `:disabled="$condition"` and `:readonly="$condition"` on components; standalone Blade directives inside component attributes are unsupported.

Do not add page-level flash summaries: the layout renders them once. Keep contextual notices beside the operation they describe using `x-ui.alert`. Keep permission checks around actions. Do not put Blade components in client-generated JavaScript HTML strings; use native elements with the shared classes there.

Existing IDs and DOM elements remain native for Preline, Choices, Flatpickr and page scripts. Tables retain their responsive wrappers, slots and action forms.

Validation: `npm run build`, `php artisan view:cache`, and `php artisan test`. `AdminScreensTest` exercises representative protected pages; `UiComponentsTest` checks attributes, semantics and escaping. Browser visual review requires an available browser connection.
