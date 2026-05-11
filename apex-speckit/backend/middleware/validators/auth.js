const { body, validationResult } = require('express-validator');

const validateRegistration = [
  body('first_name')
    .notEmpty().withMessage('First name is required'),
  body('last_name')
    .notEmpty().withMessage('Last name is required'),
  body('email')
    .isEmail().withMessage('Invalid email format')
    .normalizeEmail(),
  body('password')
    .isLength({ min: 8 }).withMessage('Password must be at least 8 characters'),
  body('country')
    .notEmpty().withMessage('Country is required'),
  body('phone')
    .optional()
    .isMobilePhone().withMessage('Invalid phone format'),
  body('organization')
    .optional(),
  body('city')
    .optional()
];

const validateLogin = [
  body('email')
    .isEmail().withMessage('Invalid email format'),
  body('password')
    .notEmpty().withMessage('Password is required')
];

const validateForgotPassword = [
  body('email')
    .isEmail().withMessage('Invalid email format')
];

const validateResetPassword = [
  body('token')
    .notEmpty().withMessage('Reset token is required'),
  body('password')
    .isLength({ min: 8 }).withMessage('Password must be at least 8 characters')
];

module.exports = {
  validateRegistration,
  validateLogin,
  validateForgotPassword,
  validateResetPassword,
  validationResult
};
