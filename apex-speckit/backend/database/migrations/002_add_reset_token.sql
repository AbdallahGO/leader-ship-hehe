-- Add password reset columns to users table
ALTER TABLE IF EXISTS users
ADD COLUMN IF NOT EXISTS reset_token VARCHAR(255);
ALTER TABLE IF EXISTS users
ADD COLUMN IF NOT EXISTS reset_token_expires TIMESTAMPTZ;