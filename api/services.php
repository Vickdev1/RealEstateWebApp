<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $stmt = $db->prepare("
                SELECT * FROM services 
                WHERE active = true 
                ORDER BY display_order ASC
            ");
            $stmt->execute();
            $services = $stmt->fetchAll();

            sendResponse(['success' => true, 'data' => $services]);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            $required = ['title', 'description'];
            $errors = validateRequired($data, $required);
            
            if (!empty($errors)) {
                sendResponse(['success' => false, 'errors' => $errors], 400);
            }

            $stmt = $db->prepare("
                INSERT INTO services 
                (title, description, icon, image_url, display_order, active)
                VALUES 
                (:title, :description, :icon, :image_url, :display_order, :active)
                RETURNING id
            ");

            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':icon', $data['icon']);
            $stmt->bindParam(':image_url', $data['image_url']);
            $display_order = $data['display_order'] ?? 0;
            $stmt->bindParam(':display_order', $display_order);
            $active = $data['active'] ?? true;
            $stmt->bindParam(':active', $active, PDO::PARAM_BOOL);

            $stmt->execute();
            $newId = $stmt->fetch()['id'];

            sendResponse([
                'success' => true,
                'message' => 'Service created successfully',
                'id' => $newId
            ], 201);
            break;

        default:
            sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    sendResponse(['success' => false, 'message' => $e->getMessage()], 500);
}