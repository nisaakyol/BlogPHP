<?php
declare(strict_types=1);

// Zweck: Liefert View-Model-Daten für Startseite, Topic-Filter und Suche (Posts, Topics, Titel) aus dem Repository.

namespace App\Http\Controllers;

use App\Infrastructure\Repositories\DbRepository;

class HomeController
{
    // Repository-Injection für DB-Zugriffe
    public function __construct(private DbRepository $db) {}

    /**
     * Startseite: veröffentlichte Posts + Topics.
     *
     * @return array{posts: array, topics: array, title: string}
     */
    public function recent(): array
    {
        // Veröffentlichte Posts laden
        $posts  = $this->db->getPublishedPosts();
        // Alle Topics für Sidebar/Filter
        $topics = $this->db->selectAll('topics');
        // Seitentitel
        $title  = 'Recent Posts';

        // Kompaktes View-Model
        return compact('posts', 'topics', 'title');
    }

    /**
     * Filter: Posts eines bestimmten Topics.
     *
     * @param int    $topicId   ID des Topics
     * @param string $topicName Anzeigename (nur für die Überschrift)
     * @return array{posts: array, topics: array, title: string}
     */
    public function byTopic(int $topicId, string $topicName): array
    {
        // Posts zu Topic-ID
        $posts  = $this->db->getPostsByTopicId($topicId);
        // Topics-Liste (z. B. Sidebar)
        $topics = $this->db->selectAll('topics');
        // Dynamischer Titel
        $title  = "You searched for posts under '{$topicName}'";

        return compact('posts', 'topics', 'title');
    }

    /**
     * Suche: Posts nach Suchbegriff.
     *
     * @param string $term Suchbegriff
     * @return array{posts: array, topics: array, title: string}
     */
    public function search(string $term): array
    {
        // Posts per Volltext-/LIKE-Suche
        $posts  = $this->db->searchPosts($term);
        // Topics für Navigation
        $topics = $this->db->selectAll('topics');
        // Suchtitel
        $title  = "You searched for '{$term}'";

        return compact('posts', 'topics', 'title');
    }
}
