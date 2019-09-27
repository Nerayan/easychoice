@extends('../templates/sub-page', ['title' => $title])

@section('header')
    <a href="{{parse_url(get_admin_url(null, 'admin-ajax.php'), PHP_URL_PATH) . "?action=ilab_new_image_size_page"}}" class="button button-primary ilab-thickbox">Add New Image Size</a>
@endsection

@section('main')
    <div class="settings-body">
        <p>This page will allow you to manage WordPress image sizes.</p>
    </div>
    <div class="settings-body">
        <table class="ilab-image-sizes">
            <thead>
                <th>Type</th>
                <th>Title</th>
                <th>Size</th>
                <th>Width</th>
                <th>Height</th>
                <th>Crop</th>
                <th>Crop X Axis</th>
                <th>Crop Y Axis</th>
                <th></th>
            </thead>
            <tbody>
            @foreach($wpSizes as $size)
                <tr class="ilab-size-row ilab-fixed-size-row">
                    <input type="hidden" name="nonce" value="{{wp_create_nonce('custom-size')}}">
                    <input type="hidden" name="size" value="{{$size['size']}}">
                    <td>{{$size['type']}}</td>
                    <td>{{$size['title']}}</td>
                    <td>{{$size['size']}}</td>
                    <td>{{$size['width']}}</td>
                    <td>{{$size['height']}}</td>
                    <td class="center">@include('base/ui/checkbox', ['name' => 'crop', 'value' => !empty($size['crop']), 'description' => 'Crop Enabled', 'enabled' => false])</td>
                    <td class="center">
                        <select name="x-axis" disabled="disabled">
                            <option {{($size['x-axis'] == 'left') ? 'selected' : ''}} value="left">Left</option>
                            <option {{(empty($size['x-axis']) || ($size['x-axis'] == 'center')) ? 'selected' : ''}} value="center">Center</option>
                            <option {{($size['x-axis'] == 'right') ? 'selected' : ''}} value="right">Right</option>
                        </select>
                    </td>
                    <td class="center">
                        <select name="y-axis" disabled="disabled">
                            <option {{($size['y-axis'] == 'top') ? 'selected' : ''}} value="top">Top</option>
                            <option {{(empty($size['y-axis']) || ($size['y-axis'] == 'center')) ? 'selected' : ''}} value="center">Center</option>
                            <option {{($size['y-axis'] == 'bottom') ? 'selected' : ''}} value="bottom">Bottom</option>
                        </select>
                    </td>
                    <td class="center">
                        <a class="ilab-delete-size-button disabled">Delete</a>
                        @if($hasDynamic)
                        <a href="#" class="ilab-size-settings-button">Settings</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            @foreach($sizes as $size)
                <tr class="ilab-size-row ilab-custom-size-row">
                    <input type="hidden" name="nonce" value="{{wp_create_nonce('custom-size')}}">
                    <input type="hidden" name="size" value="{{$size['size']}}">
                    <td>{{$size['type']}}</td>
                    <td>{{$size['title']}}</td>
                    <td>{{$size['size']}}</td>
                    <td><input type="number" min="0" max="99999" name="width" value="{{$size['width']}}"></td>
                    <td><input type="number" min="0" max="99999" name="height" value="{{$size['height']}}"></td>
                    <td class="center">@include('base/ui/checkbox', ['name' => $size['size'].'__crop', 'value' => !empty($size['crop']), 'description' => 'Crop Enabled', 'enabled' => true])</td>
                    <td class="center">
                        <select name="x-axis">
                            <option {{($size['x-axis'] == 'left') ? 'selected' : ''}} value="left">Left</option>
                            <option {{(empty($size['x-axis']) || ($size['x-axis'] == 'center')) ? 'selected' : ''}} value="center">Center</option>
                            <option {{($size['x-axis'] == 'right') ? 'selected' : ''}} value="right">Right</option>
                        </select>
                    </td>
                    <td class="center">
                        <select name="y-axis">
                            <option {{($size['y-axis'] == 'top') ? 'selected' : ''}} value="top">Top</option>
                            <option {{(empty($size['y-axis']) || ($size['y-axis'] == 'center')) ? 'selected' : ''}} value="center">Center</option>
                            <option {{($size['y-axis'] == 'bottom') ? 'selected' : ''}} value="bottom">Bottom</option>
                        </select>
                    </td>
                    <td class="center">
                        <a href="#" class="ilab-delete-size-button">Delete</a>
                        @if($hasDynamic)
                        <a href="#" class="ilab-size-settings-button">Settings</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
