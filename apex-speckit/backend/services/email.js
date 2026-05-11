const sgMail = require('@sendgrid/mail');

sgMail.setApiKey(process.env.SENDGRID_API_KEY);

const sendWelcomeEmail = async (user) => {
  try {
    const msg = {
      to: user.email,
      from: process.env.EMAIL_FROM || 'noreply@apexsummit.com',
      subject: 'Welcome to Apex Leadership Summit 2026',
      html: `
        <h1>Welcome, ${user.first_name}!</h1>
        <p>Thank you for registering for the Apex Leadership Summit 2026.</p>
        <p>We're excited to have you join us on this journey.</p>
        <p>Best regards,<br/>The Apex Leadership Team</p>
      `
    };
    await sgMail.send(msg);
  } catch (error) {
    console.error('Error sending welcome email:', error);
  }
};

const sendPasswordResetEmail = async (user, resetToken) => {
  try {
    const resetLink = `${process.env.FRONTEND_URL}/reset-password?token=${resetToken}`;
    const msg = {
      to: user.email,
      from: process.env.EMAIL_FROM || 'noreply@apexsummit.com',
      subject: 'Password Reset Request',
      html: `
        <h1>Password Reset Request</h1>
        <p>Hi ${user.first_name},</p>
        <p>We received a request to reset your password. Click the link below to reset it.</p>
        <p><a href="${resetLink}">Reset Your Password</a></p>
        <p>This link will expire in 1 hour.</p>
        <p>If you didn't request this, you can ignore this email.</p>
        <p>Best regards,<br/>The Apex Leadership Team</p>
      `
    };
    await sgMail.send(msg);
  } catch (error) {
    console.error('Error sending reset email:', error);
  }
};

module.exports = {
  sendWelcomeEmail,
  sendPasswordResetEmail
};
