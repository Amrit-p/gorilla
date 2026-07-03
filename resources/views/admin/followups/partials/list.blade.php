@if ($followups->isEmpty())
    <div class="flex flex-col items-center justify-center gap-3 py-16 text-center">
        <svg class="h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-sm font-medium text-slate-500">No follow-ups found.</p>
        <a href="{{ route('admin.followups.create') }}"
           class="text-sm font-medium text-slate-900 underline underline-offset-2">
            Create the first one
        </a>
    </div>
@else
    <ul class="divide-y divide-slate-100">
        @foreach ($followups as $followup)
            <li class="flex flex-wrap items-start gap-4 px-5 py-4">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        @php $followableUrl = $followup->followableUrl(); @endphp
                        @if ($followableUrl)
                            <a href="{{ $followableUrl }}" class="text-sm font-semibold text-slate-900 hover:underline">
                                {{ class_basename($followup->followable_type) }} #{{ $followup->followable_id }}
                            </a>
                        @else
                            <span class="text-sm font-semibold text-slate-900">
                                {{ class_basename($followup->followable_type) }} #{{ $followup->followable_id }}
                            </span>
                        @endif
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $followup->status->badgeClass() }}">
                            {{ $followup->status->label() }}
                        </span>
                    </div>
                    <p class="mt-1 line-clamp-2 text-sm text-slate-600">{{ $followup->outcome }}</p>
                    <p class="mt-1 text-xs text-slate-400">
                        Created {{ $followup->created_at->diffForHumans() }}
                        @if ($followup->assignedTo)
                            &middot; Assigned to {{ $followup->assignedTo->name }}
                        @endif
                        @if ($followup->next_followup_at)
                            &middot; Next: {{ $followup->next_followup_at->format('d M Y') }}
                        @endif
                    </p>
                </div>
                <div class="flex shrink-0 gap-2">
                    <a href="{{ route('admin.followups.show', $followup) }}"
                       class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                        View
                    </a>
                    <a href="{{ route('admin.followups.edit', $followup) }}"
                       class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                        Edit
                    </a>
                    <button type="button"
                            class="delete-followup-btn rounded-md border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                            data-action="{{ route('admin.followups.destroy', $followup) }}">
                        Delete
                    </button>
                </div>
            </li>
        @endforeach
    </ul>

    @if ($followups->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $followups->links() }}
        </div>
    @endif
@endif
