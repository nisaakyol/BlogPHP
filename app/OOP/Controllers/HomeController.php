<?php
declare(strict_types=1);

namespace App\OOP\Controllers;

use App\OOP\Repositories\DbRepository;

/**
 * Datei: App/OOP/Controllers/HomeController.php
 *
 * Zweck:
 * - Liefert View-Model-Daten für Startseite, Topic-Filter und Suche.
 * - Nutzt DbRepository-Methoden (getPublishedPosts, getPostsByTopicId, searchPosts).
 */
class HomeController
{
    public function __construct(private DbRepository $db)
    {
    }

    /**
     * Startseite: veröffentlichte Posts + Topics.
     *
     * @return array{posts: array, topics: array, title: string}
     */
    public function recent(): array
    {
        $posts  = $this->db->getPublishedPosts();
        $topics = $this->db->selectAll('topics');
        $title  = 'Recent Posts';

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
        $posts  = $this->db->getPostsByTopicId($topicId);
        $topics = $this->db->selectAll('topics');
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
        $posts  = $this->db->searchPosts($term);
        $topics = $this->db->selectAll('topics');
        $title  = "You searched for '{$term}'";

        return compact('posts', 'topics', 'title');
    }
}
