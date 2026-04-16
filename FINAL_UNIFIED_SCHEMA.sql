-- ==========================================================
-- INDUSTRY-GRADE UNIFIED DB SCHEMA (PostgreSQL 18)
-- Optimized for Web (CodeIgniter 4) + Mobile Apps
-- Features: Strict ENUM Types, Binary Media, Audit Trails
-- ==========================================================

-- 1. Create Custom ENUM Types for strict data integrity
CREATE TYPE ticket_status AS ENUM ('Open', 'In Progress', 'Closed', 'Resolved');
CREATE TYPE ticket_priority AS ENUM ('Low', 'Medium', 'High');
CREATE TYPE communication_medium_type AS ENUM ('Direct Call', 'Live Chat', 'Email', 'Online Meeting', 'WhatsApp');

-- 2. USERS TABLE
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    rollno VARCHAR(50),           -- Mobile specific
    course VARCHAR(100),          -- Mobile specific
    role VARCHAR(20) DEFAULT 'user',
    photo VARCHAR(255),
    photo_data BYTEA,             -- Binary storage (Web)
    photo_mime VARCHAR(100),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- 3. SUPPORT TICKETS TABLE
CREATE TABLE support_tickets (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,  -- Primary identifier (consolidated)
    category VARCHAR(100),
    priority ticket_priority DEFAULT 'Medium', -- Strict ENUM
    communication_method communication_medium_type, -- Strict ENUM
    description TEXT NOT NULL,
    attachment_name VARCHAR(255), -- Renamed for mobile sync
    attachment_data BYTEA,        -- Binary storage (Web)
    attachment_mime VARCHAR(100),
    image_url TEXT,               -- Cloud Link (Mobile)
    status ticket_status DEFAULT 'Open', -- Strict ENUM
    department_id VARCHAR(100),
    assigned_to INT REFERENCES users(id) ON DELETE SET NULL,
    agent_remark TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    closed_at TIMESTAMP
);

-- 4. TICKET REPLIES TABLE (Chat Threading)
CREATE TABLE ticket_replies (
    id SERIAL PRIMARY KEY,
    ticket_id INT NOT NULL REFERENCES support_tickets(id) ON DELETE CASCADE,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    message TEXT NOT NULL,
    attachment VARCHAR(255),
    attachment_data BYTEA,
    attachment_mime VARCHAR(100),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- 5. AUDIT LOG TABLE
CREATE TABLE support_ticket_history (
    id SERIAL PRIMARY KEY,
    ticket_id INT NOT NULL REFERENCES support_tickets(id) ON DELETE CASCADE,
    changed_by INT REFERENCES users(id) ON DELETE SET NULL,
    action_type VARCHAR(100) NOT NULL,
    old_value TEXT,
    new_value TEXT,
    log_message TEXT,
    created_at TIMESTAMP DEFAULT NOW()
);

-- 6. Performance Indexes
CREATE INDEX idx_tkt_user ON support_tickets(user_id);
CREATE INDEX idx_tkt_status ON support_tickets(status);
CREATE INDEX idx_replies_tkt ON ticket_replies(ticket_id);
