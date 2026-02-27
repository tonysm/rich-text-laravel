@foreach ($users as $user)
<lexxy-prompt-item search="{{ $user->name }}" sgid="{{ $user->richTextSgid() }}">
    <template type="menu">{{ $user->name }}</template>
    <template type="editor">@include('mentions.partials.user', ['user' => $user])</template>
</lexxy-prompt-item>
@endforeach
