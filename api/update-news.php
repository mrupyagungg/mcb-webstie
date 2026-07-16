<?php

date_default_timezone_set('Asia/Jakarta');

/*
|--------------------------------------------------------------------------
| Konfigurasi
|--------------------------------------------------------------------------
*/

$rssFeeds = [

    [
        "url" => "https://www.antaranews.com/rss/terkini.xml",
        "source" => "ANTARA"
    ]

];

$keywords = [
    "infrastruktur",
    "telekomunikasi",
    "fiber",
    "internet",
    "jalan",
    "tol",
    "jembatan",
    "pln",
    "telkom",
    "tower",
    "bts",
    "smart city",
    "digital",
    "konstruksi",
    "bandwidth",
    "jaringan"
];

$defaultImage = "img/blog-default.jpg";

$news = [];

/*
|--------------------------------------------------------------------------
| Fungsi Ambil Gambar RSS
|--------------------------------------------------------------------------
*/

function getImage(SimpleXMLElement $item, string $defaultImage): string
{
    // enclosure
    if (isset($item->enclosure)) {
        $url = (string)$item->enclosure['url'];
        if (!empty($url)) {
            return $url;
        }
    }

    // media namespace
    $media = $item->children('media', true);

    if ($media) {

        if (isset($media->content)) {

            foreach ($media->content as $content) {

                $attr = $content->attributes();

                if (!empty($attr['url'])) {
                    return (string)$attr['url'];
                }

            }

        }

        if (isset($media->thumbnail)) {

            foreach ($media->thumbnail as $thumb) {

                $attr = $thumb->attributes();

                if (!empty($attr['url'])) {
                    return (string)$attr['url'];
                }

            }

        }

    }

    return $defaultImage;
}

/*
|--------------------------------------------------------------------------
| Fungsi Kategori
|--------------------------------------------------------------------------
*/

function getCategory(string $title): string
{
    $title = strtolower($title);

    if (
        str_contains($title, "pln")
    ) {
        return "Electrical";
    }

    if (
        str_contains($title, "fiber") ||
        str_contains($title, "internet") ||
        str_contains($title, "bts") ||
        str_contains($title, "telkom") ||
        str_contains($title, "telekomunikasi")
    ) {
        return "Telecommunication";
    }

    if (
        str_contains($title, "jalan") ||
        str_contains($title, "tol") ||
        str_contains($title, "jembatan") ||
        str_contains($title, "konstruksi")
    ) {
        return "Infrastructure";
    }

    if (
        str_contains($title, "smart city")
    ) {
        return "Smart City";
    }

    return "News";
}

/*
|--------------------------------------------------------------------------
| Baca Semua RSS
|--------------------------------------------------------------------------
*/

foreach ($rssFeeds as $feed) {

    $xml = @simplexml_load_file($feed["url"]);

    if (!$xml) {
        continue;
    }

    foreach ($xml->channel->item as $item) {

        $title = trim((string)$item->title);

        $description = trim(strip_tags((string)$item->description));

        $text = strtolower($title . " " . $description);

        $found = false;

        foreach ($keywords as $keyword) {

            if (strpos($text, strtolower($keyword)) !== false) {

                $found = true;
                break;

            }

        }

        if (!$found) {
            continue;
        }

        $news[] = [

            "title" => $title,

            "description" => mb_substr($description, 0, 180) . "...",

            "image" => getImage($item, $defaultImage),

            "date" => date(
                "Y-m-d H:i:s",
                strtotime((string)$item->pubDate)
            ),

            "category" => getCategory($title),

            "url" => (string)$item->link,

            "source" => $feed["source"]

        ];

    }

}

/*
|--------------------------------------------------------------------------
| Hapus Duplikat
|--------------------------------------------------------------------------
*/

$temp = [];
$result = [];

foreach ($news as $item) {

    if (!isset($temp[$item["url"]])) {

        $temp[$item["url"]] = true;
        $result[] = $item;

    }

}

/*
|--------------------------------------------------------------------------
| Urutkan Terbaru
|--------------------------------------------------------------------------
*/

usort($result, function ($a, $b) {

    return strtotime($b["date"]) <=> strtotime($a["date"]);

});

/*
|--------------------------------------------------------------------------
| Maksimal 20 Berita
|--------------------------------------------------------------------------
*/

$result = array_slice($result, 0, 20);

/*
|--------------------------------------------------------------------------
| Simpan Cache
|--------------------------------------------------------------------------
*/

file_put_contents(
    __DIR__ . "/../cache/news.json",
    json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo "Berhasil update " . count($result) . " berita.";