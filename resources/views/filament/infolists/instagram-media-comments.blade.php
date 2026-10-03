@php
    /** @var int $recordId */
@endphp

<livewire:instagram-media-comments-panel
    :instagram-media-id="$recordId"
    :key="'instagram-media-comments-'.$recordId"
/>
