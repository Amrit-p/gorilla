@if ($isMower)
    <x-layouts.mower :title="'Mower Report'" :showBack="true" :backUrl="route('mower.index')">
        @include('reports.mower.partials.content')
    </x-layouts.mower>
@else
    <x-layouts.dashboard :title="'Mower Reports'" :subtitle="$hideBonusColumn ? null : 'View earnings, completed jobs, and incentive details for all mowers.'">
        @include('reports.mower.partials.content')
    </x-layouts.dashboard>
@endif
