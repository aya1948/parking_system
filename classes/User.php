<?php
// classes/User.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

class User {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    // ─── CRUD ────────────────────────────────────────────────

    public function register(string $name, string $email, string $password, string $role = 'driver', string $phone = ''): array {
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => 'Email already registered.'];
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO users (full_name, email, password_hash, role, phone) VALUES (?,?,?,?,?)");
        $stmt->execute([$name, $email, $hash, $role, $phone]);
        $userId = $this->db->lastInsertId();
        $this->auditLog($userId, 'USER_REGISTERED', 'users', $userId);
        return ['success' => true, 'user_id' => $userId];
    }

    public function getUserById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT user_id, full_name, email, phone, role, is_active, is_blacklisted, loyalty_points, preferred_language, preferred_currency, created_at FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function updateProfile(int $userId, array $data): bool {
        $allowed = ['full_name', 'phone', 'preferred_language', 'preferred_currency'];
        $fields = [];
        $values = [];
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        if (empty($fields)) return false;
        $values[] = $userId;
        $stmt = $this->db->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = ?");
        return $stmt->execute($values);
    }

    public function deleteUser(int $userId): bool {
        $stmt = $this->db->prepare("UPDATE users SET is_active = 0 WHERE user_id = ?");
        $result = $stmt->execute([$userId]);
        $this->auditLog($_SESSION['user_id'] ?? null, 'USER_DEACTIVATED', 'users', $userId);
        return $result;
    }

    public function searchUsers(string $query, string $role = ''): array {
        $sql = "SELECT user_id, full_name, email, phone, role, is_active, is_blacklisted FROM users WHERE (full_name LIKE ? OR email LIKE ?)";
        $params = ["%$query%", "%$query%"];
        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function listUsers(string $role = '', int $limit = 50, int $offset = 0): array {
        $sql = "SELECT user_id, full_name, email, phone, role, is_active, is_blacklisted, created_at FROM users WHERE 1=1";
        $params = [];
        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ─── NON-CRUD: Authentication ─────────────────────────────

    public function login(string $email, string $password): array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }
        if ($user['is_blacklisted']) {
            return ['success' => false, 'message' => 'Your account has been suspended. Reason: ' . $user['blacklist_reason']];
        }

        // Store in session (never store password_hash)
        unset($user['password_hash']);
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user']    = $user;

        $this->auditLog($user['user_id'], 'USER_LOGIN', 'users', $user['user_id']);
        return ['success' => true, 'role' => $user['role']];
    }

    public function logout(): void {
        $userId = $_SESSION['user_id'] ?? null;
        $this->auditLog($userId, 'USER_LOGOUT', 'users', $userId);
        session_destroy();
        header('Location: /parking_system/index.php');
        exit;
    }

    public function changePassword(int $userId, string $oldPassword, string $newPassword): array {
        $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($oldPassword, $row['password_hash'])) {
            return ['success' => false, 'message' => 'Current password is incorrect.'];
        }
        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $stmt->execute([$newHash, $userId]);
        $this->auditLog($userId, 'PASSWORD_CHANGED', 'users', $userId);
        return ['success' => true, 'message' => 'Password changed successfully.'];
    }

    // ─── NON-CRUD: Blacklist Manager ─────────────────────────

    /**
     * Automatically bars drivers with more than 3 unpaid fines.
     * Checks current unpaid fines count and blacklists if threshold exceeded.
     */
    public function checkAndApplyBlacklist(int $driverId): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM fines WHERE driver_id = ? AND status = 'unpaid'");
        $stmt->execute([$driverId]);
        $count = (int)$stmt->fetchColumn();

        if ($count >= 3) {
            $reason = "Automatically suspended: $count unpaid fines exceed the allowed limit of 3.";
            $stmt = $this->db->prepare("UPDATE users SET is_blacklisted = 1, blacklist_reason = ?, unpaid_fines_count = ? WHERE user_id = ?");
            $stmt->execute([$reason, $count, $driverId]);
            $this->auditLog(null, 'AUTO_BLACKLIST', 'users', $driverId, null, $reason);
            return true; // was blacklisted
        }
        return false;
    }

    public function liftBlacklist(int $driverId, int $adminId): bool {
        $stmt = $this->db->prepare("UPDATE users SET is_blacklisted = 0, blacklist_reason = NULL WHERE user_id = ?");
        $result = $stmt->execute([$driverId]);
        $this->auditLog($adminId, 'BLACKLIST_LIFTED', 'users', $driverId);
        return $result;
    }

    // ─── NON-CRUD: Loyalty Points ─────────────────────────────

    public function addLoyaltyPoints(int $userId, int $points): void {
        $stmt = $this->db->prepare("UPDATE users SET loyalty_points = loyalty_points + ? WHERE user_id = ?");
        $stmt->execute([$points, $userId]);
    }

    /**
     * Tiered loyalty discount: returns discount % based on monthly bookings.
     */
    public function getLoyaltyDiscount(int $userId): float {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as monthly_count 
            FROM reservations 
            WHERE driver_id = ? 
              AND status IN ('completed','active')
              AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        ");
        $stmt->execute([$userId]);
        $count = (int)$stmt->fetchColumn();

        if ($count >= 20) return 0.20;      // 20% discount
        if ($count >= 10) return 0.10;      // 10% discount
        if ($count >= 5)  return 0.05;      // 5% discount
        return 0.00;
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function emailExists(string $email): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function auditLog(?int $userId, string $action, string $table, ?int $targetId, ?string $old = null, ?string $new = null): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $stmt = $this->db->prepare("INSERT INTO audit_log (user_id, action, target_table, target_id, old_value, new_value, ip_address) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$userId, $action, $table, $targetId, $old, $new, $ip]);
    }
}
