const sgMail = require('@sendgrid/mail');
sgMail.setApiKey(process.env.SENDGRID_API_KEY);

const FROM = {
  email: process.env.EMAIL_FROM,
  name:  process.env.EMAIL_FROM_NAME
};

// ─────────────────────────────────────
// Base wrapper — shared header + footer
// ─────────────────────────────────────
function wrapEmail(content) {
  return `
  <!DOCTYPE html>
  <html>
  <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
  <body style="margin:0;padding:0;background:#f4f4f4;font-family:'DM Sans',Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:40px 20px;">
      <tr><td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:4px;overflow:hidden;max-width:600px;width:100%;">

          <!-- Gold top bar -->
          <tr><td style="background:linear-gradient(90deg,#9a7a30,#c9a84c,#e8cb82);height:4px;"></td></tr>

          <!-- Logo header -->
          <tr>
            <td style="background:#080808;padding:28px 40px;text-align:center;">
              <span style="font-family:Georgia,serif;font-size:22px;font-weight:700;color:#f2ede6;letter-spacing:-0.02em;">
                ◆ Apex Leadership
              </span>
              <p style="margin:6px 0 0;font-size:11px;letter-spacing:0.2em;text-transform:uppercase;color:#c9a84c;">
                Summit 2026
              </p>
            </td>
          </tr>

          <!-- Content -->
          <tr>
            <td style="padding:40px 40px 32px;">
              ${content}
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background:#f9f9f9;border-top:1px solid #e8e8e8;padding:24px 40px;text-align:center;">
              <p style="margin:0;font-size:12px;color:#888;line-height:1.6;">
                Apex Leadership Network · August 12–13, 2026 · Chicago, IL<br>
                <a href="#" style="color:#c9a84c;text-decoration:none;">Unsubscribe</a> ·
                <a href="#" style="color:#c9a84c;text-decoration:none;">Privacy Policy</a>
              </p>
            </td>
          </tr>

        </table>
      </td></tr>
    </table>
  </body>
  </html>`;
}

// ─────────────────────────────────────
// Send helper — never throws
// ─────────────────────────────────────
async function sendEmail(to, subject, html) {
  try {
    await sgMail.send({ to, from: FROM, subject, html });
    console.log(`✓ Email sent to ${to}: ${subject}`);
  } catch (err) {
    console.error(`✗ Email failed to ${to}:`, err.response?.body || err.message);
    // We don't throw — email failure should never crash a request
  }
}

// ═════════════════════════════════════════════
// 1. WELCOME EMAIL — sent after registration
// ═════════════════════════════════════════════
async function sendWelcomeEmail(user) {
  const html = wrapEmail(`
    <h1 style="font-family:Georgia,serif;font-size:28px;font-weight:700;color:#080808;margin:0 0 8px;">
      Welcome, ${user.first_name}! 👋
    </h1>
    <p style="font-size:15px;color:#555;margin:0 0 24px;line-height:1.6;">
      Your Apex Leadership account is ready. You're now part of a global community
      of <strong>350,000+ leaders</strong> from 100+ countries.
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f9f6f0;border-left:3px solid #c9a84c;margin:0 0 28px;">
      <tr><td style="padding:20px 24px;">
        <p style="margin:0;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;color:#c9a84c;">Your Account</p>
        <p style="margin:8px 0 0;font-size:14px;color:#333;"><strong>Name:</strong> ${user.first_name} ${user.last_name}</p>
        <p style="margin:4px 0 0;font-size:14px;color:#333;"><strong>Email:</strong> ${user.email}</p>
        ${user.organization ? `<p style="margin:4px 0 0;font-size:14px;color:#333;"><strong>Organization:</strong> ${user.organization}</p>` : ''}
      </td></tr>
    </table>

    <p style="font-size:14px;color:#555;margin:0 0 8px;line-height:1.6;">
      <strong>What's next?</strong>
    </p>
    <ul style="font-size:14px;color:#555;margin:0 0 28px;padding-left:20px;line-height:2;">
      <li>Register for the Summit — Aug 12–13, 2026</li>
      <li>Meet your speakers and explore session topics</li>
      <li>Invite your team with group ticket discounts</li>
    </ul>

    <a href="http://localhost:3000/#attend" style="
      display:inline-block;background:#c9a84c;color:#000;
      text-decoration:none;font-size:13px;font-weight:700;
      text-transform:uppercase;letter-spacing:0.1em;
      padding:15px 32px;border-radius:2px;">
      Register for the Summit →
    </a>

    <p style="font-size:13px;color:#999;margin:28px 0 0;line-height:1.6;">
      If you didn't create this account, you can safely ignore this email.
    </p>
  `);

  await sendEmail(
    user.email,
    `Welcome to Apex Leadership Summit 2026, ${user.first_name}!`,
    html
  );
}

// ═════════════════════════════════════════════
// 2. VERIFICATION EMAIL — sent after registration
//    User clicks link to verify their email
// ═════════════════════════════════════════════
async function sendVerificationEmail(user, verifyToken) {
  const verifyLink = `http://localhost:5000/api/auth/verify-email?token=${verifyToken}`;

  const html = wrapEmail(`
    <h1 style="font-family:Georgia,serif;font-size:28px;font-weight:700;color:#080808;margin:0 0 8px;">
      Verify Your Email
    </h1>
    <p style="font-size:15px;color:#555;margin:0 0 24px;line-height:1.6;">
      Hi ${user.first_name}, thanks for creating your Apex Leadership account.
      Please verify your email address to activate your account and complete registration.
    </p>

    <div style="background:#f9f6f0;border:1px solid #e8d9b0;border-radius:2px;padding:24px;text-align:center;margin:0 0 28px;">
      <p style="margin:0 0 16px;font-size:14px;color:#555;">
        Click the button below to verify your email.<br>
        <strong>This link expires in 24 hours.</strong>
      </p>
      <a href="${verifyLink}" style="
        display:inline-block;background:#c9a84c;color:#000;
        text-decoration:none;font-size:13px;font-weight:700;
        text-transform:uppercase;letter-spacing:0.1em;
        padding:15px 36px;border-radius:2px;">
        Verify My Email →
      </a>
    </div>

    <p style="font-size:13px;color:#888;margin:0 0 8px;line-height:1.6;">
      Or paste this link into your browser:
    </p>
    <p style="font-size:12px;color:#c9a84c;word-break:break-all;margin:0 0 24px;">
      ${verifyLink}
    </p>

    <p style="font-size:13px;color:#999;margin:0;line-height:1.6;">
      If you didn't create an account with us, you can safely ignore this email.
      Your email address will not be used.
    </p>
  `);

  await sendEmail(
    user.email,
    'Verify your email — Apex Leadership Summit',
    html
  );
}

// ═════════════════════════════════════════════
// 3. PASSWORD RESET EMAIL
// ═════════════════════════════════════════════
async function sendPasswordResetEmail(user, resetToken) {
  const resetLink = `http://localhost:3000/reset-password?token=${resetToken}`;

  const html = wrapEmail(`
    <h1 style="font-family:Georgia,serif;font-size:28px;font-weight:700;color:#080808;margin:0 0 8px;">
      Reset Your Password
    </h1>
    <p style="font-size:15px;color:#555;margin:0 0 24px;line-height:1.6;">
      Hi ${user.first_name}, we received a request to reset your password.
      Click the button below — this link is valid for <strong>1 hour only</strong>.
    </p>

    <div style="text-align:center;margin:0 0 28px;">
      <a href="${resetLink}" style="
        display:inline-block;background:#c9a84c;color:#000;
        text-decoration:none;font-size:13px;font-weight:700;
        text-transform:uppercase;letter-spacing:0.1em;
        padding:15px 36px;border-radius:2px;">
        Reset My Password →
      </a>
    </div>

    <p style="font-size:13px;color:#999;margin:0;line-height:1.6;">
      If you didn't request a password reset, ignore this email — your password won't change.
      For security, this link expires after 1 hour.
    </p>
  `);

  await sendEmail(user.email, 'Reset your Apex Leadership password', html);
}

module.exports = {
  sendWelcomeEmail,
  sendVerificationEmail,
  sendPasswordResetEmail
};
