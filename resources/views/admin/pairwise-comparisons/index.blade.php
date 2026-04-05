<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Pairwise Comparison Kriteria') }}</h2>
    </x-slot>

    <div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6">Pairwise Comparisons</h1>

        <div class="space-y-4">
            @foreach($criteriaWithChildren as $criteria)
                <div class="border rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold">{{ $criteria->name }}</h3>
                            <p class="text-sm text-gray-600">
                                {{ $criteria->children->count() }} sub-criteria
                            </p>
                        </div>
                        <a href="{{ route('admin.pairwise-comparisons.edit', $criteria->id) }}"
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Edit Comparisons
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    </div>
</x-app-layout>