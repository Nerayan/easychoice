<script>
    var mediaCloudDirectUploadSettings = {
        "driver": '{{$driver}}',
        "maxUploads": {{(int)$maxUploads}},
        "imgixEnabled": {{($imgixEnabled) ? 'true' : 'false'}},
        "videoEnabled": {{($videoEnabled) ? 'true' : 'false'}},
        "docsEnabled": {{($docUploads) ? 'true' : 'false'}},
        "extrasEnabled": {{($altFormats) ? 'true' : 'false'}},
        "allowedMimes": {!! json_encode($allowedMimes, JSON_PRETTY_PRINT) !!},
        "wildcardUploads": {{($wildcardUploads) ? 'true' : 'false'}},
        "imageQuality": {{$imageQuality}},
        "generateThumbnails": {{($generateThumbnails) ? 'true' : 'false'}}
    };
</script>