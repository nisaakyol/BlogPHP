<?php
namespace App\OOP\Controllers\Admin;

use App\OOP\Repositories\DbRepository;

class AdminPostController
{
    public function __construct(private DbRepository $db) {}

    /* ---------- INDEX ---------- */
    public function index(): array
    {
        $posts = $this->db->selectAll('posts', [], 'created_at DESC');
        // user map (id => username) für Anzeige
        $users = $this->db->selectAll('users', [], 'id ASC');
        $usersById = [];
        foreach ($users as $u) { $usersById[(int)$u['id']] = $u['username']; }

        return compact('posts','usersById');
    }

    /* ---------- DELETE via GET ?delete_id=ID ---------- */
    public function delete(int $id): void
    {
        $this->db->delete('posts', $id);
        $_SESSION['message'] = "Post wurde erfolgreich gelöscht";
        $_SESSION['type'] = "success";
        header("Location: " . BASE_URL . "/admin/posts/index.php");
        exit;
    }

    /* ---------- Publish Toggle: ?published=0/1&p_id=ID ---------- */
    public function togglePublish(int $postId, int $published): void
    {
        $this->db->update('posts', $postId, ['published' => $published]);
        $_SESSION['message'] = "Post published Status geändert!";
        $_SESSION['type'] = "success";
        header("Location: " . BASE_URL . "/admin/posts/index.php");
        exit;
    }

    /* ---------- CREATE (GET + POST) ---------- */
    public function handleCreate(array $post, array $files): array
    {
        require_once ROOT_PATH . "/app/helpers/validatePost.php";
        $topics = $this->db->selectAll('topics', [], 'name ASC');

        // Defaults f. Formular
        $vm = ['errors'=>[], 'title'=>'', 'body'=>'', 'topic_id'=>'', 'published'=>'', 'topics'=>$topics];

        if (!isset($post['add-post'])) return $vm;

        $errors = validatePost($post);
        $imageName = $this->uploadImage($files['image'] ?? null, $errors);

        if ($errors) {
            return [
                'errors'   => $errors,
                'title'    => $post['title'] ?? '',
                'body'     => $post['body']  ?? '',
                'topic_id' => $post['topic_id'] ?? '',
                'published'=> !empty($post['published']) ? 1 : 0,
                'topics'   => $topics,
            ];
        }

        $data = [
            'title'    => $post['title'],
            'body'     => htmlentities($post['body']),
            'topic_id' => (int)$post['topic_id'],
            'user_id'  => (int)$_SESSION['id'],
            'image'    => $imageName,
        ];

        if (!empty($_SESSION['admin'])) {
            $data['published'] = !empty($post['published']) ? 1 : 0;
            $this->db->create('posts', $data);
            $_SESSION['message'] = "Post created successfuly";
        } else {
            if (!empty($post['AdminPublish'])) {
                $this->sendPublishEmail($post['title']);
            }
            $data['published'] = 0;
            $this->db->create('posts', $data);
            $_SESSION['message'] = "Post wurde zur Veröffentlichung versandt";
        }

        $_SESSION['type'] = "success";
        header("Location: " . BASE_URL . "/admin/posts/index.php");
        exit;
    }

    /* ---------- EDIT (GET + POST) ---------- */
    public function handleEdit(array $get, array $post, array $files): array
    {
        require_once ROOT_PATH . "/app/helpers/validatePost.php";
        $topics = $this->db->selectAll('topics', [], 'name ASC');

        // GET ?id => Formular füllen
        if (isset($get['id'])) {
            $p = $this->db->selectOne('posts', ['id' => (int)$get['id']]);
            return [
                'errors'   => [],
                'id'       => $p['id'],
                'title'    => $p['title'],
                'body'     => $p['body'],
                'topic_id' => $p['topic_id'],
                'published'=> (int)$p['published'],
                'topics'   => $topics,
            ];
        }

        // POST update
        if (!isset($post['update-post'])) {
            // Fallback: leere VM
            return ['errors'=>[], 'id'=>'','title'=>'','body'=>'','topic_id'=>'','published'=>'','topics'=>$topics];
        }

        $errors = validatePost($post);
        $imageName = $this->uploadImage($files['image'] ?? null, $errors, false); // Bild optional?

        if ($errors) {
            return [
                'errors'   => $errors,
                'id'       => (int)($post['id'] ?? 0),
                'title'    => $post['title'] ?? '',
                'body'     => $post['body']  ?? '',
                'topic_id' => $post['topic_id'] ?? '',
                'published'=> !empty($post['published']) ? 1 : 0,
                'topics'   => $topics,
            ];
        }

        $id = (int)$post['id'];
        $data = [
            'title'    => $post['title'],
            'body'     => htmlentities($post['body']),
            'topic_id' => (int)$post['topic_id'],
            'user_id'  => (int)$_SESSION['id'],
            'published'=> !empty($post['published']) ? 1 : 0,
        ];
        if ($imageName) $data['image'] = $imageName;

        $this->db->update('posts', $id, $data);
        $_SESSION['message'] = "Post update erfolgreich";
        $_SESSION['type'] = "success";
        header("Location: " . BASE_URL . "/admin/posts/index.php");
        exit;
    }

    /* ---------- Helpers ---------- */
    private function uploadImage(?array $file, array &$errors, bool $required = true): ?string
    {
        if (!$file || empty($file['name'])) {
            if ($required) $errors[] = 'Post image required';
            return null;
        }
        $imageName = time() . '_' . basename($file['name']);
        $dest = ROOT_PATH . "/assets/images/" . $imageName;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $errors[] = 'Failed to upload image';
            return null;
        }
        return $imageName;
    }

    private function sendPublishEmail(string $title): void
    {
        // Gleiche Logik wie früher (sammelstelledhbwblog@gmail.com)
        $information = [
            'Title' => $title,
            'nachricht' => 'Bitte veröffentlichen Sie diesen Post'
        ];
        require ROOT_PATH . '/app/helpers/send-email.php';
    }
}
