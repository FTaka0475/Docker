<?php
function executeMixing($uid, $baseMid, $materialList) {
    $rateTable = [
        1 => [1 => 100], 2 => [2 => 100, 1 => 50],
        3 => [3 => 100, 2 => 25, 1 => 5], 4 => [4 => 100, 3 => 10, 2 => 2, 1 => 1]
    ];

    try {
        $pdo_sub = getSubDb();
        $pdo_master = getMasterDb();
        $pdo_sub->beginTransaction();

        $stmt = $pdo_sub->prepare("SELECT id FROM users_cards WHERE user_id = :u AND card_id = :m LIMIT 1");
        $stmt->execute([':u' => $uid, ':m' => $baseMid]);
        $base = $stmt->fetch();
        $bInfo = $pdo_master->query("SELECT rare, next_id FROM cards WHERE id = $baseMid")->fetch();

        if (!$base || !$bInfo) throw new Exception("データエラー");

        $totalRate = 0;
        $materialsToDelete = [];

        foreach ($materialList as $mMid => $qty) {
            $qty = (int)$qty; if ($qty <= 0) continue;
            $mRare = $pdo_master->query("SELECT rare FROM cards WHERE id = $mMid")->fetchColumn();
            $ratePer = $rateTable[$bInfo['rare']][$mRare] ?? ($mRare >= $bInfo['rare'] ? 100 : 0);
            $totalRate += ($ratePer * $qty);

            $findStmt = $pdo_sub->prepare("SELECT id FROM users_cards WHERE user_id = :u AND card_id = :m AND id != :bid LIMIT $qty");
            $findStmt->execute([':u' => $uid, ':m' => $mMid, ':bid' => $base['id']]);
            $materialsToDelete = array_merge($materialsToDelete, $findStmt->fetchAll(PDO::FETCH_COLUMN));
        }

        $finalRate = min($totalRate, 100);
        $isSuccess = (rand(1, 100) <= $finalRate);

        foreach ($materialsToDelete as $delId) {
            $pdo_sub->prepare("DELETE FROM users_cards WHERE id = :i")->execute([':i' => $delId]);
        }

        if ($isSuccess) {
            $pdo_sub->prepare("UPDATE users_cards SET card_id = :n WHERE id = :i")->execute([':n' => $bInfo['next_id'], ':i' => $base['id']]);
            $pdo_sub->commit();
            
            // 進化後のカード名と画像をJOINで取得
            $stmtNext = $pdo_master->prepare("
                SELECT c.name, img.image_path 
                FROM cards c 
                LEFT JOIN images img ON c.id = img.card_id 
                WHERE c.id = :id
            ");
            $stmtNext->execute([':id' => $bInfo['next_id']]);
            $nextCard = $stmtNext->fetch();

            return ['status' => 'success', 'new_name' => $nextCard['name'], 'new_image' => $nextCard['image_path'], 'rate' => $finalRate];
        } else {
            $pdo_sub->commit();
            return ['status' => 'fail', 'rate' => $finalRate];
        }
    } catch (Exception $e) {
        if (isset($pdo_sub)) $pdo_sub->rollBack();
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}