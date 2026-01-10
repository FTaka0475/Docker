<?php
function executeMixing($uid, $baseMid, $materialList) {
    // 【案A】の設定
    $rateTable = [
        1 => [1 => 100],
        2 => [2 => 100, 1 => 50],
        3 => [3 => 100, 2 => 25, 1 => 5],
        4 => [4 => 100, 3 => 10, 2 => 2, 1 => 1],
    ];

    try {
        $pdo_sub = getSubDb();
        $pdo_master = getMasterDb();
        $pdo_sub->beginTransaction();

        // ベース個体の特定
        $stmt = $pdo_sub->prepare("SELECT id FROM users_cards WHERE user_id = :u AND card_id = :m LIMIT 1");
        $stmt->execute([':u' => $uid, ':m' => $baseMid]);
        $base = $stmt->fetch();
        
        // rarity を rare に変更
        $bInfo = $pdo_master->query("SELECT rare, next_id FROM cards WHERE id = $baseMid")->fetch();

        if (!$base || !$bInfo) throw new Exception("カードデータが見つかりません");

        $totalRate = 0;
        $materialsToDelete = [];

        foreach ($materialList as $mMid => $qty) {
            $qty = (int)$qty;
            if ($qty <= 0) continue;

            // rarity を rare に変更
            $mRare = $pdo_master->query("SELECT rare FROM cards WHERE id = $mMid")->fetchColumn();
            
            // 確率計算（ベースのrareと素材のrareで比較）
            $ratePer = $rateTable[$bInfo['rare']][$mRare] ?? ($mRare >= $bInfo['rare'] ? 100 : 0);
            $totalRate += ($ratePer * $qty);

            // 削除対象の個別IDを取得
            $findStmt = $pdo_sub->prepare("SELECT id FROM users_cards WHERE user_id = :u AND card_id = :m AND id != :bid LIMIT $qty");
            $findStmt->execute([':u' => $uid, ':m' => $mMid, ':bid' => $base['id']]);
            $materialsToDelete = array_merge($materialsToDelete, $findStmt->fetchAll(PDO::FETCH_COLUMN));
        }

        $finalRate = min($totalRate, 100);
        $isSuccess = (rand(1, 100) <= $finalRate);

        // 素材は成否に関わらず必ず消す
        foreach ($materialsToDelete as $delId) {
            $pdo_sub->prepare("DELETE FROM users_cards WHERE id = :i")->execute([':i' => $delId]);
        }

        if ($isSuccess) {
            $pdo_sub->prepare("UPDATE users_cards SET card_id = :n WHERE id = :i")
                    ->execute([':n' => $bInfo['next_id'], ':i' => $base['id']]);
            $pdo_sub->commit();
            $newName = $pdo_master->query("SELECT name FROM cards WHERE id = ".$bInfo['next_id'])->fetchColumn();
            return ['status' => 'success', 'new_name' => $newName, 'rate' => $finalRate];
        } else {
            $pdo_sub->commit(); 
            return ['status' => 'fail', 'rate' => $finalRate];
        }
    } catch (Exception $e) {
        if (isset($pdo_sub)) $pdo_sub->rollBack();
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}