<?php
// This file only fetches items and returns them as JSON.
// It does not output any HTML directly, except for what we build.

session_start();
include_once 'connection.php';

// --- 1. GET PARAMETERS ---
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
  $page = 1;
}

$items_per_page = 20; // Match this with your shop.php
$offset = ($page - 1) * $items_per_page;

// --- 2. BUILD SQL QUERY DYNAMICALLY ---
$sql = "SELECT * FROM items";
$count_sql = "SELECT COUNT(*) FROM items";
$params = []; // This will be an associative array
$where_clauses = [];

// Add search clause
if (!empty($search)) {
  $where_clauses[] = "(name LIKE :search_name OR description LIKE :search_desc)";
  $params[':search_name'] = "%$search%";
  $params[':search_desc'] = "%$search%";
}

// ADD THIS NEW BLOCK
// Add category clause
if ($category !== 'all') {
  $where_clauses[] = "category = :category";
  $params[':category'] = $category;
}

// This part stays the same
if (count($where_clauses) > 0) {
  $sql .= " WHERE " . implode(' AND ', $where_clauses);
  $count_sql .= " WHERE " . implode(' AND ', $where_clauses);
}

// Add sorting
switch ($sort) {
    case 'price-low-high':
      $sql .= " ORDER BY price ASC";
      break;
    case 'price-high-low':
      $sql .= " ORDER BY price DESC";
      break;
    default:
      // For name sorting, we'll do natural sort in PHP after fetching
      // This ensures proper numeric sorting (e.g., [1], [2], [10] instead of [1], [10], [2])
      $sql .= " ORDER BY name ASC"; // We'll re-sort in PHP for natural order
      break;
}

// --- 3. GET TOTAL COUNT FOR PAGINATION ---
try {
  $count_stmt = $conn->prepare($count_sql);
  // This execute() call works perfectly with our associative array
  $count_stmt->execute($params); 
  $total_items = $count_stmt->fetchColumn();
} catch (Exception $e) {
  $total_items = 0;
}
$total_pages = ceil($total_items / $items_per_page);

// --- 4. GET ITEMS FOR CURRENT PAGE ---
// For natural name sorting, we need to fetch all items, sort them, then paginate
$need_natural_sort = ($sort === 'default' || empty($sort));

if ($need_natural_sort) {
  // Fetch ALL matching items first (without LIMIT/OFFSET)
  try {
    $stmt = $conn->prepare($sql);
    
    // Bind all the search parameters (if any) from our $params array
    foreach ($params as $key => $value) {
      $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $all_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Apply natural sorting
    usort($all_items, function($a, $b) {
      // Use natural sort comparison for names (handles [1], [2], [10] correctly)
      return strnatcasecmp($a['name'], $b['name']);
    });
    
    // Now apply pagination to the sorted array
    $items = array_slice($all_items, $offset, $items_per_page);
    
  } catch (Exception $e) {
    $items = [];
  }
} else {
  // For price sorting, use SQL sorting with LIMIT/OFFSET (more efficient)
  $sql .= " LIMIT :limit OFFSET :offset";
  
  try {
    $stmt = $conn->prepare($sql);
    
    // Bind all the search parameters (if any) from our $params array
    foreach ($params as $key => $value) {
      $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    
    // Bind pagination params as INTs
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
  } catch (Exception $e) {
    $items = [];
  }
}

// --- 5. BUILD HTML TO SEND BACK ---
$items_html = '';
if (count($items) > 0) {
  foreach ($items as $item) {
    // Get stock by paper type from item_stock table
    $stockStmt = $conn->prepare("SELECT paper_type, stock FROM item_stock WHERE item_id = :item_id");
    $stockStmt->bindParam(':item_id', $item['id'], PDO::PARAM_INT);
    $stockStmt->execute();
    $stockRows = $stockStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Build stock JSON
    $stockByPaperType = [];
    $totalStock = 0;
    foreach ($stockRows as $row) {
      $stockByPaperType[$row['paper_type']] = (int)$row['stock'];
      $totalStock += (int)$row['stock'];
    }
    
    $stock = $totalStock;
    $stock_json = json_encode($stockByPaperType);
    $is_out_of_stock = $stock <= 0;
    $out_of_stock_class = $is_out_of_stock ? 'out-of-stock' : '';
    $disabled_attr = $is_out_of_stock ? 'disabled' : '';
    $onsubmit_attr = $is_out_of_stock ? 'onsubmit="return false;"' : '';
    
    // This is the same card HTML from your shop.php with modal data attributes
    $items_html .= '
    <div class="card ' . $out_of_stock_class . '" 
         data-item-id="' . htmlspecialchars($item['id']) . '"
         data-item-name="' . htmlspecialchars($item['name']) . '"
         data-item-price="' . htmlspecialchars($item['price']) . '"
         data-item-image="' . htmlspecialchars($item['image']) . '"
         data-item-description="' . htmlspecialchars($item['description']) . '"
         data-item-stock="' . $stock . '"
         data-item-stock-json="' . htmlspecialchars($stock_json) . '">
      <div class="card-img">
        <div class="img">
          <img src="images/' . htmlspecialchars($item['image']) . '" alt="' . htmlspecialchars($item['name']) . '">
        </div>
      </div>
      <div class="card-title">' . htmlspecialchars($item['name']) . '</div>
      <div class="card-subtitle">' . htmlspecialchars($item['description']) . '</div>
      <hr class="card-divider">
      <div class="card-footer">
        <div class="card-price"><span>₱</span>' . htmlspecialchars($item['price']) . '</div>
        <form method="POST" action="add_to_cart" class="add-to-cart-form" ' . $onsubmit_attr . '>
          <input type="hidden" name="item_id" value="' . htmlspecialchars($item['id']) . '">
          <input type="hidden" name="item_name" value="' . htmlspecialchars($item['name']) . '">
          <input type="hidden" name="item_price" value="' . htmlspecialchars($item['price']) . '">
          <input type="hidden" name="paper_type" value="Vinyl">
          <button type="submit" class="card-btn" ' . $disabled_attr . '>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
              <path d="m397.78 316h-205.13a15 15 0 0 1 -14.65-11.67l-34.54-150.48a15 15 0 0 1 14.62-18.36h274.27a15 15 0 0 1 14.65 18.36l-34.6 150.48a15 15 0 0 1 -14.62 11.67zm-193.19-30h181.25l27.67-120.48h-236.6z"></path>
              <path d="m222 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z"></path>
              <path d="m368.42 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z"></path>
              <path d="m158.08 165.49a15 15 0 0 1 -14.23-10.26l-25.71-77.23h-47.44a15 15 0 1 1 0-30h58.3a15 15 0 0 1 14.23 10.26l29.13 87.49a15 15 0 0 1 -14.23 19.74z"></path>
            </svg>
          </button>
        </form>
      </div>
    </div>';
  }
} else {
  $items_html = '<p style="width: 100%; text-align: center; font-size: 1.2rem; font-family: \'Nunito\', sans-serif;">No stickers found matching your search.</p>';
}

$pagination_html = '';
if ($total_pages > 1) {
    $pagination_html .= '<nav class="pagination-outer" aria-label="Page navigation"><ul class="pagination">';
    // Previous button
    $disabled = ($page <= 1) ? 'disabled' : '';
    $prev_page = $page - 1;
    // We must ensure the search/sort/category parameters are kept in the pagination links!
    $category_encoded = urlencode($category);
    $search_encoded = urlencode($search);
    $pagination_html .= "<li class='page-item $disabled'><a href='?page=$prev_page&sort=$sort&search=$search_encoded&category=$category_encoded' class='page-link' aria-label='Previous'><span aria-hidden='true'>«</span></a></li>";
    
    // Page numbers
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i == $page) ? 'active' : '';
        $pagination_html .= "<li class='page-item $active'><a class='page-link' href='?page=$i&sort=$sort&search=$search_encoded&category=$category_encoded'>$i</a></li>";
    }

    // Next button
    $disabled = ($page >= $total_pages) ? 'disabled' : '';
    $next_page = $page + 1;
    $pagination_html .= "<li class='page-item $disabled'><a href='?page=$next_page&sort=$sort&search=$search_encoded&category=$category_encoded' class='page-link' aria-label='Next'><span aria-hidden='true'>»</span></a></li>";
    
    $pagination_html .= '</ul></nav>';
}

// --- 6. SEND JSON RESPONSE ---
header('Content-Type: application/json');
echo json_encode([
  'items_html' => $items_html,
  'pagination_html' => $pagination_html
]);

?>