<?php
namespace App\OOP\Controllers;

use App\OOP\Repositories\DbRepository;

class HomeController
{
    public function __construct(private DbRepository $db) {}

    public function recent(): array
    {
        $posts  = $this->db->getPublishedPosts();
        $topics = $this->db->selectAll('topics');
        $title  = 'Recent Posts';
        return compact('posts', 'topics', 'title');
    }

    public function byTopic(int $topicId, string $topicName): array
    {
        $posts  = $this->db->getPostsByTopicId($topicId);
        $topics = $this->db->selectAll('topics');
        $title  = "You searched for posts under '{$topicName}'";
        return compact('posts', 'topics', 'title');
    }

    public function search(string $term): array
    {
        $posts  = $this->db->searchPosts($term);
        $topics = $this->db->selectAll('topics');
        $title  = "You searched for '{$term}'";
        return compact('posts', 'topics', 'title');
    }
}
