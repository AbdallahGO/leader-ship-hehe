require('dotenv').config();
const express = require('express');
const helmet = require('helmet');
const cors = require('cors');
const cookieParser = require('cookie-parser');
const morgan = require('morgan');
const { validateEnv } = require('./config/env');
const { testDbConnection } = require('./config/db');
const errorHandler = require('./middleware/errorHandler');
const healthRoute = require('./routes/health');
const authRoute = require('./routes/auth');

validateEnv();

const app = express();
const PORT = Number(process.env.PORT || 5000);

app.use(helmet());
app.use(
  cors({
    origin: function (origin, callback) {
      // Allow requests with no origin (mobile apps, curl, etc.)
      if (!origin) return callback(null, true);
      
      const allowedOrigins = [
        process.env.FRONTEND_URL,
        'http://localhost:3000', // Next.js dev
        'http://localhost:8000', // Static server
        'http://127.0.0.1:8000'  // Alternative localhost
      ];
      
      if (allowedOrigins.includes(origin)) {
        return callback(null, true);
      }
      
      return callback(new Error('Not allowed by CORS'));
    },
    credentials: true,
  })
);
app.use(express.json({ limit: '10kb' }));
app.use(cookieParser());
if (process.env.NODE_ENV !== 'production') {
  app.use(morgan('dev'));
}

app.use('/health', healthRoute);
app.use('/api/auth', authRoute);

app.use(errorHandler);

async function start() {
  try {
    await testDbConnection();
    app.listen(PORT, () => {
      console.log(`✓ Server listening on port ${PORT}`);
    });
  } catch (error) {
    console.error('Database connection failed:', error.message || error);
    process.exit(1);
  }
}

start();
