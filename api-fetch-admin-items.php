<?php
// API endpoint for fetching admin table items dynamically
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include 'connection.php';

// Get parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$dir = isset($_GET['dir']) && in_array($_GET['dir'], ['asc', 'desc']) ? $_GET['dir'] : 'desc';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

$items_per_page = 20;
$offset = ($page - 1) * $items_per_page;

// Build SQL query
$sql = "SELECT * FROM items";
$count_sql = "SELECT COUNT(*) FROM items";
$params = [];
$where_clauses = [];

if (!empty($search)) {
    $where_clauses[] = "(name LIKE :search OR description LIKE :search)";
    $params[':search'] = "%$search%";
}

if (count($where_clauses) > 0) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
    $count_sql .= " WHERE " . implode(' AND ', $where_clauses);
}

// Get total count
try {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->execute($params);
    $total_items = $count_stmt->fetchColumn();
} catch (Exception $e) {
    $total_items = 0;
}
$total_pages = ceil($total_items / $items_per_page);

// Add sorting and pagination
$sql .= " ORDER BY $sort $dir";
$sql .= " LIMIT :limit OFFSET :offset";

// Fetch items
try {
    $stmt = $conn->prepare($sql);
    $params[':limit'] = $items_per_page;
    $params[':offset'] = $offset;
    
    foreach ($params as $key => &$value) {
        if ($key == ':limit' || $key == ':offset') {
            $stmt->bindParam($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindParam($key, $value, PDO::PARAM_STR);
        }
    }
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $items = [];
}

// Build table rows HTML
$table_rows_html = '';
if (count($items) > 0) {
    foreach ($items as $item) {
        $table_rows_html .= '
        <tr>
            <td>
                <input type="checkbox" name="item_ids[]" value="' . htmlspecialchars($item['id']) . '">
            </td>
            <td><img src="images/' . htmlspecialchars($item['image']) . '" alt="' . htmlspecialchars($item['name']) . '"></td>
            <td>' . htmlspecialchars($item['name']) . '</td>
            <td>' . htmlspecialchars($item['description']) . '</td>
            <td>₱' . number_format($item['price'], 2) . '</td>
            <td>' . number_format($item['stock'] ?? 0) . '</td>
            <td>' . htmlspecialchars($item['category']) . '</td>
            <td>' . htmlspecialchars($item['page']) . '</td>
            <td>
                <div class="action-buttons">
                    <button type="button" class="btn-edit" onclick="openEditModal(' . $item['id'] . ')" style="background-color: #4CAF50; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin-right: 5px;">Update</button>
                    <button type="button" class="btn-delete" onclick="deleteItem(' . $item['id'] . ')" style="background-color: #f44336; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Delete</button>
                </div>
            </td>
        </tr>';
    }
} else {
    $table_rows_html = '<tr><td colspan="9" style="text-align: center; padding: 20px;">No items found matching your search.</td></tr>';
}

// Build pagination HTML
$pagination_html = '';
if ($total_pages > 1) {
    $pagination_html .= '<ul class="pagination">';
    
    // Previous button
    if ($page > 1) {
        $pagination_html .= '<li class="page-item"><a class="page-link" href="#" data-page="' . ($page - 1) . '">«</a></li>';
    } else {
        $pagination_html .= '<li class="page-item disabled"><span class="page-link">«</span></li>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $page) {
            $pagination_html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $pagination_html .= '<li class="page-item"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
        }
    }
    
    // Next button
    if ($page < $total_pages) {
        $pagination_html .= '<li class="page-item"><a class="page-link" href="#" data-page="' . ($page + 1) . '">»</a></li>';
    } else {
        $pagination_html .= '<li class="page-item disabled"><span class="page-link">»</span></li>';
    }
    
    $pagination_html .= '</ul>';
}

// Build header HTML with sort indicators
$next_dir = ($dir == 'asc') ? 'desc' : 'asc';
$header_html = '
<tr>
    <th><input type="checkbox" id="select-all"></th>
    <th class="sortable">
        <a href="#" data-sort="image" data-dir="' . $next_dir . '">Image' . 
        ($sort == 'image' ? ($dir == 'asc' ? ' <span class="arrow">▲</span>' : ' <span class="arrow">▼</span>') : '') . 
        '</a>
    </th>
    <th class="sortable">
        <a href="#" data-sort="name" data-dir="' . $next_dir . '">Name' . 
        ($sort == 'name' ? ($dir == 'asc' ? ' <span class="arrow">▲</span>' : ' <span class="arrow">▼</span>') : '') . 
        '</a>
    </th>
    <th class="sortable">
        <a href="#" data-sort="description" data-dir="' . $next_dir . '">Description' . 
        ($sort == 'description' ? ($dir == 'asc' ? ' <span class="arrow">▲</span>' : ' <span class="arrow">▼</span>') : '') . 
        '</a>
    </th>
    <th class="sortable">
        <a href="#" data-sort="price" data-dir="' . $next_dir . '">Price' . 
        ($sort == 'price' ? ($dir == 'asc' ? ' <span class="arrow">▲</span>' : ' <span class="arrow">▼</span>') : '') . 
        '</a>
    </th>
    <th class="sortable">
        <a href="#" data-sort="stock" data-dir="' . $next_dir . '">Stock' . 
        ($sort == 'stock' ? ($dir == 'asc' ? ' <span class="arrow">▲</span>' : ' <span class="arrow">▼</span>') : '') . 
        '</a>
    </th>
    <th class="sortable">
        <a href="#" data-sort="category" data-dir="' . $next_dir . '">Category' . 
        ($sort == 'category' ? ($dir == 'asc' ? ' <span class="arrow">▲</span>' : ' <span class="arrow">▼</span>') : '') . 
        '</a>
    </th>
    <th class="sortable">
        <a href="#" data-sort="page" data-dir="' . $next_dir . '">Page' . 
        ($sort == 'page' ? ($dir == 'asc' ? ' <span class="arrow">▲</span>' : ' <span class="arrow">▼</span>') : '') . 
        '</a>
    </th>
    <th>Actions</th>
</tr>';

// Send JSON response
header('Content-Type: application/json');
echo json_encode([
    'table_rows_html' => $table_rows_html,
    'pagination_html' => $pagination_html,
    'header_html' => $header_html,
    'current_page' => $page,
    'total_pages' => $total_pages
]);

?>

