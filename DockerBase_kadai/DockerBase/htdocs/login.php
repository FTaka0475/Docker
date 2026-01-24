<?php
require_once 'common.php';

// ユーザーが選択された時の処理
if (isset($_GET['login_id'])) {
    try {
        $pdo = getSubDb();
        $stmt = $pdo->prepare("SELECT * FROM users_name WHERE id = :id");
        $stmt->execute([':id' => $_GET['login_id']]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: My_card.php");
            exit;
        }
    } catch (Exception $e) {
        $error = "ログインに失敗しました。";
    }
}

// 登録されているユーザー一覧を取得
try {
    $pdo = getSubDb();
    $users = $pdo->query("SELECT * FROM users_name ORDER BY id DESC")->fetchAll();
} catch (Exception $e) {
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン / ユーザー選択</title>
    <style>
        body { font-family: sans-serif; background: #fffcf0; text-align: center; color: #5d4037; padding-top: 50px; }
        .container { background: white; padding: 30px; border-radius: 20px; display: inline-block; box-shadow: 0 4px 15px rgba(0,0,0,0.1); min-width: 300px; }
        .user-list { margin: 20px 0; display: flex; flex-direction: column; gap: 10px; }
        .user-btn { display: block; padding: 15px; background: #f8f9fa; color: #5d4037; text-decoration: none; border-radius: 10px; border: 2px solid #ffcb81; font-weight: bold; transition: 0.2s; }
        .user-btn:hover { background: #ffcb81; color: white; }
        .create-btn { display: inline-block; margin-top: 20px; padding: 15px 30px; background: #ff9f43; color: white; text-decoration: none; border-radius: 10px; font-weight: bold; box-shadow: 0 4px 0 #e67e22; }
    </style>
</head>
<body>

<div class="container">
    <h1>🐾 誰？</h1>

    <div class="user-list">
        <?php foreach ($users as $user): ?>
            <a href="login.php?login_id=<?= $user['id'] ?>" class="user-btn">
                👤 <?= htmlspecialchars($user['name']) ?> さん
            </a>
        <?php endforeach; ?>
        
        <?php if (empty($users)): ?>
            <p>まだ登録されている飼い主がいません。</p>
        <?php endif; ?>
    </div>

    <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

    <a href="Create_user.php" class="create-btn">➕ 新しい飼い主を作る</a>
</div>

</body>
</html>