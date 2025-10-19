<?php
namespace App\OOP\Repositories;

use App\OOP\Core\DB;
use PDO;

class TopicRepository {
    private string $table = 'topics';

    public function all(): array {
        if (function_exists('selectAll')) return selectAll($this->table);
        $st = DB::pdo()->query("SELECT * FROM {$this->table}");
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array {
        if (function_exists('selectOne')) return selectOne($this->table, ['id'=>$id]) ?: null;
        $st = DB::pdo()->prepare("SELECT * FROM {$this->table} WHERE id=? LIMIT 1");
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function create(array $data): int {
        if (function_exists('create')) return (int)create($this->table, $data);
        $cols = array_keys($data);
        $ph   = array_map(fn($c)=>":$c",$cols);
        $sql  = "INSERT INTO {$this->table} (".implode(',',$cols).") VALUES (".implode(',',$ph).")";
        $st = DB::pdo()->prepare($sql);
        $st->execute($data);
        return (int)DB::pdo()->lastInsertId();
    }

    public function update(int $id, array $data): int {
        if (function_exists('update')) return (int)update($this->table, $id, $data);
        $sets=[]; foreach($data as $k=>$v){ $sets[]="$k=:$k"; }
        $sql="UPDATE {$this->table} SET ".implode(',',$sets)." WHERE id=:id";
        $data['id']=$id;
        $st=DB::pdo()->prepare($sql);
        $st->execute($data);
        return $st->rowCount();
    }

    public function delete(int $id): int {
        if (function_exists('delete')) return (int)delete($this->table, $id);
        $st=DB::pdo()->prepare("DELETE FROM {$this->table} WHERE id=?");
        $st->execute([$id]);
        return $st->rowCount();
    }
}
