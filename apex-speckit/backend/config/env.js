function validateEnv() {
  const requiredVars = [
    'PORT',
    'NODE_ENV',
    'DB_HOST',
    'DB_PORT',
    'DB_NAME',
    'DB_USER',
    'DB_PASSWORD',
    'JWT_SECRET',
    'JWT_EXPIRES_IN',
    'STRIPE_SECRET_KEY',
    'STRIPE_PUBLISHABLE_KEY',
    'STRIPE_WEBHOOK_SECRET',
    'SENDGRID_API_KEY',
    'EMAIL_FROM',
    'EMAIL_FROM_NAME',
    'FRONTEND_URL'
  ];

  const missingVars = requiredVars.filter((key) => !process.env[key]);

  if (missingVars.length > 0) {
    throw new Error(`Missing env vars: ${missingVars.join(', ')}`);
  }

  console.log('✓ Environment variables validated');
}

module.exports = {
  validateEnv,
};
