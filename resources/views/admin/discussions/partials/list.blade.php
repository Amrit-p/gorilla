@if ($discussions->isEmpty())
    <div class="flex flex-col items-center justify-center gap-3 py-16 text-center">
        <svg class="h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <p class="text-sm font-medium text-slate-500">No discussions found.</p>
        <a href="{{ route('admin.discussions.create') }}"
           class="text-sm font-medium text-slate-900 underline underline-offset-2">
            Create your first one
        </a>
    </div>
@else
    <ul id="discussions-list" class="divide-y divide-slate-100">
        @foreach ($discussions as $discussion)
            @php
                $attachmentCount = $discussion->sections->sum(fn ($s) => $s->attachments->count());
                $badgeClass = match($discussion->category) {
                    'budget'     => 'bg-yellow-100 text-yellow-700',
                    'worker'     => 'bg-blue-100 text-blue-700',
                    'expansion'  => 'bg-purple-100 text-purple-700',
                    'crm_update' => 'bg-emerald-100 text-emerald-700',
                    default      => 'bg-slate-100 text-slate-600',
                };
                $categoryLabel = match($discussion->category) {
                    'worker'     => 'Worker',
                    'budget'     => 'Budget',
                    'expansion'  => 'Expansion',
                    'crm_update' => 'CRM Update',
                    default      => 'General',
                };
            @endphp
            <li class="discussion-item flex flex-wrap items-center gap-4 px-5 py-4"
                data-category="{{ $discussion->category }}">
                <div class="min-w-0 flex-1">
                    <p class="discussion-title truncate text-sm font-semibold text-slate-900">
                        {{ $discussion->title }}
                    </p>
                    <p class="mt-0.5 text-xs text-slate-500">
                        {{ $discussion->date->format('d M Y') }}
                        &middot;
                        {{ $discussion->sections->count() }} {{ Str::plural('section', $discussion->sections->count()) }}
                        @if ($attachmentCount > 0)
                            &middot; {{ $attachmentCount }} {{ Str::plural('file', $attachmentCount) }}
                        @endif
                    </p>
                </div>
                <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">
                    {{ $categoryLabel }}
                </span>
                <div class="flex shrink-0 gap-2">
                    <a href="{{ route('admin.discussions.show', $discussion) }}"
                       class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                        View
                    </a>
                    <a href="{{ route('admin.discussions.edit', $discussion) }}"
                       class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                        Edit
                    </a>
                    <button type="button"
                            class="delete-discussion-btn rounded-md border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                            data-action="{{ route('admin.discussions.destroy', $discussion) }}">
                        Delete
                    </button>
                </div>
            </li>
        @endforeach
    </ul>
@endif
