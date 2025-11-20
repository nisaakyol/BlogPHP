<?php
declare(strict_types=1);

/**
 * Vollwertiger RSS 2.0 Feed wie im Beispiel-Screenshot
 * MIT:
 *  - Titel, Beschreibung, Links
 *  - Datum (RFC2822)
 *  - Kategorien
 *  - Bildern innerhalb CDATA
 */

// Pfade & Bootstrap
require __DIR__ . '/path.php';
require_once ROOT_PATH . '/app/Support/includes/bootstrap.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DbRepository.php';

use App\Infrastructure\Repositories\DbRepository;

// Sauberes XML-escapen
function xmlEsc(string $v): string {
    return htmlspecialchars($v, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}

header('Content-Type: application/xml; charset=utf-8');

// Feed-Metadaten
$siteTitle       = "Emily Travel Blog";
$siteDescription = "Reiseberichte, Bilder, Erfahrungen";
$siteLink        = BASE_URL;

// Posts holen
$db = new DbRepository();
$posts = $db->selectAll('posts', [], "created_at DESC");

// XML Start
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss xmlns:atom="http://www.w3.org/2005/Atom" version="2.0">
<channel>

    <title><?php echo xmlEsc($siteTitle); ?></title>
    <link><?php echo xmlEsc($siteLink); ?></link>
    <description><?php echo xmlEsc($siteDescription); ?></description>
    <language>de</language>
    <lastBuildDate><?php echo date(DATE_RFC2822); ?></lastBuildDate>

    <atom:link href="<?php echo xmlEsc($siteLink . '/feed.php'); ?>"
               rel="self"
               type="application/rss+xml" />

    <?php foreach ($posts as $p): ?>
        <?php
            $id     = (int)$p['id'];
            $title  = xmlEsc($p['title']);
            $body   = strip_tags($p['body']);
            $date   = $p['created_at'] ?? date('Y-m-d H:i:s');
            $link   = $siteLink . "/single.php?id=" . $id;

            // Summary kürzen
            $summary = mb_substr($body, 0, 350) . (strlen($body) > 300 ? "…" : "");

            // optionales Bild (wenn du ein image-Feld hast)
            $img = "";
            if (!empty($p['image'])) {
                $imgUrl = $siteLink . "/uploads/" . $p['image'];
                $img = '<img src="' . $imgUrl . '" alt="" style="width:100%; max-width: 400px; height:auto;" />';
            }

            // Kategorie falls du eine Spalte category hast
            $category = isset($p['category']) ? xmlEsc($p['category']) : "Blog";
        ?>

        <item>
            <title><?php echo $title; ?></title>
            <link><?php echo xmlEsc($link); ?></link>
            <guid isPermaLink="true"><?php echo xmlEsc($link); ?></guid>
            <pubDate><?php echo date(DATE_RFC2822, strtotime($date)); ?></pubDate>
            <category><?php echo $category; ?></category>

            <description><![CDATA[
                <?php echo $img; ?>
                <p><?php echo xmlEsc($summary); ?></p>
                <p><a href="<?php echo xmlEsc($link); ?>">Weiterlesen</a></p>
            ]]></description>
        </item>

    <?php endforeach; ?>

</channel>
</rss>
