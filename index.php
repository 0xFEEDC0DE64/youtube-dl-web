<?php

$options = [
    "audio_mp3" => [
        'name' => 'Audio Only (MP3)',
        'group' => 'Audio Only',
        'option' => '-k -x --audio-format mp3 --audio-quality 0',
        'cache' => '*-%youtubeid%.mp3'
    ],
    "audio_flac" => [
        'name' => 'Audio Only (FLAC)',
        'group' => 'Audio Only',
        'option' => '-k -x --audio-format flac --audio-quality 0',
        'cache' => '*-%youtubeid%.flac'
    ],
    "best_video_audio" => [
        'name' => 'Combine best Video+Audio',
        'group' => NULL,
        'option' => '-k',
        'cache' => NULL
    ]
];

if(!chdir('files')) {
    die('could not enter files folder');
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(!isset($_POST['option'], $_POST['url'])) {
        http_response_code(400);
        die('not all required values set!');
    }

    if(empty($option = trim($_POST['option'])) ||
       empty($url = trim($_POST['url']))) {
        http_response_code(400);
        die('url empty!');
    }

    if(!filter_var($url, FILTER_VALIDATE_URL)) {
        http_response_code(400);
        die('invalid url!');
    }

    if(!isset($options[$option])) {
        http_response_code(400);
        die('invalid option!');
    }

    $option = $options[$option];

    preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+(?=\?)|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $url, $matches);
    if(count($matches) != 1) {
        http_response_code(400);
        die('invalid youtube url!' . count($matches));
    }

    $cached = false;

    if(!is_null($option['cache'])) {
        foreach(glob(str_replace("%youtubeid%", $matches[0], $option['cache'])) as $file) {
            echo '[cache] Destination: ' . $file . "\n";
            $cached = true;
        }
    }

    if($cached) {
        die('exit code: 0');
    }

    ini_set('output_buffering', 'off');
    ini_set('zlib.output_compression', false);
    ini_set('implicit_flush', true);
    ob_implicit_flush(true);
    while (ob_get_level() > 0) {
        $level = ob_get_level();
        ob_end_clean();
        if (ob_get_level() == $level) break;
    }
    if (function_exists('apache_setenv')) {
        apache_setenv('no-gzip', '1');
        apache_setenv('dont-vary', '1');
    }
    set_time_limit(0);

    system('youtube-dl ' . $option['option']. ' ' . $url . ' 2>&1', $exitCode);
    die('exit code: ' . $exitCode);
}

?>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">

        <title>Daniel's YouTube Downloader</title>

        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous" />
        <style>
            pre {
                max-height: 100px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <form method="POST" class="form-inline">
                <select name="option" class="form-control">
                    <?php $lastGroup = NULL; ?>
                    <?php foreach($options as $index => $option) { ?>
                    <?php if($option['group'] != $lastGroup) { ?>
                    <?php if(!is_null($lastGroup)) { ?></optgroup><?php } ?>
                    <?php if(!is_null($option['group'])) { ?><optgroup label="<?php echo htmlentities($option['group']); ?>"><?php } ?>
                    <?php $lastGroup = $option['group']; ?>
                    <?php } ?>
                    <option value="<?php echo $index; ?>"><?php echo htmlentities($option['name']); ?></option>
                    <?php } ?>
                </select>
                <input type="url" name="url" <?php if(isset($url)) { echo 'value="' . htmlentities($url) . '"'; } ?>  required="required" class="form-control" />
                <button type="submit" class="btn btn-primary">Los</button>
                <a href="info.php">Service Status</a>
            </form>
        </div>

        <div class="row" id="results"></div>

        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
        <script src="script.js"></script>
    </body>
</html>
