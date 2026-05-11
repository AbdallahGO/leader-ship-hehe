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
    origin: process.env.FRONTEND_URL,
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
