<?php
define('ROOT', __DIR__);
define('DS', DIRECTORY_SEPARATOR);

require_once ROOT . DS . 'vendor' . DS . 'autoload.php';

function buildPath($path, $directory_separator = '/') {
    $newPath = str_replace($directory_separator, DS, $path);
    if (strpos($path, '/') === 0) {
        return ROOT . $newPath;
    }

    return $newPath;
}

function getFiles($path) {
    $files = [];
    foreach (glob($path) as $file) {
        $files[] = "file '$file'";
    }
    natsort($files);

    return $files;
}

function out($message, $type = null) {

    $defaultColour = "\e[0m";

    switch ($type) {
        case 'info':
            $colour = "\e[34m";
            break;
        case 'error':
            $colour = "\e[31m";
            break;
        case 'warning':
            $colour = "\e[33m";
            break;
        case 'success':
            $colour = "\e[32m";
            break;
        default:
            $colour = "";
    }

    $out = $colour . $message . $defaultColour . "\n";
    file_put_contents(buildPath('/log'), $message . "\n", FILE_APPEND);
    echo $out;
}

$urlListPath = $argv[1] ?? 'urls.txt';
$urlListHandle = fopen(buildPath("/$urlListPath"), 'r');
if (!$urlListHandle) {
    exit(1);
}

while (($line_url = fgets($urlListHandle)) !== false) {
    $url = trim($line_url);
    if (!$url) {
        continue;
    }

    out(sprintf('Received URL %s', $url), 'success');

    $matches = [];
    if (preg_match('#playlist([0-9]+)\.ts#', $url)) {
        preg_match_all('#/(.[^/]*)\.mpegts/playlist([0-9+])\.ts$#', $url, $matches);
        $filename = $matches[1][0];
        $position = (int)$matches[2][0];
    } elseif (preg_match('#seg-([0-9]+)-v1-a1\.ts#', $url)) {
        preg_match_all('#/seg-([0-9+])-v1-a1\.ts$#', $url, $matches);
        $filename = 'vid_' . str_replace(' ', '_', str_replace('.', '_', microtime()));
        $position = (int)$matches[1][0];
    }

    $episodePath = buildPath("/episodes/$filename");
    if (!file_exists($episodePath) && !mkdir($episodePath, 0777, true)) {
        // dunno
        out('Could not make path for episode', 'error');
        exit(1);
    }

    $has_data = true;
    do {
        out(sprintf('Fetching chunk %s', $position));

        $client = new \GuzzleHttp\Client(['verify' => false]);
        $res = $client->request('GET', $url, [
            'http_errors' => false,
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',
        ]);
        $statusCode = $res->getStatusCode();

        if ($statusCode !== 200) {
            $has_data = false;
            continue;
        }

        $data = (string)$res->getBody();

        file_put_contents($episodePath . DS . "$position.mpegts", $data);

        if (preg_match('#playlist([0-9]+)\.ts#', $url)) {
            $url = preg_replace('#playlist([0-9]+)\.ts$#', 'playlist' . ++$position . '.ts', $url);
        } elseif (preg_match('#seg-([0-9]+)-v1-a1\.ts#', $url)) {
            $url = preg_replace('#seg-([0-9]+)-v1-a1\.ts$#', 'seg-' . ++$position . '-v1-a1.ts', $url);
        }

        sleep(4);
    } while ($has_data);

    $manifestPath = $episodePath . DS . 'manifest.txt';

    $manifestHandle = fopen($manifestPath, 'w');
    $files = getFiles($episodePath . DS . '*.mpegts');
    foreach ($files as $file) {
        fwrite($manifestHandle, $file . "\n");
    }

    $outputPath = buildPath('/episodes/complete') . DS . $filename . '.mp4';

    out(sprintf('Writing to %s', $outputPath), 'success');

    exec(
        sprintf(
            'ffmpeg -f concat -safe 0 -i %s -c copy %s',
            $manifestPath,
            $outputPath
        )
    );
}

