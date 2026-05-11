const express = require('express');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const crypto = require('crypto');
const rateLimit = require('express-rate-limit');
const { pool } = require('../config/db');
const authMiddleware = require('../middleware/auth');
const {
  validateRegistration,
  validateLogin,
  validateForgotPassword,
  validateResetPassword,
  validationResult
} = require('../middleware/validators/auth');
const { sendWelcomeEmail, sendPasswordResetEmail } = require('../services/email');

const router = express.Router();

// Rate limiter for auth endpoints
const authLimiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 10,
  message: { success: false, error: 'Too many attempts, try again later' },
  standardHeaders: false,
  legacyHeaders: false
});

// Helper function to generate JWT
const generateToken = (user) => {
  return jwt.sign(
    { id: user.id, email: user.email, role: user.role },
    process.env.JWT_SECRET,
    { expiresIn: process.env.JWT_EXPIRES_IN || '7d' }
  );
};

// Helper function to get cookie options
const getCookieOptions = () => {
  return {
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'strict',
    maxAge: 7 * 24 * 60 * 60 * 1000 // 7 days
  };
};

// POST /api/auth/register
router.post('/register', authLimiter, validateRegistration, async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        error: 'Validation failed',
        details: errors.array()
      });
    }

    const { first_name, last_name, email, password, phone, organization, country, city } = req.body;

    // Check if email already exists
    const existingUser = await pool.query(
      'SELECT id FROM users WHERE email = $1',
      [email]
    );

    if (existingUser.rows.length > 0) {
      return res.status(409).json({
        success: false,
        error: 'Email already in use'
      });
    }

    // Hash password
    const hashedPassword = await bcrypt.hash(password, 12);

    // Insert user
    const result = await pool.query(
      `INSERT INTO users (first_name, last_name, email, password, phone, organization, country, city, role)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)
       RETURNING id, first_name, last_name, email, phone, organization, country, city, role, created_at`,
      [first_name, last_name, email, hashedPassword, phone, organization, country, city, 'attendee']
    );

    const user = result.rows[0];

    // Generate JWT and set cookie
    const token = generateToken(user);
    res.cookie('token', token, getCookieOptions());

    // Send welcome email (non-blocking)
    sendWelcomeEmail(user).catch(err => console.error('Welcome email error:', err));

    res.status(201).json({
      success: true,
      data: { user }
    });
  } catch (error) {
    console.error('Register error:', error);
    res.status(500).json({
      success: false,
      error: 'Internal server error'
    });
  }
});

// POST /api/auth/login
router.post('/login', authLimiter, validateLogin, async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        error: 'Validation failed',
        details: errors.array()
      });
    }

    const { email, password } = req.body;
    const genericError = 'Invalid email or password';

    // Find user by email
    const result = await pool.query(
      'SELECT * FROM users WHERE email = $1',
      [email]
    );

    if (result.rows.length === 0) {
      return res.status(401).json({
        success: false,
        error: genericError
      });
    }

    const user = result.rows[0];

    // Compare password
    const isPasswordValid = await bcrypt.compare(password, user.password);

    if (!isPasswordValid) {
      return res.status(401).json({
        success: false,
        error: genericError
      });
    }

    // Generate JWT and set cookie
    const token = generateToken(user);
    res.cookie('token', token, getCookieOptions());

    // Return user without password
    const { password: _, ...userWithoutPassword } = user;

    res.status(200).json({
      success: true,
      data: { user: userWithoutPassword }
    });
  } catch (error) {
    console.error('Login error:', error);
    res.status(500).json({
      success: false,
      error: 'Internal server error'
    });
  }
});

// POST /api/auth/logout
router.post('/logout', (req, res) => {
  res.clearCookie('token');
  res.status(200).json({
    success: true
  });
});

// GET /api/auth/me
router.get('/me', authMiddleware, async (req, res) => {
  try {
    const result = await pool.query(
      'SELECT id, first_name, last_name, email, phone, organization, country, city, role, created_at, updated_at FROM users WHERE id = $1',
      [req.user.id]
    );

    if (result.rows.length === 0) {
      return res.status(404).json({
        success: false,
        error: 'User not found'
      });
    }

    res.status(200).json({
      success: true,
      data: { user: result.rows[0] }
    });
  } catch (error) {
    console.error('Get user error:', error);
    res.status(500).json({
      success: false,
      error: 'Internal server error'
    });
  }
});

// POST /api/auth/forgot-password
router.post('/forgot-password', authLimiter, validateForgotPassword, async (req, res) => {
  // Always return 200 to prevent email enumeration
  try {
    const { email } = req.body;

    // Find user by email
    const result = await pool.query(
      'SELECT * FROM users WHERE email = $1',
      [email]
    );

    if (result.rows.length > 0) {
      const user = result.rows[0];

      // Generate reset token
      const resetToken = crypto.randomBytes(32).toString('hex');
      const resetTokenHash = crypto.createHash('sha256').update(resetToken).digest('hex');
      const resetTokenExpires = new Date(Date.now() + 3600000); // 1 hour

      // Save reset token and expiry
      await pool.query(
        'UPDATE users SET reset_token = $1, reset_token_expires = $2 WHERE id = $3',
        [resetTokenHash, resetTokenExpires, user.id]
      );

      // Send reset email (non-blocking)
      sendPasswordResetEmail(user, resetToken).catch(err => console.error('Reset email error:', err));
    }

    // Always return 200
    res.status(200).json({
      success: true,
      message: 'If email exists, password reset link has been sent'
    });
  } catch (error) {
    console.error('Forgot password error:', error);
    // Still return 200
    res.status(200).json({
      success: true,
      message: 'If email exists, password reset link has been sent'
    });
  }
});

// POST /api/auth/reset-password
router.post('/reset-password', validateResetPassword, async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        error: 'Validation failed',
        details: errors.array()
      });
    }

    const { token, password } = req.body;

    // Hash the incoming token
    const tokenHash = crypto.createHash('sha256').update(token).digest('hex');

    // Find user with matching reset token and check expiry
    const result = await pool.query(
      'SELECT * FROM users WHERE reset_token = $1 AND reset_token_expires > NOW()',
      [tokenHash]
    );

    if (result.rows.length === 0) {
      return res.status(400).json({
        success: false,
        error: 'Invalid or expired token'
      });
    }

    const user = result.rows[0];

    // Hash new password
    const hashedPassword = await bcrypt.hash(password, 12);

    // Update password and clear reset token
    await pool.query(
      'UPDATE users SET password = $1, reset_token = NULL, reset_token_expires = NULL WHERE id = $2',
      [hashedPassword, user.id]
    );

    res.status(200).json({
      success: true,
      message: 'Password has been reset successfully'
    });
  } catch (error) {
    console.error('Reset password error:', error);
    res.status(500).json({
      success: false,
      error: 'Internal server error'
    });
  }
});

module.exports = router;
