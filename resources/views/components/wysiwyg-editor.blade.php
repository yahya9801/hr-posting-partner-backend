@props(['name' => 'content', 'value' => '', 'id' => ''])

@php
    $editorId = $id ?: 'editor-' . $name;
    $hiddenFieldId = 'hidden-' . $editorId;
@endphp

<div class="wysiwyg-wrapper border border-gray-300 rounded-lg shadow-sm bg-white overflow-hidden" data-editor="{{ $editorId }}">
    @include('components.editor-toolbar', ['value' => $value, 'editorId' => $editorId])
    <textarea name="{{ $name }}" id="{{ $hiddenFieldId }}" hidden>{!! $value !!}</textarea>
</div>

@once
    <link rel="stylesheet" href="{{ asset('css/editor.css') }}">
    <script src="{{ asset('js/editor.js') }}"></script>
@endonce
