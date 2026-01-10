<?php
require_once 'common.php';
require_once 'Mix_process.php'; // 道具（箱）を読み込む
$current_user_id = $_SESSION['user_id'];

// 注文（POSTデータ）を受け取る
$base_mid = $_POST['base_master_id'] ?? null;
$mat_mid = $_POST['material_master_id'] ?? null;

// 道具（関数）を使って合成を実行
$result = executeMixing($current_user_id, $base_mid, $mat_mid);
?>

<!DOCTYPE html>
<html lang="ja">
<head><meta charset="UTF-8"><title>合成結果</title></head>
<body style="text-align:center; padding-top:50px; font-family:sans-serif;">
    <?php if ($result['success']): ?>
        <h1 style="color:green;">🎉 強化成功！</h1>
        <p>「<?= htmlspecialchars($result['new_name']) ?>」を手に入れた！</p>
    <?php else: ?>
        <h1 style="color:red;">❌ エラー</h1>
        <p><?= htmlspecialchars($result['error']) ?></p>
    <?php endif; ?>
    <a href="My_card.php">戻る</a>
</body>
</html>