-- Apex Summit database schema
CREATE EXTENSION IF NOT EXISTS pgcrypto;
CREATE TABLE IF NOT EXISTS users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255),
    phone VARCHAR(30),
    organization VARCHAR(200),
    country VARCHAR(100),
    city VARCHAR(100),
    role VARCHAR(20) DEFAULT 'attendee',
    -- Email verification (add these 3 lines)
    is_verified BOOLEAN DEFAULT FALSE,
    verify_token VARCHAR(255),
    verify_token_expires TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
CREATE UNIQUE INDEX IF NOT EXISTS users_email_unique ON users(email);
CREATE TABLE IF NOT EXISTS plans (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    key VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    price NUMERIC(10, 2) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE UNIQUE INDEX IF NOT EXISTS plans_key_unique ON plans(key);
CREATE TABLE IF NOT EXISTS orders (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_number VARCHAR(100) NOT NULL,
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    plan_id UUID REFERENCES plans(id) ON DELETE CASCADE,
    quantity INTEGER NOT NULL DEFAULT 1,
    unit_price NUMERIC(10, 2) NOT NULL,
    promo_code VARCHAR(100),
    discount_amount NUMERIC(10, 2) DEFAULT 0,
    fee_amount NUMERIC(10, 2) DEFAULT 0,
    total_amount NUMERIC(10, 2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'USD',
    status VARCHAR(50) DEFAULT 'pending',
    payment_method VARCHAR(50),
    stripe_payment_id VARCHAR(255),
    created_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE UNIQUE INDEX IF NOT EXISTS orders_order_number_unique ON orders(order_number);
CREATE INDEX IF NOT EXISTS orders_user_id_idx ON orders(user_id);
CREATE INDEX IF NOT EXISTS orders_status_idx ON orders(status);
CREATE TABLE IF NOT EXISTS tickets (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_id UUID REFERENCES orders(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    ticket_code VARCHAR(150) NOT NULL,
    plan_key VARCHAR(100) NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    used_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE UNIQUE INDEX IF NOT EXISTS tickets_ticket_code_unique ON tickets(ticket_code);
CREATE INDEX IF NOT EXISTS tickets_order_id_idx ON tickets(order_id);
CREATE TABLE IF NOT EXISTS promo_codes (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    code VARCHAR(100) NOT NULL,
    discount_type VARCHAR(50) NOT NULL,
    discount_value NUMERIC(10, 2) NOT NULL,
    max_uses INTEGER NOT NULL DEFAULT 500,
    used_count INTEGER NOT NULL DEFAULT 0,
    expires_at TIMESTAMPTZ,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE UNIQUE INDEX IF NOT EXISTS promo_codes_code_unique ON promo_codes(code);
CREATE TABLE IF NOT EXISTS speakers (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    role VARCHAR(255),
    bio TEXT,
    talk_title VARCHAR(255),
    talk_desc TEXT,
    image_url VARCHAR(500),
    order_index INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE UNIQUE INDEX IF NOT EXISTS speakers_name_unique ON speakers(name);
CREATE TABLE IF NOT EXISTS sponsors (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    logo_url VARCHAR(500),
    website VARCHAR(500),
    tier VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE UNIQUE INDEX IF NOT EXISTS sponsors_name_unique ON sponsors(name);
CREATE TABLE IF NOT EXISTS host_applications (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    organization VARCHAR(255),
    venue_name VARCHAR(255),
    venue_capacity INTEGER,
    city VARCHAR(100),
    country VARCHAR(100),
    message TEXT,
    status VARCHAR(50) DEFAULT 'pending',
    submitted_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE TABLE IF NOT EXISTS inquiries (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    type VARCHAR(100),
    message TEXT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);