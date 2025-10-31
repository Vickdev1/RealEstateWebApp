<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Get single property
                $stmt = $db->prepare("
                    SELECT * FROM properties 
                    WHERE id = :id
                ");
                $stmt->bindParam(':id', $_GET['id']);
                $stmt->execute();
                $property = $stmt->fetch();
                
                if ($property) {
                    sendResponse(['success' => true, 'data' => $property]);
                } else {
                    sendResponse(['success' => false, 'message' => 'Property not found'], 404);
                }
            } else {
                // Get all properties with filters
                $query = "SELECT * FROM properties WHERE 1=1";
                $params = [];

                if (isset($_GET['type'])) {
                    $query .= " AND property_type = :type";
                    $params[':type'] = $_GET['type'];
                }

                if (isset($_GET['status'])) {
                    $query .= " AND status = :status";
                    $params[':status'] = $_GET['status'];
                } else {
                    $query .= " AND status = 'available'";
                }

                if (isset($_GET['featured'])) {
                    $query .= " AND featured = true";
                }

                if (isset($_GET['min_price'])) {
                    $query .= " AND price >= :min_price";
                    $params[':min_price'] = $_GET['min_price'];
                }

                if (isset($_GET['max_price'])) {
                    $query .= " AND price <= :max_price";
                    $params[':max_price'] = $_GET['max_price'];
                }

                $query .= " ORDER BY featured DESC, created_at DESC";

                if (isset($_GET['limit'])) {
                    $query .= " LIMIT :limit";
                    $params[':limit'] = (int)$_GET['limit'];
                }

                $stmt = $db->prepare($query);
                foreach ($params as $key => $value) {
                    if ($key === ':limit') {
                        $stmt->bindValue($key, $value, PDO::PARAM_INT);
                    } else {
                        $stmt->bindValue($key, $value);
                    }
                }
                $stmt->execute();
                $properties = $stmt->fetchAll();

                sendResponse(['success' => true, 'data' => $properties]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            $required = ['title', 'price', 'property_type'];
            $errors = validateRequired($data, $required);
            
            if (!empty($errors)) {
                sendResponse(['success' => false, 'errors' => $errors], 400);
            }

            $stmt = $db->prepare("
                INSERT INTO properties 
                (title, description, price, property_type, bedrooms, bathrooms, 
                square_feet, location, features, image_url, gallery_urls, status, featured)
                VALUES 
                (:title, :description, :price, :property_type, :bedrooms, :bathrooms,
                :square_feet, :location, :features, :image_url, :gallery_urls, :status, :featured)
                RETURNING id
            ");

            $features = isset($data['features']) ? '{' . implode(',', $data['features']) . '}' : null;
            $gallery = isset($data['gallery_urls']) ? '{' . implode(',', $data['gallery_urls']) . '}' : null;

            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':property_type', $data['property_type']);
            $stmt->bindParam(':bedrooms', $data['bedrooms']);
            $stmt->bindParam(':bathrooms', $data['bathrooms']);
            $stmt->bindParam(':square_feet', $data['square_feet']);
            $stmt->bindParam(':location', $data['location']);
            $stmt->bindParam(':features', $features);
            $stmt->bindParam(':image_url', $data['image_url']);
            $stmt->bindParam(':gallery_urls', $gallery);
            $status = $data['status'] ?? 'available';
            $stmt->bindParam(':status', $status);
            $featured = $data['featured'] ?? false;
            $stmt->bindParam(':featured', $featured, PDO::PARAM_BOOL);

            $stmt->execute();
            $newId = $stmt->fetch()['id'];

            sendResponse([
                'success' => true,
                'message' => 'Property created successfully',
                'id' => $newId
            ], 201);
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                sendResponse(['success' => false, 'message' => 'Property ID is required'], 400);
            }

            $updateFields = [];
            $params = [':id' => $data['id']];

            $allowedFields = ['title', 'description', 'price', 'property_type', 'bedrooms', 
                             'bathrooms', 'square_feet', 'location', 'image_url', 'status', 'featured'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }

            if (isset($data['features'])) {
                $updateFields[] = "features = :features";
                $params[':features'] = '{' . implode(',', $data['features']) . '}';
            }

            if (isset($data['gallery_urls'])) {
                $updateFields[] = "gallery_urls = :gallery_urls";
                $params[':gallery_urls'] = '{' . implode(',', $data['gallery_urls']) . '}';
            }

            $updateFields[] = "updated_at = CURRENT_TIMESTAMP";

            $query = "UPDATE properties SET " . implode(', ', $updateFields) . " WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute($params);

            sendResponse(['success' => true, 'message' => 'Property updated successfully']);
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                sendResponse(['success' => false, 'message' => 'Property ID is required'], 400);
            }

            $stmt = $db->prepare("DELETE FROM properties WHERE id = :id");
            $stmt->bindParam(':id', $_GET['id']);
            $stmt->execute();

            sendResponse(['success' => true, 'message' => 'Property deleted successfully']);
            break;

        default:
            sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    sendResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
