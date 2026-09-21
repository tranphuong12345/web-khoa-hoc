-- ============================================================
-- COURSE MARKETPLACE DATABASE
-- MySQL 8.0+
-- Model: Admin + Seller + Student
-- Payment: Bank Transfer / Payment Gateway Webhook
-- Seller Wallet + Withdrawal
-- ============================================================

CREATE DATABASE IF NOT EXISTS course_marketplace
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE course_marketplace;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS lesson_progress;
DROP TABLE IF EXISTS enrollments;
DROP TABLE IF EXISTS withdrawal_requests;
DROP TABLE IF EXISTS wallet_transactions;
DROP TABLE IF EXISTS seller_wallets;
DROP TABLE IF EXISTS payment_webhooks;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_details;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS lessons;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 1. USERS
-- ============================================================
CREATE TABLE users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) UNIQUE,
    avatar VARCHAR(255),

    role ENUM('admin', 'seller', 'student')
        NOT NULL DEFAULT 'student',

    bank_account_number VARCHAR(50),
    bank_name VARCHAR(100),
    account_holder_name VARCHAR(150),

    status TINYINT NOT NULL DEFAULT 1,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_users_role (role),
    INDEX idx_users_status (status)
) ENGINE=InnoDB;

-- ============================================================
-- 2. CATEGORIES
-- ============================================================
CREATE TABLE categories (
    category_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255),

    status TINYINT NOT NULL DEFAULT 1,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_categories_status (status)
) ENGINE=InnoDB;

-- ============================================================
-- 3. COURSES
-- ============================================================
CREATE TABLE courses (
    course_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    seller_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,

    course_name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,

    description TEXT,
    image VARCHAR(255),

    price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    sale_price DECIMAL(15,2),

    -- Commission percentage fixed when course is approved/purchased.
    commission_rate DECIMAL(5,2) NOT NULL DEFAULT 10.00,

    level ENUM('beginner', 'intermediate', 'advanced')
        NOT NULL DEFAULT 'beginner',

    duration INT UNSIGNED NOT NULL DEFAULT 0,

    status ENUM(
        'draft',
        'pending',
        'approved',
        'rejected',
        'suspended'
    ) NOT NULL DEFAULT 'draft',

    rejection_reason TEXT,

    approved_by INT UNSIGNED NULL,
    submitted_at DATETIME NULL,
    approved_at DATETIME NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_courses_seller
        FOREIGN KEY (seller_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_courses_category
        FOREIGN KEY (category_id)
        REFERENCES categories(category_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_courses_approver
        FOREIGN KEY (approved_by)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    INDEX idx_courses_seller (seller_id),
    INDEX idx_courses_category (category_id),
    INDEX idx_courses_status (status)
) ENGINE=InnoDB;

-- ============================================================
-- 4. LESSONS
-- ============================================================
CREATE TABLE lessons (
    lesson_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    course_id INT UNSIGNED NOT NULL,

    lesson_name VARCHAR(255) NOT NULL,
    content LONGTEXT,
    video_url VARCHAR(500),

    duration INT UNSIGNED NOT NULL DEFAULT 0,
    sort_order INT UNSIGNED NOT NULL DEFAULT 1,

    is_preview TINYINT NOT NULL DEFAULT 0,
    status TINYINT NOT NULL DEFAULT 1,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_lessons_course
        FOREIGN KEY (course_id)
        REFERENCES courses(course_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    INDEX idx_lessons_course (course_id),
    INDEX idx_lessons_sort (course_id, sort_order)
) ENGINE=InnoDB;

-- ============================================================
-- 5. ORDERS
-- ============================================================
CREATE TABLE orders (
    order_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    buyer_id INT UNSIGNED NOT NULL,

    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    currency VARCHAR(10) NOT NULL DEFAULT 'VND',

    status ENUM(
        'pending',
        'paid',
        'cancelled',
        'refunded',
        'partially_refunded'
    ) NOT NULL DEFAULT 'pending',

    note TEXT,

    paid_at DATETIME NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_orders_buyer
        FOREIGN KEY (buyer_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_orders_buyer (buyer_id),
    INDEX idx_orders_status (status),
    INDEX idx_orders_created_at (created_at)
) ENGINE=InnoDB;

-- ============================================================
-- 6. ORDER DETAILS
-- ============================================================
CREATE TABLE order_details (
    order_detail_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    order_id BIGINT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    seller_id INT UNSIGNED NOT NULL,

    -- Snapshot data at purchase time.
    course_name VARCHAR(255) NOT NULL,

    original_price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    sale_price DECIMAL(15,2) NOT NULL DEFAULT 0.00,

    commission_rate DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    commission_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    seller_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,

    seller_amount_status ENUM(
        'pending',
        'available',
        'withdrawn',
        'refunded'
    ) NOT NULL DEFAULT 'pending',

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_order_details_order
        FOREIGN KEY (order_id)
        REFERENCES orders(order_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_order_details_course
        FOREIGN KEY (course_id)
        REFERENCES courses(course_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_order_details_seller
        FOREIGN KEY (seller_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_order_details_order (order_id),
    INDEX idx_order_details_course (course_id),
    INDEX idx_order_details_seller (seller_id),
    INDEX idx_order_details_seller_status (seller_id, seller_amount_status)
) ENGINE=InnoDB;

-- ============================================================
-- 7. PAYMENTS
-- ============================================================
CREATE TABLE payments (
    payment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    order_id BIGINT UNSIGNED NOT NULL,

    transaction_code VARCHAR(150) NOT NULL UNIQUE,

    provider VARCHAR(50) NOT NULL DEFAULT 'bank_transfer',

    payment_method ENUM(
        'bank_transfer',
        'vnpay',
        'momo',
        'zalopay'
    ) NOT NULL DEFAULT 'bank_transfer',

    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(10) NOT NULL DEFAULT 'VND',

    status ENUM(
        'pending',
        'processing',
        'success',
        'failed',
        'cancelled',
        'refunded'
    ) NOT NULL DEFAULT 'pending',

    provider_transaction_id VARCHAR(150),
    provider_reference VARCHAR(150),

    payment_content VARCHAR(255),

    paid_at DATETIME NULL,
    expired_at DATETIME NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_payments_order
        FOREIGN KEY (order_id)
        REFERENCES orders(order_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_payments_order (order_id),
    INDEX idx_payments_status (status),
    INDEX idx_payments_provider_transaction (provider_transaction_id)
) ENGINE=InnoDB;

-- ============================================================
-- 8. PAYMENT WEBHOOKS
-- Stores raw webhook/event data from bank/payment providers.
-- ============================================================
CREATE TABLE payment_webhooks (
    webhook_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    provider VARCHAR(50) NOT NULL,

    event_type VARCHAR(100),

    transaction_id VARCHAR(150),
    reference_code VARCHAR(150),

    amount DECIMAL(15,2),

    payload JSON NOT NULL,

    signature VARCHAR(500),

    status ENUM(
        'received',
        'processed',
        'failed',
        'ignored'
    ) NOT NULL DEFAULT 'received',

    processed_at DATETIME NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_webhooks_provider (provider),
    INDEX idx_webhooks_transaction (transaction_id),
    INDEX idx_webhooks_reference (reference_code),
    INDEX idx_webhooks_status (status),
    INDEX idx_webhooks_created_at (created_at)
) ENGINE=InnoDB;

-- ============================================================
-- 9. SELLER WALLETS
-- One wallet per seller.
-- ============================================================
CREATE TABLE seller_wallets (
    wallet_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    seller_id INT UNSIGNED NOT NULL UNIQUE,

    available_balance DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    pending_balance DECIMAL(15,2) NOT NULL DEFAULT 0.00,

    total_earned DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_withdrawn DECIMAL(15,2) NOT NULL DEFAULT 0.00,

    currency VARCHAR(10) NOT NULL DEFAULT 'VND',

    status ENUM('active', 'locked')
        NOT NULL DEFAULT 'active',

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_seller_wallets_seller
        FOREIGN KEY (seller_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_seller_wallets_status (status)
) ENGINE=InnoDB;

-- ============================================================
-- 10. WALLET TRANSACTIONS
-- Ledger for every wallet increase/decrease.
-- ============================================================
CREATE TABLE wallet_transactions (
    wallet_transaction_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    wallet_id BIGINT UNSIGNED NOT NULL,
    seller_id INT UNSIGNED NOT NULL,

    transaction_type ENUM(
        'course_sale',
        'withdrawal',
        'refund',
        'adjustment'
    ) NOT NULL,

    reference_type VARCHAR(50),
    reference_id BIGINT UNSIGNED,

    amount DECIMAL(15,2) NOT NULL,

    balance_before DECIMAL(15,2) NOT NULL,
    balance_after DECIMAL(15,2) NOT NULL,

    description VARCHAR(255),

    status ENUM(
        'pending',
        'completed',
        'failed',
        'reversed'
    ) NOT NULL DEFAULT 'completed',

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_wallet_transactions_wallet
        FOREIGN KEY (wallet_id)
        REFERENCES seller_wallets(wallet_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_wallet_transactions_seller
        FOREIGN KEY (seller_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_wallet_transactions_wallet (wallet_id),
    INDEX idx_wallet_transactions_seller (seller_id),
    INDEX idx_wallet_transactions_type (transaction_type),
    INDEX idx_wallet_transactions_reference (reference_type, reference_id),
    INDEX idx_wallet_transactions_created_at (created_at)
) ENGINE=InnoDB;

-- ============================================================
-- 11. WITHDRAWAL REQUESTS
-- Seller can request withdrawal whenever available balance allows.
-- ============================================================
CREATE TABLE withdrawal_requests (
    withdrawal_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    wallet_id BIGINT UNSIGNED NOT NULL,
    seller_id INT UNSIGNED NOT NULL,

    amount DECIMAL(15,2) NOT NULL,

    -- Snapshot of destination bank information.
    bank_name VARCHAR(100) NOT NULL,
    bank_account_number VARCHAR(50) NOT NULL,
    account_holder_name VARCHAR(150) NOT NULL,

    status ENUM(
        'pending',
        'processing',
        'completed',
        'rejected',
        'cancelled',
        'failed'
    ) NOT NULL DEFAULT 'pending',

    transfer_reference VARCHAR(150),

    admin_note TEXT,

    requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME NULL,
    completed_at DATETIME NULL,

    CONSTRAINT fk_withdrawal_wallet
        FOREIGN KEY (wallet_id)
        REFERENCES seller_wallets(wallet_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_withdrawal_seller
        FOREIGN KEY (seller_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_withdrawals_wallet (wallet_id),
    INDEX idx_withdrawals_seller (seller_id),
    INDEX idx_withdrawals_status (status),
    INDEX idx_withdrawals_created_at (requested_at)
) ENGINE=InnoDB;

-- ============================================================
-- 12. ENROLLMENTS
-- Created after successful payment.
-- ============================================================
CREATE TABLE enrollments (
    enrollment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    student_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NOT NULL,

    enrolled_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,

    status ENUM(
        'active',
        'completed',
        'cancelled'
    ) NOT NULL DEFAULT 'active',

    CONSTRAINT uq_student_course
        UNIQUE (student_id, course_id),

    CONSTRAINT fk_enrollments_student
        FOREIGN KEY (student_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_enrollments_course
        FOREIGN KEY (course_id)
        REFERENCES courses(course_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_enrollments_order
        FOREIGN KEY (order_id)
        REFERENCES orders(order_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_enrollments_student (student_id),
    INDEX idx_enrollments_course (course_id),
    INDEX idx_enrollments_order (order_id)
) ENGINE=InnoDB;

-- ============================================================
-- 13. LESSON PROGRESS
-- ============================================================
CREATE TABLE lesson_progress (
    progress_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    enrollment_id BIGINT UNSIGNED NOT NULL,
    lesson_id INT UNSIGNED NOT NULL,

    progress_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,

    is_completed TINYINT NOT NULL DEFAULT 0,

    video_position INT UNSIGNED NOT NULL DEFAULT 0,

    started_at DATETIME NULL,
    completed_at DATETIME NULL,

    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT uq_enrollment_lesson
        UNIQUE (enrollment_id, lesson_id),

    CONSTRAINT fk_progress_enrollment
        FOREIGN KEY (enrollment_id)
        REFERENCES enrollments(enrollment_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_progress_lesson
        FOREIGN KEY (lesson_id)
        REFERENCES lessons(lesson_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    INDEX idx_progress_enrollment (enrollment_id),
    INDEX idx_progress_lesson (lesson_id)
) ENGINE=InnoDB;

-- ============================================================
-- 14. REVIEWS
-- ============================================================
CREATE TABLE reviews (
    review_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    student_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,

    rating TINYINT UNSIGNED NOT NULL,

    comment TEXT,

    status TINYINT NOT NULL DEFAULT 1,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT uq_student_course_review
        UNIQUE (student_id, course_id),

    CONSTRAINT fk_reviews_student
        FOREIGN KEY (student_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_reviews_course
        FOREIGN KEY (course_id)
        REFERENCES courses(course_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_reviews_rating
        CHECK (rating BETWEEN 1 AND 5),

    INDEX idx_reviews_course (course_id),
    INDEX idx_reviews_student (student_id)
) ENGINE=InnoDB;

-- ============================================================
-- 15. NOTIFICATIONS
-- ============================================================
CREATE TABLE notifications (
    notification_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL,

    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,

    type ENUM(
        'course_approved',
        'course_rejected',
        'order',
        'payment',
        'withdrawal',
        'wallet',
        'system'
    ) NOT NULL,

    reference_id BIGINT UNSIGNED NULL,

    is_read TINYINT NOT NULL DEFAULT 0,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_notifications_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    INDEX idx_notifications_user (user_id),
    INDEX idx_notifications_read (user_id, is_read),
    INDEX idx_notifications_created_at (created_at)
) ENGINE=InnoDB;

-- ============================================================
-- BASIC SEED DATA
-- Passwords below are placeholders for development only.
-- In the real application, store bcrypt/argon2 hashes.
-- ============================================================

INSERT INTO users
(full_name, email, password, role, status)
VALUES
('System Administrator', 'admin@example.com', 'CHANGE_ME_HASH', 'admin', 1);

-- ============================================================
-- BUSINESS FLOW
--
-- 1. Seller creates account.
-- 2. Seller creates course -> draft.
-- 3. Seller submits course -> pending.
-- 4. Admin reviews -> approved/rejected.
-- 5. Student creates order -> pending.
-- 6. Student transfers money to platform bank account.
-- 7. Payment gateway/bank webhook reaches backend.
-- 8. Backend validates webhook and updates payments -> success.
-- 9. Backend updates orders -> paid.
-- 10. Backend creates enrollments.
-- 11. Backend calculates:
--       commission_amount = sale_price * commission_rate / 100
--       seller_amount     = sale_price - commission_amount
-- 12. Seller amount becomes available/pending according to business rules.
-- 13. wallet_transactions records the wallet movement.
-- 14. Seller requests withdrawal.
-- 15. withdrawal_requests stores the withdrawal.
-- 16. Backend/admin/payout provider transfers money to Seller bank.
-- 17. wallet_transactions records the withdrawal.
-- 18. seller_wallets.available_balance decreases.
--
-- IMPORTANT:
-- Database wallet balance is NOT real bank money.
-- Real money remains in the platform bank account until payout.
-- Automatic payout requires a bank/payment provider API.
-- ============================================================
