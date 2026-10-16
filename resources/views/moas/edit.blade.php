<x-app-layout>
    @section('title', 'Edit ' . ($moa->moa_number ?: 'MOA #' . $moa->id))

    <x-slot name="header">
        <div class="flex items-center gap-2 text-xs text-text-muted mb-1">
            <a href="{{ route('moas.index') }}" class="hover:text-primary transition">MOAs</a>
            <span>/</span>
            <a href="{{ route('moas.show', $moa) }}" class="hover:text-primary transition">{{ $moa->moa_number ?: 'MOA #' . $moa->id }}</a>
            <span>/</span>
            <span class="text-text font-medium">Edit</span>
        </div>
        <h1 class="text-xl font-bold text-text">Edit Agreement Details</h1>
        <p class="text-xs text-text-muted mt-0.5">Update tracking numbers, covenants, and validity period for {{ $moa->company->name }}.</p>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-surface border border-border rounded-xl shadow-sm p-6 sm:p-8">
            <form method="POST" action="{{ route('moas.update', $moa) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-text mb-1">Partner Organization</label>
                    <input type="text" value="{{ $moa->company->name }}" disabled class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface-2 text-text opacity-80" />
                </div>

                <div>
                    <label for="moa_number" class="block text-xs font-semibold text-text mb-1">Institutional MOA Tracking Number</label>
                    <input 
                        type="text" 
                        name="moa_number" 
                        id="moa_number" 
                        value="{{ old('moa_number', $moa->moa_number) }}" 
                        class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="effective_date" class="block text-xs font-semibold text-text mb-1">Effective Date</label>
                        <input 
                            type="date" 
                            name="effective_date" 
                            id="effective_date" 
                            value="{{ old('effective_date', $moa->effective_date?->format('Y-m-d')) }}" 
                            class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                        />
                    </div>

                    <div>
                        <label for="expiry_date" class="block text-xs font-semibold text-text mb-1">Expiration Date</label>
                        <input 
                            type="date" 
                            name="expiry_date" 
                            id="expiry_date" 
                            value="{{ old('expiry_date', $moa->expiry_date?->format('Y-m-d')) }}" 
                            class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                        />
                    </div>
                </div>

                <div>
                    <label for="summary" class="block text-xs font-semibold text-text mb-1">Summary / Key Terms</label>
                    <textarea 
                        name="summary" 
                        id="summary" 
                        rows="3" 
                        class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    >{{ old('summary', $moa->summary) }}</textarea>
                </div>

                <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                    <a href="{{ route('moas.show', $moa) }}" class="px-4 py-2 text-xs font-medium text-text-muted hover:text-text rounded-lg transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold bg-primary text-primary-contrast rounded-lg shadow hover:opacity-90 transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
