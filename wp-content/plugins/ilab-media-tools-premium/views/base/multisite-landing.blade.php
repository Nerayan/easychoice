<?php /** @var \ILAB\MediaCloud\Tools\ToolsManager $manager */ ?>
@extends('../templates/sub-page')

@section('main')
    @if(!empty($manager->multisiteTools()))
        <h2>Tools</h2>
        @foreach($manager->multisiteTools() as $tool)
        <div class="ilab-settings-section ilab-settings-features">
            <h4>{{$tool->toolInfo['name']}}</h4>
            <p>{!! $tool->toolInfo['description'] !!}</p>
            <a class="button" href="{{admin_url('admin.php?page='.$tool->optionsPage())}}">Launch Tool</a>
        </div>
        @endforeach
    @endif
    @if(!empty($manager->multisiteBatchTools()))
        <h2>Batch Tools</h2>
        @foreach($manager->multisiteBatchTools() as $tool)
            <div class="ilab-settings-section ilab-settings-features">
                <h4>{{$tool->title()}}</h4>
                @include($tool->instructionView(), ['description' => true, 'background' => false])
                <a class="button" href="{{admin_url('admin.php?page='.$tool->menuSlug())}}">Launch Tool</a>
            </div>
        @endforeach
    @endif
@endsection