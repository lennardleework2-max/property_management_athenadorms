<?php
/**
 * User Model
 * Athena Dorms Property Management System
 * Handles user authentication and management
 */

class UserModel
{
    private $db;

    public function __construct()
    {
        $this->db = getDbConnection();
    }

    /**
     * Find user by email
     * @param string $email
     * @return array|false
     */
    public function findByEmail($email)
    {
        $sql = "SELECT recid, user_id, full_name, email, password_hash, user_role, user_status
                FROM utl_userfile
                WHERE email = :email AND user_status = 'active'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);

        return $stmt->fetch();
    }

    /**
     * Verify password against hash
     * Uses PHP password_verify for bcrypt hashes
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Get user by recid
     * @param int $recid
     * @return array|false
     */
    public function findById($recid)
    {
        $sql = "SELECT recid, user_id, full_name, email, user_role, user_status
                FROM utl_userfile
                WHERE recid = :recid";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['recid' => $recid]);

        return $stmt->fetch();
    }

    /**
     * Get all active users
     * @return array
     */
    public function getAll()
    {
        $sql = "SELECT recid, user_id, full_name, email, user_role, user_status, date_created
                FROM utl_userfile
                ORDER BY user_id";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    /**
     * Update user password with PHP hash
     * Use this to reseed passwords if PostgreSQL crypt doesn't match
     * @param int $recid
     * @param string $password
     * @return bool
     */
    public function updatePassword($recid, $password)
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $sql = "UPDATE utl_userfile
                SET password_hash = :hash, date_updated = NOW()
                WHERE recid = :recid";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'hash' => $hash,
            'recid' => $recid
        ]);
    }
}
