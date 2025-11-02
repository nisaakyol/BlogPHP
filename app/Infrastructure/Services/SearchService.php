<?php
declare(strict_types=1);

// Zweck: Scoring für Volltextsuche (Titel/Body/Recency) inkl. Token-Levenshtein zur Tippfehler-Toleranz.

namespace App\Infrastructure\Services;

final class SearchService
{
    public static function score(string $query, string $title, string $body, ?string $createdAt = null): float
    {
        $q = self::norm($query);
        $t = self::norm($title);
        $b = self::norm(strip_tags($body));

        if ($q === '' || ($t === '' && $b === '')) return 0.0;

        $score = 0.0;

        // 1) Harte Treffer (Titel)
        if ($t === $q)               $score += 70;     // exakt gleich
        if (str_starts_with($t, $q)) $score += 45;     // Prefix-Titel
        if (str_contains($t, $q))    $score += 25;     // Substring im Titel

        // 2) Token-Levenshtein im Titel (einzelne Wörter vergleichen)
        $score += self::bestTokenLevenshtein($q, $t) * 0.8; // bis ~80

        // 3) Body grob (nur Bonus, niedriger gewichtet)
        if (str_contains($b, $q))    $score += 12;

        // 4) Recency (max +10 in den letzten 90 Tagen)
        if ($createdAt) {
            $ts = strtotime($createdAt);
            if ($ts !== false) {
                $ageDays = max(0, (time() - $ts) / 86400);
                $recency = max(0.0, 1.0 - min($ageDays, 90) / 90.0);
                $score += 10.0 * $recency;
            }
        }

        // 5) Clamp
        if ($score < 0)   $score = 0;
        if ($score > 100) $score = 100;

        return $score;
    }

    private static function norm(string $s): string
    {
        $s = mb_strtolower(trim($s));
        // einfache Akzent-/Umlaut-Normierung
        $s = strtr($s, [
            'ä'=>'ae','ö'=>'oe','ü'=>'ue','ß'=>'ss',
            'á'=>'a','à'=>'a','â'=>'a','é'=>'e','è'=>'e','ê'=>'e','í'=>'i','ì'=>'i','î'=>'i',
            'ó'=>'o','ò'=>'o','ô'=>'o','ú'=>'u','ù'=>'u','û'=>'u'
        ]);
        // mehrfaches Whitespace zu einem Leerzeichen
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
        return $s;
    }

    // Vergleicht Query gegen die besten Titel-Tokens. Kurze Queries (<=4) toleranter.
    private static function bestTokenLevenshtein(string $q, string $titleNorm): float
    {
        if ($q === '' || $titleNorm === '') return 0.0;

        $tokens = preg_split('/[^a-z0-9]+/u', $titleNorm, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (!$tokens) return 0.0;

        $best = 0.0;
        $qlen = mb_strlen($q);

        foreach ($tokens as $tok) {
            $tlen = mb_strlen($tok);
            if ($tlen === 0) continue;

            // Distanz gegen Token und gegen gleichlangen Ausschnitt (für Einfügungen)
            $d1 = levenshtein($q, $tok);
            $slice = mb_substr($tok, 0, $qlen);
            $d2 = levenshtein($q, $slice);

            $d = min($d1, $d2);
            $norm = max($qlen, 3);                 // Toleranz für sehr kurze Queries
            $ratio = max(0.0, 1.0 - ($d / $norm)); // 1.0 perfekt, 0.0 schlecht
            $tokenScore = $ratio * 80.0;           // bis 80 Punkte

            // Zusatzbonus wenn Token-Prefix
            if (str_starts_with($tok, $q)) $tokenScore += 10;

            if ($tokenScore > $best) $best = $tokenScore;
        }

        return min($best, 90.0);
    }
}
