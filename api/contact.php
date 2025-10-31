<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Admin endpoint to view contact submissions
            $query = "SELECT * FROM contact_submissions ORDER BY created_at DESC";
            
            if (isset($_GET['status'])) {
                $query = "SELECT * FROM contact_submissions WHERE status = :status ORDER BY created_at DESC";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':status', $_GET['status']);
            } else {
                $stmt = $db->prepare($query);
            }
            
            $stmt->execute();
            $submissions = $stmt->fetchAll();

            sendResponse(['success' => true, 'data' => $submissions]);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            $required = ['name', 'email', 'message'];
            $errors = validateRequired($data, $required);
            
            // Validate email
            if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            }
            
            if (!empty($errors)) {
                sendResponse(['success' => false, 'errors' => $errors], 400);
            }

            $stmt = $db->prepare("
                INSERT INTO contact_submissions 
                (name, email, phone, message, status)
                VALUES 
                (:name, :email, :phone, :message, 'new')
                RETURNING id
            ");

            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':message', $data['message']);

            $stmt->execute();
            $newId = $stmt->fetch()['id'];

            // Here you could add email notification logic
            
            sendResponse([
                'success' => true,
                'message' => 'Thank you for your message! We will get back to you soon.',
                'id' => $newId
            ], 201);
            break;

        case 'PUT':
            // Update submission status (admin only)
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id']) || !isset($data['status'])) {
                sendResponse(['success' => false, 'message' => 'ID and status are required'], 400);
            }

            $stmt = $db->prepare("UPDATE contact_submissions SET status = :status WHERE id = :id");
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':id', $data['id']);
            $stmt->execute();

            sendResponse(['success' => true, 'message' => 'Status updated successfully']);
            break;

        default:
            sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    sendResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
