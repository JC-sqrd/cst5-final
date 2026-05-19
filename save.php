<?php
header('Content-Type: application/json');
require 'db.php';

// 1. Catch Drop Upload Event and Write to DB
if (isset($_FILES['image']) && isset($_POST['moodboard_id'])) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = time() . '_' . basename($_FILES['image']['name']);
    $targetPath = $uploadDir . $fileName;
    $boardId = intval($_POST['moodboard_id']);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $itemId = 'item_' . time() . '_' . rand(100, 999);
        $x = isset($_POST['x']) ? intval($_POST['x']) : 50;
        $y = isset($_POST['y']) ? intval($_POST['y']) : 50;
        $w = 150;
        $h = 150;

        // Insert new entry row mapped to the distinct board ID
        $stmt = $pdo->prepare("INSERT INTO moodboard_items (id, moodboard_id, src, x, y, w, h) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$itemId, $boardId, $targetPath, $x, $y, $w, $h]);
        
        echo json_encode([
            'success' => true, 'id' => $itemId, 'src' => $targetPath, 'x' => $x, 'y' => $y, 'w' => $w, 'h' => $h
        ]);
        exit;
    }
}

// 2. Catch Canvas Layout Sync Request
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if ($contentType === 'application/json') {
    $content = trim(file_get_contents("php://input"));
    $decoded = json_decode($content, true);

    if (isset($decoded['layout']) && isset($decoded['moodboard_id'])) {
        $boardId = intval($decoded['moodboard_id']);
        
        // Use an atomic transaction loop to wipe old positions and insert the updated set safely
        $pdo->beginTransaction();
        try {
            // Drop current state inside this specific board
            $deleteStmt = $pdo->prepare("DELETE FROM moodboard_items WHERE moodboard_id = ?");
            $deleteStmt->execute([$boardId]);

            // Repopulate with new location properties
            $insertStmt = $pdo->prepare("INSERT INTO moodboard_items (id, moodboard_id, src, x, y, w, h) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($decoded['layout'] as $item) {
                $insertStmt->execute([
                    $item['id'], $boardId, $item['src'], $item['x'], $item['y'], $item['w'], $item['h']
                ]);
            }
            
            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid Pipeline Request']);