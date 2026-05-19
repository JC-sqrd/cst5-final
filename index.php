<?php 
require 'db.php'; 

// 1. Handle creating a brand new board
if (isset($_POST['create_board']) && !empty(trim($_POST['board_title']))) {
    $stmt = $pdo->prepare("INSERT INTO moodboards (title) VALUES (?)");
    $stmt->execute([trim($_POST['board_title'])]);
    $newId = $pdo->lastInsertId();
    header("Location: index.php?board_id=" . $newId);
    exit;
}

// 2. Fetch all available boards for the sidebar menu
$boards = $pdo->query("SELECT * FROM moodboards ORDER BY created_at DESC")->fetchAll();

// 3. Determine which board is currently active
$currentBoardId = isset($_GET['board_id']) ? intval($_GET['board_id']) : null;
if (!$currentBoardId && !empty($boards)) {
    $currentBoardId = $boards[0]['id']; 
}

// 4. Fetch items just for the active board
$items = [];
if ($currentBoardId) {
    $stmt = $pdo->prepare("SELECT * FROM moodboard_items WHERE moodboard_id = ?");
    $stmt->execute([$currentBoardId]);
    $items = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoodSpark — Ignite Your Inspiration</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; height: 100vh; background: #f4f4f9; }
        
        /* Sidebar Layout */
        #sidebar { width: 260px; background: #1a1a24; color: #fff; padding: 20px; display: flex; flex-direction: column; gap: 20px; box-sizing: border-box; border-right: 1px solid #2d2d3d; }
        
        /* App Branding */
        .brand { font-size: 1.5rem; font-weight: bold; letter-spacing: -0.5px; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
        .brand span { color: #ff9f43; } /* The Spark orange color */

        #sidebar form input { width: 100%; padding: 10px; margin-bottom: 8px; border-radius: 6px; border: 1px solid #2d2d3d; background: #252538; color: #fff; box-sizing: border-box;}
        #sidebar form input::placeholder { color: #777; }
        #sidebar form button { width: 100%; padding: 10px; background: #ff9f43; color: #1a1a24; font-weight: bold; border: none; border-radius: 6px; cursor: pointer; transition: background 0.2s; }
        #sidebar form button:hover { background: #ffb366; }
        
        .board-list { list-style: none; padding: 0; margin: 0; overflow-y: auto; flex: 1; }
        .board-list li a { display: block; padding: 12px; color: #a0a0b0; text-decoration: none; border-radius: 6px; margin-bottom: 5px; transition: all 0.2s; }
        .board-list li a:hover { background: #252538; color: #fff; }
        .board-list li.active a { background: #ff9f43; color: #1a1a24; font-weight: bold; }

        /* Main Workspace Layout */
        #workspace { flex: 1; display: flex; flex-direction: column; }
        header { background: #fff; color: #1a1a24; padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e1e1e9; }
        header h2 { margin: 0; font-size: 1.35rem; font-weight: 600; }
        header button { padding: 10px 20px; background: #1a1a24; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; transition: background 0.2s; }
        header button:hover { background: #33334d; }

        #canvas { flex: 1; position: relative; background: #fafafa; overflow: hidden; background-image: radial-gradient(#e1e1e9 1.5px, transparent 1.5px); background-size: 24px 24px; }
        #canvas.drag-hover { background-color: #fff9f2; background-image: radial-gradient(#ff9f43 1.5px, transparent 1.5px); }
        
        .board-item { position: absolute; cursor: move; user-select: none; box-shadow: 0 10px 25px rgba(0,0,0,0.08); border: 2px solid transparent; box-sizing: border-box; background: #fff; border-radius: 4px; padding: 4px; }
        .board-item:hover { border-color: #ff9f43; }
        .board-item img { width: 100%; height: 100%; object-fit: contain; display: block; pointer-events: none; border-radius: 2px; }
        .resize-handle { width: 12px; height: 12px; background: #ff9f43; position: absolute; right: -4px; bottom: -4px; cursor: nwse-resize; border-radius: 3px; display: none; z-index: 10; }
        .board-item:hover .resize-handle { display: block; }
        .instructions { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #999; font-size: 1.1rem; pointer-events: none; text-align: center; line-height: 1.6; }
    </style>
</head>
<body>

<div id="sidebar">
    <div class="brand">Mood<span>Spark</span></div>
    
    <form action="index.php" method="POST">
        <input type="text" name="board_title" placeholder="New board title..." required>
        <button type="submit" name="create_board">+ Create New Board</button>
    </form>
    
    <ul class="board-list">
        <?php foreach ($boards as $b): ?>
            <li class="<?= $b['id'] == $currentBoardId ? 'active' : '' ?>">
                <a href="index.php?board_id=<?= $b['id'] ?>"><?= htmlspecialchars($b['title']) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<div id="workspace">
    <header>
        <h2>
            <?php 
            if ($currentBoardId) {
                $currentTitle = array_column($boards, 'title', 'id')[$currentBoardId];
                echo htmlspecialchars($currentTitle);
            } else {
                echo "Welcome to MoodSpark";
            }
            ?>
        </h2>
        <?php if ($currentBoardId): ?>
            <button id="save-layout">Save Changes</button>
        <?php endif; ?>
    </header>

    <div id="canvas">
        <?php if (!$currentBoardId): ?>
            <div class="instructions">Spark something new.<br>Create or select a board from the sidebar to begin.</div>
        <?php else: ?>
            <div class="instructions" id="hint" style="<?= !empty($items) ? 'display:none;' : '' ?>">Drop images anywhere onto the canvas to add them.</div>
            <?php foreach ($items as $item): ?>
                <div class="board-item" id="<?= $item['id'] ?>" style="left: <?= $item['x'] ?>px; top: <?= $item['y'] ?>px; width: <?= $item['w'] ?>px; height: <?= $item['h'] ?>px;">
                    <img src="<?= $item['src'] ?>" alt="Moodboard item">
                    <div class="resize-handle"></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    const boardId = <?= json_encode($currentBoardId) ?>;
    const canvas = document.getElementById('canvas');
    const hint = document.getElementById('hint');
    let activeItem = null;
    let isResizing = false;
    let startX, startY, startWidth, startHeight, startLeft, startTop;

    if (!boardId) {
        throw new Error("No active moodboard selected.");
    }

    // 1. Image Drop Handler
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        canvas.addEventListener(eventName, (e) => e.preventDefault(), false);
    });

    canvas.addEventListener('dragover', () => canvas.classList.add('drag-hover'));
    canvas.addEventListener('dragleave', () => canvas.classList.remove('drag-hover'));

    canvas.addEventListener('drop', (e) => {
        canvas.classList.remove('drag-hover');
        if(hint) hint.style.display = 'none';

        const files = e.dataTransfer.files;
        if (files.length === 0) return;

        const rect = canvas.getBoundingClientRect();
        const dropX = e.clientX - rect.left;
        const dropY = e.clientY - rect.top;

        Array.from(files).forEach(file => {
            if (!file.type.startsWith('image/')) return;

            const formData = new FormData();
            formData.append('image', file);
            formData.append('moodboard_id', boardId);
            formData.append('x', dropX);
            formData.append('y', dropY);

            fetch('save.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    createBoardItem(data.id, data.src, data.x, data.y, data.w, data.h);
                }
            });
        });
    });

    function createBoardItem(id, src, x, y, w, h) {
        const div = document.createElement('div');
        div.className = 'board-item';
        div.id = id;
        div.style.left = x + 'px';
        div.style.top = y + 'px';
        div.style.width = w + 'px';
        div.style.height = h + 'px';

        const img = document.createElement('img');
        img.src = src;

        const handle = document.createElement('div');
        handle.className = 'resize-handle';
        
        div.appendChild(img);
        div.appendChild(handle);
        canvas.appendChild(div);
    }

    // 2. Drag & Resize Controls
    canvas.addEventListener('mousedown', (e) => {
        if (e.target.classList.contains('resize-handle')) {
            isResizing = true;
            activeItem = e.target.parentElement;
            startX = e.clientX;
            startY = e.clientY;
            startWidth = parseInt(document.defaultView.getComputedStyle(activeItem).width, 10);
            startHeight = parseInt(document.defaultView.getComputedStyle(activeItem).height, 10);
            e.stopPropagation();
            return;
        }

        if (e.target.classList.contains('board-item')) {
            isResizing = false;
            activeItem = e.target;
            startLeft = e.clientX - activeItem.offsetLeft;
            startTop = e.clientY - activeItem.offsetTop;
            activeItem.style.zIndex = 1000;
        }
    });

    document.addEventListener('mousemove', (e) => {
        if (!activeItem) return;

        if (isResizing) {
            const newWidth = startWidth + (e.clientX - startX);
            const newHeight = startHeight + (e.clientY - startY);
            if (newWidth > 30) activeItem.style.width = newWidth + 'px';
            if (newHeight > 30) activeItem.style.height = newHeight + 'px';
        } else {
            let x = e.clientX - startLeft;
            let y = e.clientY - startTop;
            if (x < 0) x = 0;
            if (y < 0) y = 0;
            activeItem.style.left = x + 'px';
            activeItem.style.top = y + 'px';
        }
    });

    document.addEventListener('mouseup', () => {
        if (activeItem) {
            activeItem.style.zIndex = '';
            activeItem = null;
            isResizing = false;
        }
    });

    // 3. Database Sync
    document.getElementById('save-layout').addEventListener('click', () => {
        const items = document.querySelectorAll('.board-item');
        const layoutData = [];

        items.forEach(item => {
            layoutData.push({
                id: item.id,
                src: item.querySelector('img').getAttribute('src'),
                x: parseInt(item.style.left) || 0,
                y: parseInt(item.style.top) || 0,
                w: parseInt(item.style.width) || 150,
                h: parseInt(item.style.height) || 150
            });
        });

        fetch('save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ moodboard_id: boardId, layout: layoutData })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) alert('Layout updates saved to MoodSpark database!');
        });
    });
</script>
</body>
</html>