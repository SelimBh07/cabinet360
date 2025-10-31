<?php
class UserPreferences {
    private $conn;
    private $lawyer_id;

    public function __construct($conn, $lawyer_id) {
        $this->conn = $conn;
        $this->lawyer_id = $lawyer_id;
    }

    public function getPreferences() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM user_preferences WHERE lawyer_id = ?");
            $stmt->execute([$this->lawyer_id]);
            $prefs = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$prefs && $this->lawyer_id) {
                // Create default preferences if they don't exist
                $stmt = $this->conn->prepare("INSERT INTO user_preferences (lawyer_id) VALUES (?)");
                $stmt->execute([$this->lawyer_id]);
                
                // Fetch the newly created preferences
                $stmt = $this->conn->prepare("SELECT * FROM user_preferences WHERE lawyer_id = ?");
                $stmt->execute([$this->lawyer_id]);
                $prefs = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            return $prefs ?: [
                'theme' => 'light',
                'language' => 'fr',
                'email_notifications' => false,
                'appointment_reminders' => false,
                'payment_reminders' => false
            ];
        } catch (PDOException $e) {
            // Return default preferences if there's an error
            return [
                'theme' => 'light',
                'language' => 'fr',
                'email_notifications' => false,
                'appointment_reminders' => false,
                'payment_reminders' => false
            ];
        }
    }

    public function updateTheme($theme) {
        $stmt = $this->conn->prepare("UPDATE user_preferences SET theme = ? WHERE lawyer_id = ?");
        return $stmt->execute([$theme, $this->lawyer_id]);
    }

    public function updateLanguage($language) {
        $stmt = $this->conn->prepare("UPDATE user_preferences SET language = ? WHERE lawyer_id = ?");
        return $stmt->execute([$language, $this->lawyer_id]);
    }

    public function updateNotificationSettings($email, $appointment, $payment) {
        $stmt = $this->conn->prepare("UPDATE user_preferences 
            SET email_notifications = ?, 
                appointment_reminders = ?, 
                payment_reminders = ? 
            WHERE lawyer_id = ?");
        return $stmt->execute([$email, $appointment, $payment, $this->lawyer_id]);
    }
}