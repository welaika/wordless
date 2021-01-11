<?php

$settings = parse_ini_file('.env');

function env($var)
{
    global $settings;

    $value = getenv($var);

    if ($value === false) {
        return isset($settings[$var]) ? $settings[$var] : null;
    }

    return $value;
}

function request($url, $repo = 'phug', $data = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://api.github.com/repos/phug-php/$repo/$url");
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    if ($data) {
        $payload = json_encode($data);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: token '.env('GITHUB_TOKEN'),
        ]);
    }

    $content = curl_exec($curl);
    curl_close($curl);

    return $content;
}

function json($url)
{
    return json_decode(request($url));
}

$draft = false;
$argv = array_values(array_filter($argv, function ($arg) use (&$draft) {
    if ($arg !== '-d' && $arg !== '--draft') {
        return true;
    }

    $draft = true;

    return false;
}));
$numberNames = ['major', 'minor', 'patch'];
$preTypes = ['alpha', 'beta', 'RC'];
$index = array_search(strtolower(isset($argv[1]) ? $argv[1] : ''), $numberNames);

if ($index === false) {
    echo 'Please choose one of the number to increment: '.implode(', ', $numberNames);
    exit(1);
}

$prerelease = null;

if (isset($argv[2])) {
    $prereleaseInput = strtolower($argv[2]);

    if ($prereleaseInput !== 'stable') {
        $preIndex = array_search($prereleaseInput, array_map('strtolower', $preTypes));

        if ($preIndex === false) {
            echo 'Please choose one of the following type (or none): '.implode(', ', $preTypes);
            exit(1);
        }

        $prerelease = $preTypes[$preIndex];
    }
}

$description = null;

if (isset($argv[3])) {
    $description = @file_get_contents($argv[3]);
}

$json = json('releases');
$maxRelease = $json[0];

for ($i = count($json) - 1; $i > 0; $i--) {
    $release = $json[$i];

    if (version_compare($release->tag_name, $maxRelease->tag_name, '>')) {
        $maxRelease = $release;
    }
}

list($version, $preType) = array_pad(explode('-', $maxRelease->tag_name), 2, '');

$number = 0;

if (preg_match('/^(\w+)\.?(\d+)$/', $preType, $match)) {
    list(, $preType, $number) = $match;
}

$tag = array_map('intval', explode('.', $version));
$isStable = ($preType === '');

if ($isStable && (!$prerelease || strtolower($prerelease) !== strtolower($preType))) {
    $tag[$index]++;

    for ($i = $index + 1; $i < count($tag); $i++) {
        $tag[$i] = 0;
    }
}

$tag = implode('.', $tag).($prerelease ? "-$prerelease".($number + 1) : '');

echo "Publishing tag $tag\n";

$projects = [
    'phug',
    'renderer',
    'ast',
    'compiler',
    'dependency-injection',
    'event',
    'facade',
    'formatter',
    'invoker',
    'lexer',
    'parser',
    'reader',
    'util',
];

if (empty($description)) {
    $description = readline('Description: ');
}

foreach ($projects as $project) {
    echo "Tagging $project\n";

    $content = request('releases', $project, [
        'tag_name'         => $tag,
        'target_commitish' => 'master',
        'name'             => $tag,
        'body'             => $description,
        'draft'            => $draft,
        'prerelease'       => (bool) $prerelease,
    ]);

    echo "$content\n\n";
}
