<?php
header('Content-Type: application/json');

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get raw POST data (since we'll send JSON from JS)
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'No data received']);
        exit;
    }

    // Sanitize and extract data
    $name       = strip_tags(trim($data['name'] ?? ''));
    $phone      = strip_tags(trim($data['phone'] ?? ''));
    $email      = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $event_type = strip_tags(trim($data['event_type'] ?? ''));
    $event_date = strip_tags(trim($data['event_date'] ?? ''));
    $location   = strip_tags(trim($data['location'] ?? ''));
    $message    = strip_tags(trim($data['message'] ?? ''));

    // Basic validation
    if (empty($name) || empty($phone) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields (Name, Phone, Email)']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO enquiries (name, phone, email, event_type, event_date, location, message) 
                               VALUES (:name, :phone, :email, :event_type, :event_date, :location, :message)");
        
        $stmt->execute([
            ':name'       => $name,
            ':phone'      => $phone,
            ':email'      => $email,
            ':event_type' => $event_type,
            ':event_date' => $event_date ?: null, // Handle empty date
            ':location'   => $location,
            ':message'    => $message
        ]);

        echo json_encode(['success' => true, 'message' => 'Enquiry submitted successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
