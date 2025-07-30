
@props(['name' => 'content', 'value' => '','id' => ''])

@php
  $editorId = $id ?: 'editor-' . $name;
@endphp

<div>
  <div class='editControls'>
    @include('components.editor-toolbar')
  </div>

  <textarea name="{{ $name }}"  id="hidden-{{ $editorId }}" hidden></textarea>
</div>


    <link rel="stylesheet" href="{{ asset('css/editor.css') }}">


    <script src="{{ asset('js/editor.js') }}"></script>



