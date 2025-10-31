<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $query = "SELECT * FROM testimonials WHERE active = true ORDER BY display_order ASC, created_at DESC";
            
            if (isset($_GET['limit'])) {
                $query .= " LIMIT :limit";
            }

            $stmt = $db->prepare($query);
            
            if (isset($_GET['limit'])) {
                $stmt->bindValue(':limit', (int)$_GET['limit'], PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $testimonials = $stmt->fetchAll();

            sendResponse(['success' => true, 'data' => $testimonials]);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            $required = ['client_name', 'testimonial'];
            $errors = validateRequired($data, $required);
            
            if (!empty($errors)) {
                sendResponse(['success' => false, 'errors' => $errors], 400);
            }

            $stmt = $db->prepare("
                INSERT INTO testimonials 
                (client_name, testimonial, rating, client_image, display_order, active)
                VALUES 
                (:client_name, :testimonial, :rating, :client_image, :display_order, :active)
                RETURNING id
            ");

            $stmt->bindParam(':client_name', $data['client_name']);
            $stmt->bindParam(':testimonial', $data['testimonial']);
            $rating = $data['rating'] ?? 5;
            $stmt->bindParam(':rating', $rating);
            $stmt->bindParam(':client_image', $data['client_image']);
            $display_order = $data['display_order'] ?? 0;
            $stmt->bindParam(':display_order', $display_order);
            $active = $data['active'] ?? true;
            $stmt->bindParam(':active', $active, PDO::PARAM_BOOL);

            $stmt->execute();
            $newId = $stmt->fetch()['id'];

            sendResponse([
                'success' => true,
                'message' => 'Testimonial created successfully',
                'id' => $newId
            ], 201);
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                sendResponse(['success' => false, 'message' => 'Testimonial ID is required'], 400);
            }

            $stmt = $db->prepare("DELETE FROM testimonials WHERE id = :id");
            $stmt->bindParam(':id', $_GET['id']);
            $stmt->execute();

            sendResponse(['success' => true, 'message' => 'Testimonial deleted successfully']);
            break;

        default:
            sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    sendResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
