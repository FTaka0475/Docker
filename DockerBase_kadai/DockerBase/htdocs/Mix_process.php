<?php
// このファイルは「道具箱」なので、呼び出されて動く前提です
function executeMixing($uid, $baseMasterId, $matMasterId) {
    try {
        $pdo_sub = getSubDb();
        $pdo_master = getMasterDb();
        $pdo_sub->beginTransaction();

        // 1. ベース個体の特定
        $stmt = $pdo_sub->prepare("SELECT id FROM users_cards WHERE user_id = :u AND card_id = :m LIMIT 1");
        $stmt->execute([':u' => $uid, ':m' => $baseMasterId]);
        $base = $stmt->fetch();
        if (!$base) throw new Exception("ベースカードがありません");

        // 2. 素材個体の特定
        $stmt = $pdo_sub->prepare("SELECT id FROM users_cards WHERE user_id = :u AND card_id = :m AND id != :b LIMIT 1");
        $stmt->execute([':u' => $uid, ':m' => $matMasterId, ':b' => $base['id']]);
        $mat = $stmt->fetch();
        if (!$mat) throw new Exception("素材カードが足りません");

        // 3. 進化先の特定
        $stmt = $pdo_master->prepare("SELECT next_id FROM cards WHERE id = :m");
        $stmt->execute([':m' => $baseMasterId]);
        $nextId = $stmt->fetchColumn();
        if (!$nextId) throw new Exception("進化先がありません");

        // 4. DB更新
        $pdo_sub->prepare("UPDATE users_cards SET card_id = :n WHERE id = :i")->execute([':n' => $nextId, ':i' => $base['id']]);
        $pdo_sub->prepare("DELETE FROM users_cards WHERE id = :i")->execute([':i' => $mat['id']]);

        $pdo_sub->commit();
        
        // 進化後の名前を返り値として返す
        $stmt = $pdo_master->prepare("SELECT name FROM cards WHERE id = :i");
        $stmt->execute([':i' => $nextId]);
        return ['success' => true, 'new_name' => $stmt->fetchColumn()];

    } catch (Exception $e) {
        if (isset($pdo_sub)) $pdo_sub->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}