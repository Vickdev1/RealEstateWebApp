<?php
require_once 'config/database.php';

echo "Testing database connection...\n\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "✅ Database connection successful!\n\n";
    
    // Test query
    $stmt = $db->query("SELECT COUNT(*) as count FROM properties");
    $result = $stmt->fetch();
    
    echo "Properties in database: " . $result['count'] . "\n";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM services");
    $result = $stmt->fetch();
    
    echo "Services in database: " . $result['count'] . "\n";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM testimonials");
    $result = $stmt->fetch();
    
    echo "Testimonials in database: " . $result['count'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
}